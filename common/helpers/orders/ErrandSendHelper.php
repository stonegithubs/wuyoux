<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/9/22
 */

namespace common\helpers\orders;

use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderFee;
use common\helpers\payment\WalletHelper;
use common\helpers\utils\RegionHelper;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

//帮我送Helper
class ErrandSendHelper extends ErrandHelper
{

	public static function getLowPrice($user_location, $user_city_id)
	{
		$city_price = RegionHelper::getCityPrice($user_location, $user_city_id, Ref::CATE_ID_FOR_ERRAND_SEND);
		$low_price  = $city_price['range_init_price'] + $city_price['service_fee'];   //初始距离价格+服务费

		return sprintf("%.2f", $low_price);
	}

	public static function checkCanOrder($user_id)
	{
		//TODO 显示帮我送的订单
		return parent::isCanOrder($user_id, Ref::CATE_ID_FOR_ERRAND_SEND);
	}

	public static function getOrderCateList()
	{
		//图片从OSS读取
		return [
			[
				'name'         => '其他',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_other_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_other_selected.png",
			],
			[
				'name'         => '文件',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_file_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_file_selected.png",
			],
			[
				'name'         => '普通物品',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_ordinary_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_ordinary_selected.png",
			],
			[
				'name'         => '贵重物品',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_precious_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_precious_selected.png",
			],
			[
				'name'         => '易碎',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_fragile_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_fragile_selected.png",
			],
			[
				'name'         => '衣服',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_clothes_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_clothes_selected.png",
			],
			[
				'name'         => '鲜花',
				'src'          => ImageHelper::OSS_URL . "/app/tags/icon/icon_flower_default.png",
				'src_selected' => ImageHelper::OSS_URL . "/app/tags/icon/icon_flower_selected.png",
			]
		];

	}


