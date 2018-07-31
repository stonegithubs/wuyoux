<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace common\helpers\orders;

//出行类
use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UrlHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderCancel;
use common\models\orders\OrderComplaint;
use common\models\orders\OrderFee;
use common\models\orders\OrderLog;
use common\models\orders\OrderTrip;
use common\helpers\utils\RegionHelper;
use common\helpers\payment\CouponHelper;
use common\models\util\HistoryAddress;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

abstract class TripBikeHelper extends TripHelper
{

	const ORDER_TYPE    = '小帮出行';
	const OLD_ORDER_TBL = 'bb_51_orders';

	//用户任务页
	public static function userTask($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id']]);
		if ($order) {
			$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
			if ($trip) {

				$orderAmount = bcadd($trip->estimate_amount, $trip->amount_ext, 2);
				$discount    = CouponHelper::maxCoupon($order->user_id, Ref::CATE_ID_FOR_MOTOR, $orderAmount);    //最大的抵扣券的费用

				$result = [
					'order_no'               => $order->order_no,
					'order_type'             => self::ORDER_TYPE,
					'order_time'             => date("Y-m-d H:i:s", $order->create_time),
					'start_location'         => $order->start_location,          //起点坐标
					'start_address'          => $order->start_address,           //起点位置
					'end_location'           => $order->end_location,            //终点坐标
					'end_address'            => $order->end_address,             //终点位置
					'estimate_amount'        => sprintf("%.2f", $trip->estimate_amount),          //预估费用
					'estimate_amount_text'   => $trip->estimate_amount . "元",
					'estimate_distance'      => $trip->estimate_distance,        //预估行程距离
					'estimate_distance_text' => UtilsHelper::distance($trip->estimate_distance),   //预估行程距离
					'estimate_discount'      => sprintf("%.2f", $discount),                        //预估折扣
					'license_plate'          => $trip->license_plate,
					'trip_status'            => $trip->trip_status,
					'spend_time'             => 0,
					'starting_distance'      => $trip->starting_distance,
					'starting_distance_text' => UtilsHelper::distance($trip->starting_distance),
					'provider_location'      => $order->provider_location,
					'provider_address'       => $order->provider_address,
					'amount_ext'             => sprintf("%.2f", $trip->amount_ext),
				];

				if ($trip->amount_ext > 0) {
					$result['estimate_amount_text'] = $orderAmount . "元（含加价" . $trip->amount_ext . "元）";
				}

				if ($discount > 0) {
					$trip->amount_ext > 0 ?
						$result['estimate_amount_text'] = bcsub($orderAmount, $discount, 2) . "元（含加价" . $trip->amount_ext . "元 劵已抵" . $discount . "元）" :
						$result['estimate_amount_text'] = bcsub($orderAmount, $discount, 2) . "元（劵已抵" . $discount . "元）";
				}

				//小帮预计用时
				$arrive_duration                    = $trip->starting_distance > 0 ? ($trip->starting_distance * 0.001 * 90) : 60;    //90s 一公里计算
				$result['provider_arrive_duration'] = UtilsHelper::durationLabel($arrive_duration);

				//抢单后 小帮信息
				if ($order->robbed == Ref::ORDER_ROBBED) {    //已经接单 显示小帮信息
					$provider = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->provider_address);
					$result   = array_merge($result, $provider);
				}
				//行程用时计算
				if ($trip->trip_status == Ref::TRIP_STATUS_START)
					$result['spend_time'] = time() - $trip->pick_time;

				if ($trip->trip_status == Ref::TRIP_STATUS_END)
					$result['spend_time'] = $trip->arrive_time - $trip->pick_time;

				//当结束行程后，赋值真实的数据给前端
				if ($order->order_status == Ref::ORDER_STATUS_AWAITING_PAY) {

					$params['card_id']                = '-1';
					$priceDetail                      = self::priceDetail($order->user_id, $params);
					$result['estimate_amount']        = $priceDetail ? $priceDetail['amount_payable'] : bcsub($order->order_amount, $discount, 2);    //订单金额-折扣 就是实付
					$result['estimate_distance']      = $trip->actual_distance;
					$result['estimate_distance_text'] = UtilsHelper::distance($trip->actual_distance);   //行程距离
					$result['estimate_discount']      = $priceDetail ? $priceDetail['discount'] : $discount;
					$result['amount_ext']             = sprintf("%.2f", $priceDetail['amount_ext']);
				}

				//订单进度
				$result['schedule'] = self::getOrderSchedule($order, $trip);
			}
		}