	//TODO 需要更新原型图
	public static function getCalc($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no']]);//->getAttributes(['order_amount','order_id']);
		if ($model) {
			$errand       = OrderErrand::findOne(['order_id' => $model->order_id]);//->getAttributes(['total_fee','first_fee']);
			$first_fee    = isset($errand->first_fee) ? $errand->first_fee : 0;
			$total_fee    = isset($errand->total_fee) ? $errand->total_fee : 0;
			$order_amount = $model->order_amount;
			$city_id      = isset($params['user_city']) ? $params['user_city'] : $model->city_id;

			$price_data             = RegionHelper::getCityPrice($model->start_location, $city_id, $model->cate_id);
			$params['online_money'] = $params['online_money'] * $price_data['online_money_discount'];
			$orderHelper            = new OrderHelper();
			$calc                   = $orderHelper->getOrderCalc($order_amount, $params);

			$range          = number_format($price_data['range_init'] / 1000, 2);
			$card_amount    = sprintf("%.2f", $calc['card_amount']);
			$amount_payable = sprintf("%.2f", $calc['amount_payable']);
			$online_money   = sprintf("%.2f", $calc['online_money']);
			$total_fee      = sprintf("%.2f", $total_fee);
			$order_amount   = sprintf("%.2f", ($order_amount - $first_fee));
			$service_price  = sprintf("%.2f", $errand->service_price);
			$discount       = sprintf("%.2f", $calc['discount']);
			$allData        = RegionHelper::getPriceByDay($model->city_id, $model->area_id, $model->cate_id);
			$service_fee    = isset($allData['night_service_fee']) ? $allData['night_service_fee'] : 0;
			$html
							= <<<EOF
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1"><title>订单明细</title> <style>body, table{ font-size:13px; margin:0; padding:0; color:#888;font-family: Helvetica, Tahoma, Arial, "Hiragino Sans GB", "Hiragino Sans GB W3", "Microsoft YaHei", STXihei, STHeiti, Heiti, SimSun, sans-serif;font-weight: normal;} .je{ font-size:26px;margin-top: 1em;margin-right:5px;color: #FF0000;} h2{ font-size: 15px;font-weight: normal;text-indent: .5em; line-height:32px; padding-top:20px;color:#333;} p{padding-left: 2em;line-height: 22px;margin: .5em;}
table{ width:80%; margin:auto;}
table td{ line-height:28px;}
</style></head><body><table border="0" cellpadding="0" cellspacing="0">
<tbody>
  <tr>
    <td height="45" colspan="2" align="center"><h2>— 合计预估 —</h2></td>
    </tr>
  <tr>
  <tr>
    <td height="45" colspan="2" align="center"><span class="je">$amount_payable</span>元</td>
    </tr>
  <tr>
    <td width="50%">小帮赏金</td>
    <td align="right"><span>$order_amount<span>元</td>
  </tr>
  <tr>
    <td>卡券抵扣</td>
    <td align="right"><span>$card_amount<span>元</td>
  </tr>
</tbody>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="0">
<tbody>
  <tr>
    <td height="45" colspan="2" align="center"><h2>— 计价规则 —</h2></td>
    </tr>
  <tr>
    <td width="45%">{$range}公里内</td>
    <td align="right"><span>{$price_data['range_init_price']}</span>元起</td>
  </tr>
    <tr>
    <td width="45%">超出{$range}公里</td>
    <td align="right">每公里加<span>{$price_data['range_unit_price']}</span>元</td>
  </tr>
    <tr>
    <td width="45%">夜晚({$price_data['night_time']}-{$price_data['day_time']})</td>
    <td align="right">夜间服务费<span>{$service_fee}</span>元</td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td height="45" colspan="2" align="center"><h2>— 体积重量计算 —</h2></td>
  </tr>
  <tr>
    <td width="45%">长宽高相加>80cm</td>
    <td align="right">每10cm加1元<span></span></td>
  </tr>
  <tr>
    <td width="45%">5kg<重量≤25kg</td>
    <td align="right">每1kg加1元,不超过25kg<span></span></td>
  </tr>
</tbody>
</table>
</body>
</html>
EOF;
			$result         = [
				'card_amount'    => $card_amount,
				'amount_payable' => $amount_payable,
				'online_money'   => $online_money,
				'total_fee'      => $total_fee,
				'order_amount'   => $order_amount,
				'service_price'  => $service_price,
				'discount'       => $discount,
				'html'           => htmlspecialchars_decode($html),
			];
		}

		return $result;
	}