		return $result;
	}

	//用户完成页
	public static function userFinish($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id']]);
		if ($order) {
			$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
			if ($trip) {

				$result = [
					'order_no'            => $order->order_no,
					'order_type'          => self::ORDER_TYPE,
					'order_time'          => date("Y-m-d H:i:s", $order->create_time),
					'start_location'      => $order->start_location,                        //起点坐标
					'start_address'       => $order->start_address,                        //起点位置
					'end_location'        => $order->end_location,                            //终点坐标
					'end_address'         => $order->end_address,                            //终点位置
					'license_plate'       => $trip->license_plate,                            //车牌号码
					'trip_status'         => $trip->trip_status,                         //出行状态
					'spend_time'          => 0,
					'order_amount'        => sprintf("%.2f", $order->order_amount),            //实际金额
					'order_amount_text'   => sprintf("%.2f", $order->order_amount) . "元",
					'amount_payable'      => sprintf("%.2f", $order->amount_payable),
					'order_distance'      => intval($trip->actual_distance),                        //实际距离
					'order_distance_text' => UtilsHelper::distanceLabel($trip->actual_distance),
					'total_fee'           => sprintf("%.2f", $trip->total_fee),            //打赏小费
					'payment_type'        => TransactionHelper::getPaymentType($order->payment_id),
				];

				//注意：这里返回实付的金额，给前端显示
				if ($trip->amount_ext > 0) {
					$result['order_amount_text'] = $order->amount_payable . "元（含加价" . $trip->amount_ext . "元）";

				}

				if ($order->discount > 0) {
					$trip->amount_ext > 0 ?
						$result['order_amount_text'] = $order->amount_payable . "元（含加价" . $trip->amount_ext . "元 劵已抵" . $order->discount . "元）" :
						$result['order_amount_text'] = $order->amount_payable . "元（劵已抵" . $order->discount . "元）";
				}

				//抢单后 小帮信息
				if ($order->robbed == Ref::ORDER_ROBBED) {    //已经接单 显示小帮信息
					$provider = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->provider_address);
					$result   = array_merge($result, $provider);
				}

				//订单进度
				$result['schedule'] = self::getOrderSchedule($order, $trip);

				//评价信息
				$evaluate = EvaluateHelper::getEvaluateInfo($order->order_no);
				if ($evaluate) {

					$result['evaluate']     = $evaluate;
					$result['can_evaluate'] = 0;    //有评价信息 不能评价

				} else {

					$result['evaluate']     = null;
					$result['can_evaluate'] = 1;    //无评价信息 能评价
				}

				//行程用时计算
				if ($trip->trip_status == Ref::TRIP_STATUS_START)
					$result['spend_time'] = time() - $trip->pick_time;

				if ($trip->trip_status == Ref::TRIP_STATUS_END)
					$result['spend_time'] = $trip->arrive_time - $trip->pick_time;

				$result['can_call'] = 1;
				if (time() - $order->finish_time > 86400) {
					$result['can_call'] = 0;
				}
			}
		}

		return $result;
	}

	//用户取消页
	public static function userCancel($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no']]);
		if ($order) {
			$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
			if ($trip) {

				$result = [
					'order_no'               => $order->order_no,
					'order_type'             => self::ORDER_TYPE,
					'order_time'             => date("Y-m-d H:i:s", $order->create_time),
					'start_location'         => $order->start_location,          //起点坐标
					'start_address'          => $order->start_address,           //起点位置
					'end_location'           => $order->end_location,            //终点坐标
					'end_address'            => $order->end_address,             //终点位置
					'estimate_amount'        => $trip->estimate_amount,          //预估费用
					'estimate_amount_text'   => $trip->estimate_amount . "元",   //预估费用Text
					'estimate_distance'      => $trip->estimate_distance,        //预估行程距离
					'estimate_distance_text' => UtilsHelper::distance($trip->estimate_distance),   //预估行程距离
					'license_plate'          => $trip->license_plate,
					'trip_status'            => $trip->trip_status,
					'cancel_type'            => $trip->cancel_type,
				];

				//抢单后 小帮信息
				if ($order->robbed == Ref::ORDER_ROBBED) {    //已经接单 显示小帮信息
					$provider = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->provider_address);
					$result   = array_merge($result, $provider);
				}

				//订单进度
				$result['schedule'] = self::getOrderSchedule($order, $trip);

				$result['cancel_message'] = "您已取消订单！有问题请联系我们客服进行处理";
				if ($trip->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_APPLY) {
					$result['cancel_message'] = "小帮已取消了订单，试试重新发布吧";
				}
				//TODO 平台取消

				$result['can_call'] = 1;
				if (time() - $order->finish_time > 86400) {
					$result['can_call'] = 0;
				}
			}
		}

		return $result;
	}

	/**
	 * 获取订单进度
	 *
	 * @param $order
	 * @param $trip
	 * @return array
	 */
	public static function getOrderSchedule($order, $trip)
	{

		$result[] = ['name' => '创建订单', 'value' => date("Y-m-d H:i", $order->create_time)];    //创建订单
		isset($order->robbed_time) && !empty($order->robbed_time) ? $result[] = ['name' => '小帮接单', 'value' => date("Y-m-d H:i", $order->robbed_time)] : null;    //小帮接单
		isset($trip->point_time) && !empty($trip->point_time) ? $result[] = ['name' => '小帮到达上车点', 'value' => date("Y-m-d H:i", $trip->point_time)] : null;    //小帮到达上车点
		isset($trip->pick_time) && !empty($trip->pick_time) ? $result[] = ['name' => '开始出行', 'value' => date("Y-m-d H:i", $trip->pick_time)] : null;        //开始出行
		isset($trip->arrive_time) && !empty($trip->arrive_time) ? $result[] = ['name' => '结束行程', 'value' => date("Y-m-d H:i", $trip->arrive_time)] : null;    //结束行程
		isset($order->payment_time) && !empty($order->payment_time) ? $result[] = ['name' => '订单支付', 'value' => date("Y-m-d H:i", $order->payment_time)] : null;    //订单支付
		isset($trip->fee_time) && !empty($trip->fee_time) ? $result[] = ['name' => '打赏小帮', 'value' => date("Y-m-d H:i", $trip->fee_time)] : null;    //打赏小帮

		if ($order->order_status == Ref::ORDER_STATUS_EVALUATE) {
			$evaluateTime = EvaluateHelper::getEvaluateTime($order->order_no);

			$evaluateTime ? $result[] = ['name' => '评价小帮', 'value' => date("Y-m-d H:i", $evaluateTime)] : null;    //评价小帮
		}

		isset($order->cancel_time) && !empty($order->cancel_time) ? $result[] = ['name' => '取消订单', 'value' => date("Y-m-d H:i", $order->cancel_time)] : null;    //取消小帮

		ArrayHelper::multisort($result, 'value');

		//TODO 时间排序
		return $result;
	}


	//小帮任务页
	public static function workerTask($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id']]);
		if ($order) {
			$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
			if ($trip) {

				$result = [
					'order_no'               => $order->order_no,
					'order_type'             => self::ORDER_TYPE,
					'order_time'             => date("Y-m-d H:i:s", $order->create_time),
					'start_location'         => $order->start_location,          //起点坐标
					'start_address'          => $order->start_address,           //起点位置
					'end_location'           => $order->end_location,            //终点坐标
					'end_address'            => $order->end_address,             //终点位置
					'estimate_amount'        => bcadd($trip->estimate_amount, $trip->amount_ext, 2),          //预估费用	TODO 扣除抽佣的
					'estimate_distance'      => $trip->estimate_distance,        //预估行程距离
					'estimate_distance_text' => UtilsHelper::distance($trip->estimate_distance),   //预估行程距离
					'trip_status'            => $trip->trip_status,
					'spend_time'             => 0,
					'amount_ext'             => $trip->amount_ext,
					'estimate_amount_text'   => $trip->estimate_amount,
				];

				//用户信息
				$userInfo               = UserHelper::getUserInfo($order->user_id);
				$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "帮帮用户";
				$result['mobile']       = isset($userInfo['mobile']) ? $userInfo['mobile'] : $order->user_mobile;
				$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
				$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);

				//行程用时计算
				if ($trip->trip_status == Ref::TRIP_STATUS_START)
					$result['spend_time'] = time() - $trip->pick_time;

				if ($trip->trip_status == Ref::TRIP_STATUS_END)
					$result['spend_time'] = $trip->arrive_time - $trip->pick_time;

				$trip->amount_ext > 0
					? $result['estimate_amount_text'] = $result['estimate_amount'] . "元" . "(含加价" . $trip->amount_ext . "元)"
					: '';
			}
		}

		return $result;
	}

	//小帮任务页
	public static function workerDetail($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id']]);
		if ($order) {
			$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
			if ($trip) {

				$result = [
					'order_no'            => $order->order_no,
					'order_type'          => self::ORDER_TYPE,
					'order_time'          => date("Y-m-d H:i:s", $order->create_time),
					'start_location'      => $order->start_location,          //起点坐标
					'start_address'       => $order->start_address,           //起点位置
					'end_location'        => $order->end_location,            //终点坐标
					'end_address'         => $order->end_address,             //终点位置
					'order_amount'        => $order->order_amount,            //订单金额
					'order_amount_text'   => $order->order_amount . "元",
					'trip_status'         => $trip->trip_status,
					'spend_time'          => 0,
					'order_status_text'   => OrderHelper::getOrderType($order->order_status),    //订单状态
					'total_fee'           => sprintf("%.2f", $trip->total_fee),
					'order_distance'      => $trip->actual_distance,
					'order_distance_text' => UtilsHelper::distanceLabel($trip->actual_distance),
				];

				//用户信息
				$userInfo               = UserHelper::getUserInfo($order->user_id);
				$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "帮帮用户";
				$result['mobile']       = isset($userInfo['mobile']) ? $userInfo['mobile'] : $order->user_mobile;
				$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
				$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);

				$spend_time = 0;
				//行程用时计算
				if ($trip->trip_status == Ref::TRIP_STATUS_START)
					$spend_time = time() - $trip->pick_time;

				if ($trip->trip_status == Ref::TRIP_STATUS_END)
					$spend_time = $trip->arrive_time - $trip->pick_time;

				$result['spend_time']      = $spend_time;
				$result['spend_time_text'] = UtilsHelper::durationLabel($spend_time);

				if ($order->order_status == Ref::ORDER_STATUS_CANCEL
					|| $order->order_status == Ref::ORDER_STATUS_DECLINE
					|| $order->order_status == Ref::ORDER_STATUS_CALL_OFF) {

					$order->order_amount           = bcadd($trip->estimate_amount, $trip->amount_ext, 2);
					$result['order_distance']      = $trip->estimate_distance;
					$result['order_distance_text'] = UtilsHelper::distanceLabel($trip->estimate_distance);

				}
				$trip->amount_ext > 0
					? $result['order_amount_text'] = $order->order_amount . "元" . "(含加价" . $trip->amount_ext . "元)"
					: '';

			}
		}

		return $result;
	}

	//小帮任务页（旧表）
	public static function oldWorkerDetail($params)
	{
		$result = false;
		$fields = ['uid', 'orderid', 'n_mobile', 'n_address', 'n_address_location', 'n_end_address', 'n_end_location', 'trip_status', 'status', 'money', 'received_time', 'arrive_time', 'create_time'];
		$order  = (new Query())->select($fields)->from(self::OLD_ORDER_TBL)->where(['orderid' => $params['order_no']])->one();
		if ($order) {
			$result = [
				'order_no'          => $order['orderid'],
				'order_type'        => self::ORDER_TYPE,
				'order_time'        => date("Y-m-d H:i:s", $order['create_time']),
				'start_location'    => $order['n_address_location'],          //起点坐标
				'start_address'     => $order['n_address'],           //起点位置
				'end_location'      => $order['n_end_location'],            //终点坐标
				'end_address'       => $order['n_end_address'],             //终点位置
				'order_amount'      => $order['money'],            //订单金额
				'trip_status'       => $order['trip_status'],
				'spend_time'        => 0,
				'order_status_text' => self::getOldOrderType($order['status'])    //订单状态
			];

			//用户信息
			$userInfo               = UserHelper::getUserInfo($order['uid'], 'nickname, mobile, userphoto');
			$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "帮帮用户";
			$result['mobile']       = isset($userInfo['mobile']) ? $userInfo['mobile'] : $order['n_mobile'];
			$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
			$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);

			$spend_time = 0;
			//行程用时计算
			if ($order['trip_status'] == 2) {
				$spend_time = time() - $order['received_time']; //已接乘客
			}

			if ($order['trip_status'] == 3) {
				$spend_time = $order['arrive_time'] - $order['received_time']; //已到达
			}
			$result ['spend_time']     = $spend_time;
			$result['spend_time_text'] = UtilsHelper::durationLabel($spend_time);
		}

		return $result;
	}

	//小帮出行价格预估
	public static function estimatePrice($startLocation, $endLocation, $userInfo, $cateId)
	{
		$priceData  = parent::getRangePriceDataForAMap($startLocation, $endLocation, $userInfo, $cateId);
		$totalPrice = $priceData['price'];  //原订单价格
		$discount   = CouponHelper::maxCoupon($userInfo['uid'], $cateId, $totalPrice);  //获取最大的优惠金额

		$estimatePrice = bcsub($totalPrice, $discount, 2);  //减了优惠券后的预估价格
		$estimatePrice = $estimatePrice > 0 ? $estimatePrice : 0;
		$distance      = $priceData['distance'];   //行程距离

		if ($priceData['type'] == 'day') {
			$serviceFeeText = '白天服务费';
		} else {
			$serviceFeeText = '夜间服务费';
		}
		$regionArr           = RegionHelper::getAddressIdByLocation($startLocation, $userInfo['city_id']);
		$priceRuleLink       = UrlHelper::webLink(['rule/price-trip-bike', 'city_id' => $regionArr['city_id'], 'area_id' => $regionArr['area_id']]);
		$serviceProtocolLink = UrlHelper::webLink(['protocol/index', 'doc' => 'user_use']);

		$result = [
			'estimate_price'        => $estimatePrice,  //预估价格
			'distance'              => $distance,
			'distance_text'         => UtilsHelper::distance($distance),
			'price'                 => bcsub($totalPrice, $priceData['service_fee'], 2),    //行程费用 总费用 - 服务费
			'discount'              => $discount,
			'service_fee'           => $priceData['service_fee'],   //服务费
			'service_fee_text'      => $serviceFeeText,
			'price_rule_link'       => $priceRuleLink,
			'service_protocol_link' => $serviceProtocolLink,
			'city_id'               => $regionArr['city_id'],
			'area_id'               => $regionArr['area_id'],
		];

		return $result;
	}


	//内容推送给用户
	public static function pushToUserNotice($order_id, $type, $params = [])
	{

		//1.待接单提示流程
		//2.小帮已接收订单 快送状态变更通知
		//3.平台自动取消订单通知
		//4.申请取消订单通知

		$order = Order::findOne(['order_id' => $order_id]);
		if ($order) {
			$pushData = [];
			$canPush  = true;

			if ($type == self::PUSH_USER_TYPE_SEARCH_WORKER) {
				if ($order->robbed == Ref::ORDER_ROBBED || $order->order_status != Ref::ORDER_STATUS_DEFAULT) {
					$canPush = false;
				}
				$inform = [
					0  => "暂无附近没有小帮接单,建议你加价试试",            //这个暂时没有用到
					5  => '暂时没有小帮接单,建议加价',
					20 => '附近暂时没有小帮接单，10分钟后系统会自动取消订单',
					30 => '订单长时间没有小帮接单，系统已自动取消了订单，建议更改地址再重新下单',    //这个暂时没有用到
				];

				$tip_times                  = $params['tip_times'];
				$pushData['glx']            = Ref::GETUI_TYPE_GRAB_NOTICE;//个推类型
				$pushData['to']             = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']            = PushHelper::TRIP_BIKE_WAITE_TIP;
				$pushData['inform_content'] = isset($inform[$tip_times]) ? $inform[$tip_times] : '!';
				$pushData['content']        = isset($inform[$tip_times]) ? $inform[$tip_times] : '!';
			}

			if ($type == self::PUSH_USER_TYPE_TASK_PROGRESS) {//抢单和流程状态变更通知

				$inform = [
					Ref::TRIP_STATUS_PICKED => '您的订单已经被小帮接受，请查看!',
					Ref::TRIP_STATUS_POINT  => '小帮已到达出发起点，请准备上车',
					Ref::TRIP_STATUS_START  => '开始出行，请注意安全',
					Ref::TRIP_STATUS_END    => '您已经到达终点，请支付费用',
				];

				$trip_status                = $params['trip_status'];
				$pushData['glx']            = Ref::GETUI_TYPE_GRAB_NOTICE;//个推类型
				$pushData['to']             = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']            = PushHelper::TRIP_BIKE_WORKER_TASK;
				$pushData['inform_content'] = isset($inform[$trip_status]) ? $inform[$trip_status] : '您的订单已经被小帮接受，请查看!';
				$pushData['trip_status']    = $trip_status;

				isset($inform[$trip_status]) ? null : $canPush = false;    //联系客户不需要推送

				//小帮信息
				$providerInfo                = UserHelper::getShopInfo($order->provider_id);
				$id_image                    = isset($providerInfo['shops_photo']) ? $providerInfo['shops_photo'] : 0;
				$pushData['provider_name']   = isset($providerInfo['shops_name']) ? $providerInfo['shops_name'] : "无忧帮帮";
				$pushData['provider_mobile'] = $order->provider_mobile;
				$pushData['provider_photo']  = ImageHelper::getUserPhoto($id_image);
			}

			if ($type == self::PUSH_USER_TYPE_CANCEL_PROGRESS) {    //申请取消流程

				$inform                        = [
					Ref::ERRAND_CANCEL_PROVIDER_APPLY    => '小帮取消订单!',
					Ref::ERRAND_CANCEL_PROVIDER_AGREE    => '小帮同意取消订单!',
					Ref::ERRAND_CANCEL_PROVIDER_DISAGREE => '小帮不同意取消订单!',
					Ref::ERRAND_CANCEL_DEAL_NOTIFY       => isset($params['deal_msg']) ? $params['deal_msg'] : ErrandHelper::CANCEL_DEAL_NOTIFY_MSG,
				];
				$cancel_type                   = isset($params['cancel_type']) ? $params['cancel_type'] : Ref::ERRAND_CANCEL_PROVIDER_APPLY;
				$current_page                  = 'cancel';
				$pushData['glx']               = Ref::GETUI_TYPE_CANCEL_NOTICE; //个推类型
				$pushData['to']                = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']               = isset($params['url']) ? $params['url'] : PushHelper::TRIP_BIKE_WORKER_CANCEL;
				$pushData['cancel_type']       = $cancel_type;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '小帮取消订单!';
				$pushData['current_page']      = $current_page;
				isset($inform[$cancel_type]) ? '' : $canPush = false;
			}

			$pushData['order_no']     = $order->order_no;
			$pushData['provider_id']  = $order->provider_id;
			$pushData['user_id']      = $order->user_id;
			$pushData['push_user_id'] = $order->user_id;
			$pushData['push_role']    = Ref::PUSH_ROLE_USER;
			$pushData['log_time']     = date("Y-m-d H:i:s");
			$canPush ? QueueHelper::toOneTransmissionForUser($order->user_id, $pushData, 'errand_buy_to_user') : null;
		}

	}

	/**
	 * 内容推送给小帮
	 *
	 * @param $orderId
	 * @param $type
	 * @param $params
	 */
	public static function pushToProviderNotice($orderId, $type, $params = [])
	{
		//1订单确认
		//2.申请取消订单通知
		//3.商品支付通知
		$order = Order::findOne(['order_id' => $orderId]);
		if ($order) {
			$pushData = [];
			$canPush  = true;
			if ($type == self::PUSH_PROVIDER_TYPE_PAY) {    //订单确认

				$pushData['glx']            = Ref::GETUI_TYPE_PAY;
				$pushData['to']             = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']            = PushHelper::TRIP_BIKE_USER_PAY;
				$pushData['inform_content'] = '您的订单已经确认完成';
			}

			if ($type == self::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS) {    //取消订单

				$pushData['glx']            = Ref::GETUI_TYPE_CANCEL_NOTICE;
				$pushData['to']             = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']            = isset($params['url']) ? $params['url'] : PushHelper::TRIP_BIKE_USER_CANCEL;
				$pushData['inform_content'] = isset($params['deal_msg']) ? $params['deal_msg'] : '用户取消订单!';

			}

			if ($type == self::PUSH_PROVIDER_TYPE_PRESS) {    //用户催一催

				$pushData['glx']            = Ref::GETUI_TYPE_CANCEL_NOTICE;
				$pushData['to']             = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']            = PushHelper::TRIP_BIKE_USER_PRESS;
				$pushData['inform_content'] = '无忧帮帮用户催单啦';//'客户催单啦,请尽快到达上车点!';
			}

			if ($type == self::PUSH_PROVIDER_TYPE_ASSIGN) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_PROVIDER;
				$pushData['to']             = Ref::GETUI_TO_TRIP_ORDER;
				$pushData['url']            = PushHelper::TRIP_BIKE_ASSIGN_PROVIDER_NOTICE;
				$pushData['inform_content'] = '平台给您分发了一张出行订单!';//'客户催单啦,请尽快到达上车点!';
			}

			$pushData['order_no']     = $order->order_no;
			$pushData['provider_id']  = $order->provider_id;
			$pushData['user_id']      = $order->user_id;
			$pushData['push_user_id'] = $order->provider_id;
			$pushData['push_role']    = Ref::PUSH_ROLE_PROVIDER;
			$pushData['log_time']     = date("Y-m-d H:i:s");

			$canPush ? QueueHelper::toOneTransmissionForProvider($order->provider_id, $pushData, 'errand_buy_to_provider') : null;

		}
	}

	//计价详情
	public static function priceDetail($user_id, $params)
	{
		$result = false;

		$order = Order::findOne(['order_no' => $params['order_no']]);
		if ($order) {
			$userInfo            = UserHelper::getUserInfo($user_id, 'city_id');
			$regionArr           = RegionHelper::getAddressIdByLocation($order->start_location, $userInfo['city_id']);
			$priceRuleLink       = UrlHelper::webLink(['rule/price-trip-bike', 'city_id' => $regionArr['city_id'], 'area_id' => $regionArr['area_id']]);
			$serviceProtocolLink = UrlHelper::webLink(['protocol/index', 'doc' => 'user_use']);
			$trip                = OrderTrip::findOne(['order_id' => $order->order_id]);
			if ($trip) {

				$orderAmount = $order->order_amount;

				//自动匹配一张优惠券
				if ($params['card_id'] == '-1') {
					$now_time  = time();
					$card_data = (new Query())->from("bb_card_user as cu")
						->select("cu.id")
						->leftJoin("bb_card as c", "cu.c_id = c.id")
						->where(['cu.uid' => $user_id, 'cu.status' => Ref::CARD_STATUS_NEW, 'c.second_category' => [0, Ref::CATE_ID_FOR_MOTOR]])// 'c.belong_type' => Ref::BELONG_TYPE_BIZ
						->andWhere([">", 'cu.end_time', $now_time])
						->andWhere(["<=", "cu.price", $orderAmount])
						->orderBy("cu.price desc,cu.end_time")
						->one();
					if ($card_data) {
						$params['card_id'] = $card_data['id'];
					}
				}

				$distance  = $trip->actual_distance;
				$cityPrice = self::getLogContent($order->order_id, 'trip_price_snapshot');

				if (isset($cityPrice['type']) && $cityPrice['type'] == 'day') {
					$serviceFeeText = '白天服务费';
				} else {
					$serviceFeeText = '夜间服务费';
				}

				$orderHelper = new OrderHelper();
				$calc        = $orderHelper->getOrderCalc($orderAmount, $params);
				$result      = [
					'card_amount'           => $calc['card_amount'],
					'card_id'               => $calc['card_id'],
					'amount_payable'        => $calc['amount_payable'],//实际支付
					'distance'              => $distance,
					'distance_text'         => UtilsHelper::distance($distance),
					'actual_amount'         => bcsub($trip->actual_amount, $cityPrice['service_fee'], 2), //行程费用
					'amount_ext'            => sprintf("%.2f", $trip->amount_ext),
					'discount'              => $calc['discount'],
					'service_fee'           => isset($cityPrice['service_fee']) ? $cityPrice['service_fee'] : "0元",   //服务费
					'service_fee_text'      => $serviceFeeText,
					'price_rule_link'       => $priceRuleLink,
					'service_protocol_link' => $serviceProtocolLink,
				];
			}
		}

		return $result;
	}

	//加价
	public static function addPrice($order_no, $price)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $order_no, 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$trip    = OrderTrip::findOne(['order_id' => $order->order_id]);
				$logData = [];
				if ($trip) {
					$logData['before_amount_ext'] = $trip->amount_ext;
					$trip->amount_ext             += $price;
					$logData['now_amount_ext']    = $trip->amount_ext;

					$trip->save() ? $result = true : Yii::$app->debug->log_info("add_price", $trip->getErrors());
					$result &= self::saveLogContent($order->order_id, 'add_price', $logData, '出行加价');    //记录业务日志
				}
				if ($result) {
					$orderAmount = bcadd($trip->estimate_amount, $trip->amount_ext, 2);
					$discount    = CouponHelper::maxCoupon($order->user_id, Ref::CATE_ID_FOR_MOTOR, $orderAmount);    //最大的抵扣券的费用

					$result = ['estimate_amount_text' => bcsub($orderAmount, $discount, 2) . "元（含加价" . $trip->amount_ext . "元）"];
					$discount > 0
						? $result['estimate_amount_text'] = bcsub($orderAmount, $discount, 2) . "元（含加价" . $trip->amount_ext . "元 劵已抵" . $discount . "元）"
						: null;

					$transaction->commit();
				}
			}

		}
		catch (Exception $e) {
			$transaction->rollBack();
		}


		return $result;
	}

	//催一催小帮
	//默认15分钟后才能进行下一次催单
	public static function pressProvider($order_no, $userId, $minute = 15)
	{
		$result = false;
		$order  = Order::find()->where(['order_no' => $order_no, 'user_id' => $userId])->select(['order_id', 'provider_id'])->one();
		if ($order) {
			//出行状态必须是小帮已接单
			$trip = OrderTrip::find()->where(['order_id' => $order['order_id'], 'trip_status' => Ref::TRIP_STATUS_PICKED])->select('trip_id')->one();
			if ($trip) {
				$pressTime = time() - $minute * 60;
				$logData   = OrderLog::find()->where(['order_id' => $order['order_id'], 'log_key' => 'press_provider'])
					->andWhere(['>', 'create_time', $pressTime])->one();
				if (!$logData) {
					$data   = [
						'user_id'     => $order['user_id'],
						'provider_id' => $order['provider_id'],
					];
					$result = OrderHelper::saveLogContent($order['order_id'], 'press_provider', $data, '催一催小帮');
					if ($result) {
						$result
							= ['order_id' => $order->order_id];
					}
				}
			}
		}

		return $result;
	}

	//用户取消信息保存
	//$param['content_id', 'content']
	/**
	 * 用户取消订单-保存
	 * @param $params
	 * @param $userId
	 * @return array
	 * @throws Exception
	 */
	public static function userCancelSave($params, $userId)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $userId, 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$trip = OrderTrip::findOne(['order_id' => $order->order_id]);

				$updateData = [
					'order_status'        => Ref::ORDER_STATUS_CANCEL,
					'request_cancel_id'   => $order->user_id,
					'request_cancel_time' => time(),
					'cancel_time'         => time(),
				];
				//订单主表
				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::$app->debug->log_info("trip_user_cancel_order", $order->getErrors());

				//订单出行表
				$updateData['cancel_type'] = Ref::ERRAND_CANCEL_USER_APPLY;
				$trip->attributes          = $updateData;
				$trip->save() ? $result &= true : Yii::$app->debug->log_info("trip_user_cancel_trip", $trip->getErrors());

				//订单取消表
				$orderCancel             = new OrderCancel();
				$orderCancel->ids_ref    = $order->order_id;
				$orderCancel->content_id = $params['content_id'];
				$orderCancel->content    = $params['content'];

				$orderCancel->save() ? $result &= true : Yii::$app->debug->log_info("trip_user_cancel_save", $orderCancel->getErrors());

				//订单日志
				$result &= self::saveLogContent($order->order_id, 'user_cancel', $updateData, '用户取消订单');    //记录业务日志

				$orderCancel->save() ? ($result &= true) : Yii::error("user_cancel_save:" . json_encode($orderCancel->getErrors()));
				if ($result) {
					$result = [
						'order_id' => $order->order_id,
						'robbed'   => $order->robbed
					];
					$transaction->commit();
				}
			}

		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	//投诉小帮保存
	public static function complaintSave($order_no, $param)
	{
		$result = false;
		$order  = Order::find()->where(['order_no' => $order_no])->select(['order_id', 'provider_id'])->one();
		//判断是否有小帮接单
		if ($order['provider_id']) {
			$complaint             = new OrderComplaint();
			$complaint->ids_ref    = $order['order_id'];
			$complaint->status     = Ref::COMPLAINT_WAIT;
			$complaint->content_id = $param['content_id'];
			$complaint->content    = $param['content'];
			$complaint->save() ? ($result = true) : Yii::error('complaint_save:' . json_encode($complaint->getErrors()));
		}

		return $result;
	}

	/**
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerProgress($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$trip = OrderTrip::findOne(['order_id' => $order->order_id]);

				$updateData = [];
				if ($params['trip_status'] == Ref::TRIP_STATUS_POINT) {    //到达起点
					$updateData = [
						'trip_status'    => Ref::TRIP_STATUS_POINT,
						'point_location' => $params['current_location'],
						'point_address'  => $params['current_address'],
						'point_time'     => time(),
						'update_time'    => time(),
					];
				}

				if ($params['trip_status'] == Ref::TRIP_STATUS_START) {    //开始出行
					$updateData = [
						'trip_status'   => Ref::TRIP_STATUS_START,
						'pick_location' => $params['current_location'],
						'pick_address'  => $params['current_address'],
						'pick_time'     => time(),
						'update_time'   => time(),
					];
				}

				if ($params['trip_status'] == Ref::TRIP_STATUS_END) { //结束行程
					$updateData = [
						'trip_status'     => Ref::ERRAND_STATUS_FINISH,
						'arrive_location' => $params['current_location'],
						'arrive_address'  => $params['current_address'],
						'arrive_time'     => time(),
						'finish_time'     => time(),
						'order_status'    => Ref::ORDER_STATUS_AWAITING_PAY,
						'update_time'     => time(),
						'end_location'    => $params['current_location'],
						'end_address'     => $params['current_address'],
					];

					//根据最终的坐标来计算最终的价格
					$route       = AMapHelper::bicycling(AMapHelper::coordToStr($order->start_location), AMapHelper::coordToStr($params['current_location'])); //订单起点坐标，当前坐标
					$newDistance = 0;
					if (is_array($route)) {
						$newDistance = $route['distance'];
					}

					$city_price      = RegionHelper::getPriceByTime($order->create_time, $order->city_id, $order->area_id, Ref::CATE_ID_FOR_MOTOR);
					$actual_amount   = $trip->estimate_amount;
					$actual_distance = $trip->estimate_distance;


					if ($newDistance > 0) {
						$actual_distance = $newDistance;

						$range_init = $city_price['range_init'];//初始距离
						$range      = $newDistance - $range_init;
						$price      = $city_price['range_init_price'];

						//根据距离得出价格
						if ($range > 0) {
							$price = bcadd($price, $range / 1000 * $city_price['range_unit_price'], 3);
						}

						$actual_amount = bcadd($price, $city_price['service_fee'], 3);
					}

					//记录价格快照
					self::saveLogContent($order->order_id, 'trip_price_snapshot', $city_price, '价格快照');

					$updateData['actual_amount']   = $actual_amount;
					$updateData['actual_distance'] = $actual_distance;
					$updateData['order_amount']    = bcadd($actual_amount, $trip->amount_ext, 3);
				}

				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::$app->debug->log_info("save_worker_progress_order", $order->getErrors());

				$trip->attributes = $updateData;
				$tripRes          = $trip->save();
				if (!$tripRes) {
					$result = false;
					Yii::$app->debug->log_info("save_worker_progress_trip", $trip->getErrors());
				}

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'worker_progress_trip', $updateData, '小帮工作流程');

				if ($result) {
					$result = [
						'order_id'    => $order->order_id,
						'trip_type'   => $trip->trip_type,
						'trip_status' => $params['trip_status'],
						'user_id'     => $order->user_id,
					];

					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	//小帮取消
	//$params['order_no','provider_id']
	public static function workerCancel($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();

		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$time                       = time();
				$order->order_status        = Ref::ORDER_STATUS_DECLINE;
				$order->request_cancel_id   = $params['provider_id'];
				$order->cancel_time         = $time;
				$order->request_cancel_time = $time;
				$order->update_time         = $time;
				$order->save() ? $result = true : Yii::error("worker_cancel:" . json_encode($order->getErrors()));

				$trip              = OrderTrip::findOne(['order_id' => $order->order_id]);
				$trip->cancel_type = Ref::ERRAND_CANCEL_PROVIDER_APPLY;
				$trip->save() ? ($result &= true) : Yii::error("user_cancel_save:" . json_encode($trip->getErrors()));

				$data   = [
					'provider_id' => $order->provider_id,
					'user_id'     => $order->user_id,
				];
				$result &= self::saveLogContent($order->order_id, 'worker_cancel', $data, '小帮取消');    //记录业务日志

				if ($result) {
					$result = [
						'order_id' => $order->order_id,
					];
					$transaction->commit();
				}
			}

		}
		catch (Exception $e) {
			$transaction->rollBack();
		}


		return $result;

	}


	//打赏小帮
	public static function addRewardPrePayment($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $params['order_no'], 'order_status' => [Ref::ORDER_STATUS_COMPLETED, Ref::ORDER_STATUS_EVALUATE]]);
			if ($order) {
				$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
				if ($trip) {

					$amount = $params['fee'];

					//写入小费表记录
					$feeData = [
						'ids_ref'    => $order->order_id,
						'type'       => Ref::FEE_TYPE_REWARD,
						'amount'     => $amount,
						'status'     => Ref::PAY_STATUS_WAIT,
						'payment_id' => $params['payment_id'],
					];

					$feeRes = self::addFee($feeData);
					$feeRes ? $result = true : Yii::$app->debug->log_info('trip_fee', $feeData);
					$fee_id = $feeRes && isset($feeRes['fee_id']) ? $feeRes['fee_id'] : 0;    //得到小费ID

					//写入交易表记录
					//添加支付流水
					$tradeParams['payment_id'] = $params['payment_id'];
					$tradeParams['type']       = Ref::TRANSACTION_TYPE_TIPS;
					$tranRes                   = TransactionHelper::createTrade($fee_id, $amount, $tradeParams);
					$result                    &= $tranRes;

					$result &= self::saveLogContent($order->order_id, 'trip_fee_prepayment', $feeData, '打赏小帮');    //记录业务日志

					if ($result) {
						$result = [
							'user_id'        => $order['user_id'],
							'order_id'       => $order['order_id'],
							'transaction_no' => strval($tranRes['transaction_no']),
							'fee'            => $tranRes['fee'],
							'fee_id'         => $fee_id
						];
						$transaction->commit();
					}
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;

	}

	//打赏小帮
	//支付成功后更新
	public static function addRewardSuccess($transaction_no, $trade_no, $payment_id, $fee, $remark = null, $data = null)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$data = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);
			if ($data) {
				$fee_id   = $data['ids_ref'];
				$amount   = $data['fee'];
				$orderFee = OrderFee::findOne(['fee_id' => $fee_id, 'status' => Ref::PAY_STATUS_WAIT]);

				if ($orderFee) {

					$order = Order::findOne(['order_id' => $orderFee->ids_ref]);
					if ($order) {
						$orderFee->status = Ref::PAY_STATUS_COMPLETE;
						$orderFee->save() ? $result = true : Yii::error("trip_fee_success:" . json_encode($orderFee->getErrors()));

						if ($payment_id == Ref::PAYMENT_TYPE_BALANCE && $amount > 0) {
							//扣除用户金额
							$result &= WalletHelper::decreaseUserBalance($order->user_id, $amount);
						}

						if ($amount > 0) {
							//用户收支明细表
							$result &= WalletHelper::userIncomePay('2', '5', $order->user_id, $data['transaction_no'], $amount, "小帮出行打赏");
							//小帮添加打赏金额
							$result &= WalletHelper::handleShopBalance($order->provider_id, $amount);

							//添加小帮收入记录
							$shop = ShopHelper::getShopInfoByProviderId($order->provider_id, ['uid', 'shops_money']);
							if ($shop) {
								$shop_balance = bcadd($shop['shops_money'], $amount, 2);
								$result       &= WalletHelper::handleIncomeShop($order->provider_id, $shop['uid'], $order->order_id, $amount, "小帮出行打赏", 1, 1, $shop_balance);
							}
							//修改订单记录
							$result &= TripHelper::updateOrderFee($order->order_id, $amount);
						}
						$logData = [
							'fee_id'     => $fee_id,
							'payment_id' => $payment_id,
							'amount'     => $amount,
						];
						$result  &= self::saveLogContent($order->order_id, 'trip_fee_success', $logData, '打赏小帮支付成功');
						if ($result) {
							$transaction->commit();
						}
					}
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	//获取支付信息
	public static function getPaymentDetail($params)
	{

		$result = false;

		$model = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => Ref::ORDER_STATUS_AWAITING_PAY]);
		if ($model) {

			$order_amount = $model->order_amount;

			//自动匹配一张优惠券
			if ($params['card_id'] == '-1') {
				$now_time  = time();
				$card_data = (new Query())->from("bb_card_user as cu")
					->select("cu.id")
					->leftJoin("bb_card as c", "cu.c_id = c.id")
					->where(['cu.uid' => $params['user_id'], 'cu.status' => Ref::CARD_STATUS_NEW, 'c.second_category' => [0, Ref::CATE_ID_FOR_MOTOR]])// 'c.belong_type' => Ref::BELONG_TYPE_BIZ
					->andWhere([">", 'cu.end_time', $now_time])
					->andWhere(["<=", "cu.price", $order_amount])
					->orderBy("cu.price desc,cu.end_time")
					->one();
				if ($card_data) {
					$params['card_id'] = $card_data['id'];
				}
			}

			$orderHelper              = new OrderHelper();
			$result                   = $orderHelper->getOrderCalc($order_amount, $params);
			$user_info                = UserHelper::getUserInfo($params['user_id'], 'money');
			$result['balance_pay']    = $user_info['money'] > $result['amount_payable'] ? 1 : 0;
			$result['order_amount']   = $order_amount;
			$result['card_available'] = CouponHelper::getOrderCardNum($model->order_no);//可用优惠券数量
		}

		return $result;
	}

	public static function tripPaymentSuccess($transaction_no, $trade_no, $payment_id, $fee, $remark = null, $data = null)
	{

		//解决方案
		//1、更新支付流水
		//2、获取抽佣
		//3、更新订单
		//4、更新用户资金
		//5、更新小帮资金
		//6、更新保险信息

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			//1、更新支付流水
			$data = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);
			if ($data) {
				$result   = true;
				$order_id = $data['ids_ref'];
				$order    = Order::findOne(['order_id' => $order_id]);

				if ($order) {
					$amount       = $order->amount_payable;
					$order_amount = $order->order_amount;        //行程的最终金额
					$orderData    = ArrayHelper::toArray($order);

					//2、获取抽佣
					$actual_amount = WalletHelper::takeMoney($orderData, $order_amount); //小帮金额是已经抽佣后的金额

					//3、更新订单和日志信息
					$updateData        = [
						'order_status'             => Ref::ORDER_STATUS_COMPLETED,
						'payment_status'           => Ref::PAY_STATUS_COMPLETE,
						'finish_time'              => time(),
						'payment_time'             => time(),
						'payment_id'               => $payment_id,
						'update_time'              => time(),
						'provider_estimate_amount' => $order_amount,
						'provider_actual_amount'   => $actual_amount,
					];
					$order->attributes = $updateData;
					$user_id           = $order->user_id;
					$order->save() ? $result = true : $result = false;
					$result ? null : Yii::error("order pay success save:" . json_encode($order->getErrors()));
					//记录业务日志
					$result &= self::saveLogContent($order->order_id, 'trip_finish', $updateData, '支付成功订单完成');
					CouponHelper::beUseCard($order->card_id);//更新卡券

					//4.1、更新用户资金
					if ($payment_id == Ref::PAYMENT_TYPE_BALANCE && $amount > 0) {
						//扣除 冻结用户金额
						$result &= WalletHelper::decreaseUserBalance($user_id, $amount);
					}

					//4.2、用户收支明细表
					if ($amount > 0) {
						$result &= WalletHelper::userIncomePay('2', '5', $user_id, $data['transaction_no'], $amount, "小帮出行");
					}

					//5.1、更新小帮资金
					$result           &= WalletHelper::handleShopBalance($order->provider_id, $actual_amount);
					$shop             = UserHelper::getShopInfo($order->provider_id);
					$provider_user_id = isset($shop['uid']) ? $shop['uid'] : 0;

					//5.2、店铺收支明细
					$balance = isset($shop['shops_money']) ? $shop['shops_money'] : $actual_amount;
					$result  &= WalletHelper::handleIncomeShop($order->provider_id, $provider_user_id, $order->order_no, $actual_amount, "小帮出行，收入" . $actual_amount . "元", Ref::PROVIDER_BALANCE_IN, Ref::BALANCE_TYPE_IN, $balance);

					//6、增加保险首扣
					$result &= WalletHelper::takeInsuranceFee($orderData);

					if ($result) {
						$transaction->commit();
						self::pushToProviderNotice($order_id, self::PUSH_PROVIDER_TYPE_PAY);
					}
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	//获取旧表的订单状态
	public static function getOldOrderType($status)
	{
		$data = [
			0 => '发报中',
			1 => '确认',
			2 => '已取消',
			3 => '已到达',
			4 => '已支付',
			5 => '已评价',
			6 => '待支付',
		];

		return isset($data[$status]) ? $data[$status] : $status;
	}

	//删除订单(旧表)
	public static function oldWorkerDelete($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$status = [2, 4, 5]; //已取消，已支付，已评价
			$order  = (new Query())->select('id, orderid')->from(self::OLD_ORDER_TBL)->where(['orderid' => $params['order_no'], 'status' => $status])->one();
			if ($order) {
				$updateData = [
					's_sure'      => 6,
					'update_time' => time(),
				];

				$affect = Yii::$app->db->createCommand()->update(self::OLD_ORDER_TBL, $updateData, ['id' => $order['id']])->execute();
				$result = $affect > 0 ? true : false;
				$result &= self::saveLogContent($order['id'], 'worker_delete', $updateData, '小帮删除订单');
				if ($result) {
					$result = [
						'order_no' => $order['orderid']
					];
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	//保存输入的地址
	//type=1是小帮出行，2是小帮快送
	public static function saveHistoryAddress($params, $user_id, $type = 1)
	{
		$result  = false;
		$history = HistoryAddress::find()->select(['end_address'])->where(['end_address' => $params['end_address'], 'user_id' => $user_id, 'type' => $type])->one();
		if (!$history) {
			$history    = new HistoryAddress();
			$insertData = [
				'user_id'            => $user_id,
				'type'               => $type,
				'end_location'       => isset($params['end_location']) ? $params['end_location'] : null,
				'end_address'        => isset($params['end_address']) ? $params['end_address'] : null,  //地方名
				'end_address_detail' => isset($params['end_address_detail']) ? $params['end_address_detail'] : null,  //详细地址
				'create_time'        => time(),
			];
			if ($insertData['end_location'] && $insertData['end_address'] && $insertData['end_address_detail']) {

				$history->attributes = $insertData;
				$history->save() ? $result = true : Yii::error("history save:" . json_encode($history->getErrors()));
			}
		}

		return $result;
	}

	//存在未完成订单  不允许接单
	public static function notFinishOrder($provider_id)
	{
		$result = false;
		$model  = Order::findOne(['provider_id' => $provider_id, 'order_status' => Ref::ORDER_STATUS_DEFAULT, 'cate_id' => Ref::CATE_ID_FOR_MOTOR]);

		if ($model) {
			$result = true;
		}

		return $result;
	}
}