	//用户任务页和详情页
	public static function userTaskAndDetail($params)
	{

		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {
				$total_fee     = doubleval($errand->total_fee);
				$service_fee   = doubleval($errand->service_price) * $errand->service_qty;
				$order_amount  = $total_fee + $service_fee;           //订单总金额 = 总小费 + 服务时长
				$distance_text = "约" . UtilsHelper::distance($errand->order_distance);
				$photo_url     = ImageHelper::getErrandImageUrlByIdRef($order->order_id);
				$result        = [
					'order_no'        => $order->order_no,
					'order_time'      => isset($order->payment_time) ? date("m-d H:i", $order->payment_time) : null,        //发单时间 就是支付时间
					'order_type'      => "小帮快送-" . ErrandHelper::getErrandType($errand->errand_type),                    //发单类型
					'content'         => $errand->errand_content,                                                            //发单内容
					'start_address'   => $order->start_address,                                                              //购买地址
					'end_address'     => $order->end_address,                                                                //收货地址
					'distance_text'   => $distance_text,                                                                     //取货地址距离收货地址多远
					'service_time'    => isset($errand->service_time) ? date("Y-m-d H:i:00", $errand->service_time) : '',    //收货时间
					'service_price'   => sprintf("%.2f", $errand->service_price),                                            //服务费用
					'payment_type'    => TransactionHelper::getPaymentType($order->payment_id),                              //支付方式
					'order_amount'    => sprintf("%.2f", $order_amount),                                                    //订单总金额
					'publish_time'    => isset($order->payment_time) ? date("m-d H:i", $order->payment_time) : null,        //发布时间 就是支付时间
					'robbed_time'     => isset($order->robbed_time) ? date("m-d H:i", $order->robbed_time) : null,            //接单时间
					'begin_time'      => isset($errand->begin_time) ? date("m-d H:i", $errand->begin_time) : null,            //开始时间
					'finish_time'     => isset($errand->finish_time) ? date("m-d H:i", $errand->finish_time) : null,        //完成时间
					'errand_status'   => $errand->errand_status,                                                            //快送状态
					'robbed'          => $order->robbed,
					'total_fee'       => sprintf("%.2f", $errand->total_fee),
					'receiver_mobile' => $errand->mobile,
					'photo_url'       => $photo_url ? $photo_url : null,                            //商品图片
					'cancel_time'     => isset($order->cancel_time) ? date('m-d H:i', $order->cancel_time) : null,
					'user_mobile'     => $order->user_mobile,
					'spend_time'      => 0,
				];

				//抢单后 小帮信息
				if ($order->robbed == Ref::ORDER_ROBBED) {

					$provider = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->provider_address);
					$result   = array_merge($result, $provider);
				}

				//完成后 评价信息
				if ($errand->errand_status == Ref::ERRAND_STATUS_FINISH) {

					$evaluate = EvaluateHelper::getEvaluateInfo($order->order_no);
					if ($evaluate) {

						$result['evaluate']     = $evaluate;
						$result['can_evaluate'] = 0;    //有评价信息 不能评价

					} else {

						$result['evaluate']     = null;
						$result['can_evaluate'] = 1;    //无评价信息 能评价
					}
				}

				//任务中 取消内容
				if ($params['current_page'] == "task") {
					if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_APPLY) {
						$result['cancel_content'] = self::CANCEL_PROVIDER_APPLY_MSG;
					}

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE) {
						$result['cancel_content'] = self::CANCEL_PROVIDER_AGREE_MSG;
					}

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_DISAGREE) {
						$result['cancel_content'] = self::CANCEL_PROVIDER_DISAGREE_MSG;
					}

					//如果是客服的消息
					if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY || $errand->cancel_type == Ref::ERRAND_CANCEL_USER_NOTIFY) {
						$result['cancel_content'] = $order->order_status == Ref::ORDER_STATUS_CALL_OFF
							? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;
					}
					$result['cancel_type'] = $errand->cancel_type;
					$result['spend_time']  = $errand->begin_time ? time() - $errand->begin_time : null;    //在任务页是计算的时间
				} else {

					if ($errand->begin_time && $order->order_status != Ref::ORDER_STATUS_DOING) {    //取消的时长
						$result['spend_time'] = $errand->begin_time ? $order->cancel_time - $errand->begin_time : null;
					}

					if ($errand->finish_time) {    //完成的时长
						$result['spend_time'] = $errand->begin_time ? $errand->finish_time - $errand->begin_time : null;
					}
				}
				//取消后 数据显示
				if ($params['current_page'] == 'cancel') {
					$result['order_status'] = OrderHelper::getOrderTypeShow($order->order_status);  //TODO 按照文档显示正确的状态
				}

			}
		}

		return $result;
	}

	//抢单成功任务页和详情
	public static function workerTaskAndDetail($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {
				$total_fee     = doubleval($errand->total_fee);
				$service_fee   = doubleval($errand->service_price) * $errand->service_qty;
				$order_amount  = $total_fee + $service_fee;           //订单总金额 = 总小费 + 服务时长
				$distance_text = "约" . UtilsHelper::distance($errand->order_distance);
				$photo_url     = ImageHelper::getErrandImageUrlByIdRef($order->order_id);
				$result        = [
					'order_no'          => $order->order_no,
					'content'           => $errand->errand_content,                                    //内容
					'service_time'      => UtilsHelper::todayTimeFormat($errand->service_time),                //预约收货时间
					'start_location'    => UtilsHelper::checkVersionCoord($params, $order->start_location),        //购买地坐标
					'start_address'     => $order->start_address,            //购买地地址
					'end_location'      => UtilsHelper::checkVersionCoord($params, $order->end_location),            //送货地坐标
					'end_address'       => $order->end_address,            //送货地地址
					'distance_text'     => $distance_text,                 //取货地址距离收货地址多远
					'starting_distance' => UtilsHelper::distance($errand->starting_distance),//起点距离我多少米
					'ending_distance'   => UtilsHelper::distance(0),                                    //终点与小帮距离
					'order_amount'      => sprintf("%.2f", $order_amount),                    //服务费用（包含小费）
					'receiver_mobile'   => $errand->mobile,            //收货电话
					'photo_url'         => $photo_url ? $photo_url : null,    //商品图片
					'begin_time'        => $errand->begin_time,
					'finish_time'       => $errand->finish_time,
					'errand_status'     => $errand->errand_status,
					'cancel_type'       => $errand->cancel_type,
					'order_time'        => isset($order->payment_time) ? date("m-d H:i", $order->payment_time) : null,   //下单时间 就是支付时间
					'payment_type'      => TransactionHelper::getPaymentType($order->payment_id),    //支付方式
					"order_status_text" => OrderHelper::getOrderTypeShow($order->order_status),      // 按照文档显示正确的状态
					'user_mobile'       => $order->user_mobile,
					'spend_time'        => 0,
					'total_fee'         => sprintf("%.2f", $total_fee),        //小费
				];

				//进行中 计算终点到小帮的距离
				if ($order->order_status == Ref::ORDER_STATUS_DOING) {//订单进行中才计算 终点到小帮的距离
					$route                     = AMapHelper::bicycling(AMapHelper::coordToStr($order->provider_location), AMapHelper::coordToStr($order->end_location));
					$ending_distance           = is_array($route) ? $route['distance'] : '0'; //终点到小帮的距离
					$result['ending_distance'] = UtilsHelper::distance($ending_distance);
				}

				//用户信息
				$userInfo               = UserHelper::getUserInfo($order->user_id);
				$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "帮帮用户";
				$result['mobile']       = isset($userInfo['mobile']) ? $userInfo['mobile'] : $order->user_mobile;
				$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
				$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);

				//任务中 取消内容
				if ($params['current_page'] == 'task') {

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_APPLY) {
						$result['cancel_content'] = self::CANCEL_USER_APPLY_MSG;
					}

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_AGREE) {
						$result['cancel_content'] = self::CANCEL_USER_AGREE_MSG;
					}

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_DISAGREE) {
						$result['cancel_content'] = self::CANCEL_USER_DISAGREE_MSG;
					}

					//如果是客服的消息
					if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY || $errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_NOTIFY) {
						$result['cancel_content'] = $order->order_status == Ref::ORDER_STATUS_CALL_OFF
							? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;
					}
					$result['spend_time'] = $errand->begin_time ? time() - $errand->begin_time : 0;    //在任务页是计算的时间
				} else {

					if ($errand->begin_time && $order->order_status != Ref::ORDER_STATUS_DOING) {    //取消的时长
						$result['spend_time'] = $errand->begin_time ? $order->cancel_time - $errand->begin_time : 0;
					}

					if ($errand->finish_time) {    //完成的时长
						$result['spend_time'] = $errand->begin_time ? $errand->finish_time - $errand->begin_time : 0;
					}
				}
			}
		}

		return $result;
	}

	//内容推送给用户
	public static function pushToUserNotice($order_no, $type, $params = [])
	{
		//1.小帮已接收订单 快送状态变更通知
		//2.平台自动取消订单通知
		//3.申请取消订单通知
		//4.商品支付通知
		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$pushData = [];
			$canPush  = true;
			if ($type == self::PUSH_USER_TYPE_TASK_PROGRESS) {//抢单和流程状态变更通知

				$inform = [
					Ref::ERRAND_STATUS_PICKED => '您的订单已经被小帮接受，请查看!',
					Ref::ERRAND_STATUS_DOING  => '您的订单正在进行中',
					Ref::ERRAND_STATUS_FINISH => '您的订单小帮已经完成',
				];

				$errand_status              = $params['errand_status'];
				$pushData['glx']            = Ref::GETUI_TYPE_GRAB_NOTICE;//个推类型
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_SEND_WORKER_TASK;
				$pushData['inform_content'] = isset($inform[$errand_status]) ? $inform[$errand_status] : '您的订单已经被小帮接受，请查看!';
				$pushData['errand_status']  = $errand_status;

				isset($inform[$errand_status]) ? null : $canPush = false;    //联系客户不需要推送
//小帮信息
				$providerInfo                = UserHelper::getShopInfo($order->provider_id);
				$id_image                    = isset($providerInfo['shops_photo']) ? $providerInfo['shops_photo'] : 0;
				$pushData['provider_name']   = isset($providerInfo['shops_name']) ? $providerInfo['shops_name'] : "无忧帮帮";
				$pushData['provider_mobile'] = $order->provider_mobile;
				$pushData['provider_photo']  = ImageHelper::getUserPhoto($id_image);
				$pushData['task_time']       = date("m-d H:i"); //任务更新时间

			}

			if ($type == self::PUSH_USER_TYPE_AUTO_CANCEL) {    //平台自动取消
				$pushData['glx']            = Ref::GETUI_TYPE_CANCEL_NOTICE; //个推类型
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_SEND_WORKER_TASK;
				$pushData['cancel_type']    = "auto_cancel";
				$pushData['inform_content'] = "您的订单暂无人接单\n 系统已自动取消";    //App针对\\处理
			}

			if ($type == self::PUSH_USER_TYPE_CANCEL_PROGRESS) {    //申请取消流程

				$inform = [
					Ref::ERRAND_CANCEL_PROVIDER_APPLY    => '小帮申请取消订单',
					Ref::ERRAND_CANCEL_PROVIDER_AGREE    => '小帮同意取消订单!',
					Ref::ERRAND_CANCEL_PROVIDER_DISAGREE => '小帮不同意取消订单!',
					Ref::ERRAND_CANCEL_DEAL_NOTIFY       => isset($params['deal_msg']) ? $params['deal_msg'] : ErrandHelper::CANCEL_DEAL_NOTIFY_MSG,
				];

				$cancel_type                   = $params['cancel_type'];
				$pushData['glx']               = Ref::GETUI_TYPE_CANCEL_NOTICE; //个推类型
				$pushData['to']                = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']               = PushHelper::ERRAND_SEND_WORKER_CANCEL;    //TODO 更改
				$pushData['cancel_type']       = $cancel_type;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!';

				$current_page = 'task';
				if ($cancel_type == Ref::ERRAND_CANCEL_USER_AGREE || $cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE)
					$current_page = 'cancel';

				$pushData['current_page'] = $current_page;
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

	//内容推送给小帮
	public static function pushToProviderNotice($order_no, $type, $params)
	{
		//1订单确认
		//2.申请取消订单通知
		//3.商品支付通知
		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$pushData = [];
			$canPush  = true;
			if ($type == self::PUSH_PROVIDER_TYPE_CONFIRM) {    //订单确认

				$pushData['glx']            = Ref::GETUI_TYPE_USER_CONFIRM;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_SEND_USER_CONFIRM;
				$pushData['inform_content'] = '您的订单已经确认完成';
			}

			if ($type == self::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS) {    //申请取消通知
				$inform                        = [
					Ref::ERRAND_CANCEL_USER_APPLY    => '用户申请取消订单!',
					Ref::ERRAND_CANCEL_USER_AGREE    => '用户同意取消订单!',
					Ref::ERRAND_CANCEL_USER_DISAGREE => '用户不同意取消订单!',
					Ref::ERRAND_CANCEL_DEAL_NOTIFY   => isset($params['deal_msg']) ? $params['deal_msg'] : self::CANCEL_DEAL_NOTIFY_MSG,
				];
				$cancel_type                   = $params['cancel_type'];
				$pushData['glx']               = Ref::GETUI_TYPE_CANCEL_NOTICE;
				$pushData['to']                = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']               = PushHelper::ERRAND_SEND_USER_CANCEL;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!';
				$pushData['cancel_type']       = $cancel_type;

				$current_page = 'task';
				if ($cancel_type == Ref::ERRAND_CANCEL_USER_AGREE || $cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE)
					$current_page = 'cancel';

				$pushData['current_page'] = $current_page;
				isset($inform[$cancel_type]) ? null : $canPush = false;
			}

			if ($type == self::PUSH_PROVIDER_SMALL_FEE) {    //通知支付小费

				$pushData['glx']            = Ref::GETUI_TYPE_CUSTOM_FEE;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_SEND_FEE_NOTICE;
				$pushData['total_fee']      = self::getTotalFee($order->order_id);
				$pushData['fee']            = $params['fee'];
				$pushData['order_amount']   = $order->order_amount;
				$pushData['inform_content'] = str_replace("{fee}", $params['fee'], self::ADD_CUSTOM_FEE_MSG);
				$pushData['current_page']   = $params['errand_status'] == Ref::ERRAND_STATUS_FINISH ? 'detail' : 'task';
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


	//1、支付成功更新并推送通知
	public static function addCustomFeeSuccess($transaction_no, $trade_no, $payment_id, $fee, $remark = null, $data = null)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();

		try {

			$data = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);

			if ($data) {
				$fee_id   = $data['ids_ref'];
				$orderFee = OrderFee::findOne(['fee_id' => $fee_id, 'status' => Ref::PAY_STATUS_WAIT, 'type' => Ref::FEE_TYPE_PROD]);

				if ($orderFee) {

					$order_id = $orderFee->ids_ref;
					$order    = Order::findOne(['order_id' => $order_id]);
					$errand   = OrderErrand::findOne(['order_id' => $order_id]);
					if ($order && $orderFee && $errand) {

						$total_fee = self::getTotalFee($order->order_id);
						$total_fee += doubleval($orderFee->amount);

						//更新用户   订单总金额 = 服务单价 + 总小费
						$new_order_amount = doubleval($errand->service_price) * $errand->service_qty + $total_fee;
						$updateData       = [
							'total_fee'    => $total_fee,                //errand
							'status'       => Ref::PAY_STATUS_COMPLETE,    //fee
							'order_amount' => $new_order_amount,        //order
							'payment_id'   => $payment_id,        //order,fee
						];

						$orderFee->attributes = $updateData;
						$orderFee->save() ? $result = true : Yii::error("SmallFeePaySuccess fee save:" . json_encode($orderFee->getErrors()));

						$order->attributes = $updateData;

						if (!$order->save()) {
							Yii::error("SmallFeePaySuccess order save:" . json_encode($order->getErrors()));
							$result &= false;
						}

						$errand->attributes = $updateData;
						if (!$errand->save()) {
							Yii::error("SmallFeePaySuccess errand save:" . json_encode($errand->getErrors()));
							$result &= false;
						}
						$updateData = array_merge($updateData, ['小费表ID' => $orderFee->fee_id, '本次添加小费' => $orderFee->amount]);
						$result     &= self::saveLogContent($order_id, 'small_fee_pay_success', $updateData, '小费支付成功更改记录');//记录业务日志


						//支付成功后，需要冻结这笔费用
						if ($order->payment_id == Ref::PAYMENT_TYPE_BALANCE && $orderFee->amount > 0) {
							//冻结用户金额
							$result &= WalletHelper::frozenMoney($order->user_id, $orderFee->amount);
						}

						//用户收支明细表
						if ($orderFee->amount > 0) {

							$result &= WalletHelper::userIncomePay('2', '5', $order->user_id, $data['transaction_no'], $orderFee->amount, "小帮快送-支付小费");
						}


						if ($result) {

							$result = [
								'transaction_no' => $transaction_no,
								'fee'            => $orderFee->amount,
								'errand_status'  => $errand->errand_status,
							];
							$transaction->commit();
							ErrandSendHelper::pushToProviderNotice($order->order_no, ErrandSendHelper::PUSH_PROVIDER_SMALL_FEE, $result);    //TODO 队列

							if ($order->robbed == Ref::ORDER_ROB_NEW)    //加了小费未接单，自动再发单
							{
								QueueHelper::errandSendOrder($order->order_id);//重新派发订单
							}
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

	/**
	 *
	 * 用户确认
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function userConfirm($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {
				$errand          = OrderErrand::findOne(['order_id' => $order->order_id]);
				$total_fee       = self::getTotalFee($order->order_id);    //小费
				$status          = $errand->errand_status;
				$discount        = $order->discount;                //总优惠
				$online_money    = $order->online_money;            //在线宝扣除的金额
				$service_fee     = doubleval($errand->service_price) * $errand->service_qty;
				$estimate_amount = $total_fee + $service_fee;           //预计收到金额也是订单总金额 = 商品费用 + 服务时长
				$pay_amount      = $total_fee + $service_fee - $discount;    //用户实际支付 = 小费 + 服务时长 - 折扣
				$orderData       = ArrayHelper::toArray($order);
				$actual_amount   = WalletHelper::takeMoney($orderData, $estimate_amount); //小帮金额是已经抽佣后的金额

				if ($status == Ref::ERRAND_STATUS_FINISH) {
					$updateData = [
						'provider_estimate_amount' => $estimate_amount,
						'provider_actual_amount'   => $actual_amount,
						'order_status'             => Ref::ORDER_STATUS_COMPLETED,
						'finish_time'              => time(),
					];

					$order->attributes = $updateData;
					$order->save() ? $result = true : Yii::error("save user confirm:" . json_encode($order->getErrors()));

					//记录业务日志
					$result &= self::saveLogContent($order->order_id, 'user_confirm', $updateData, '用户确认订单');

					//确认后需要把钱转到小帮账号
					//1、用户资金变化
					if ($order->payment_id == Ref::PAYMENT_TYPE_BALANCE && $order->amount_payable > 0) {

						$result &= WalletHelper::handleUserBalance($order->user_id, $pay_amount, $online_money);
					}

					//2、小帮资金收入变化
					$result           &= WalletHelper::handleShopBalance($order->provider_id, $actual_amount);
					$shop             = UserHelper::getShopInfo($order->provider_id);
					$provider_user_id = isset($shop['uid']) ? $shop['uid'] : 0;

					//3、店铺收支明细
					$balance = isset($shop['shops_money']) ? $shop['shops_money'] : $actual_amount;
					$result  &= WalletHelper::handleIncomeShop($order->provider_id, $provider_user_id, $order->order_no, $actual_amount, "小帮快送，收入" . $actual_amount . "元", Ref::PROVIDER_BALANCE_IN, Ref::BALANCE_TYPE_IN, $balance);

					//4、增加保险首扣
					$result &= WalletHelper::takeInsuranceFee($orderData);
				}
				if ($result) {
					$result = [
						'order_no'    => $order->order_no,
						'errand_type' => $errand->errand_type
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

	/**
	 * 小帮拍照
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function takePhoto($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {
				$errand     = OrderErrand::findOne(['order_id' => $order->order_id]);
				$updateData = [
					'errand_status' => Ref::ERRAND_STATUS_PHOTO,
				];

				$errand->attributes = $updateData;
				$errand->save() ? $result = true : Yii::error("save worker progress errand:" . json_encode($order->getErrors()));
				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'worker_progress_errand', $updateData, '小帮工作流程');

				//设置图片
				$result &= ImageHelper::setErrandImage($params['image_id'], $order->order_id);

				if ($result) {

					$result = $updateData;
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}
}

















