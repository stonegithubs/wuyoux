<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/15
 */

namespace common\helpers\orders;


//企业送
use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\BizHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\users\BizInfo;
use common\models\util\BizAgentDistrict;
use common\models\util\BizUserDistrict;
use common\models\util\Region;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class BizSendHelper extends ErrandHelper
{
	const BATCH_PAYMENT_KEY = 'batch_payment';

	const PUSH_TMP_TYPE_ASSIGN_PROVIDER   = 'assign';
	const PUSH_TMP_TYPE_REASSIGN_PROVIDER = 'reassign';
	const PUSH_TMP_TYPE_CANCEL_PROVIDER   = 'cancel';

	//保存临时订单
	public static function saveOrderNow($params)
	{
		$qty   = $params['qty'];
		$count = intval($qty / 5);
		$last  = $qty % 5;

		$item = [];
		for ($j = 0; $j < $count; $j++) {
			$item[$j] = 5;
		}
		if ($last > 0) {
			$item[$j] = $last;
		}

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$no       = date('YmdHis') . rand(00, 99) . sprintf("%02d", $qty);
			$batch_no = "B" . $no;
			foreach ($item as $key => $tmp_qty) {
				$model              = new BizTmpOrder();
				$model->tmp_no      = $no . ($key + 1);
				$model->tmp_qty     = $tmp_qty;
				$model->tmp_status  = Ref::BIZ_TMP_STATUS_WAITE;
				$model->attributes  = $params;
				$model->batch_no    = $batch_no;
				$model->create_time = time();
				$result             = $model->save();
			}

			if ($result) {
				$result = [
					'batch_no' => $batch_no
				];
				$transaction->commit();

				QueueHelper::newOrderNotice($batch_no, 'biz_send');
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 通过区域下单
	 * @param $params
	 * @return bool
	 */
	public static function saveOrderNowForArea($params)
	{
		$result  = false;
		$bizInfo = BizHelper::getBizData($params['user_id']);
		if ($bizInfo) {
			$biz_address     = isset($bizInfo['biz_address']) ? $bizInfo['biz_address'] : null;
			$biz_address_ext = isset($bizInfo['biz_address_ext']) ? $bizInfo['biz_address_ext'] : null;
			$tag_id          = isset($bizInfo['tag_id']) ? $bizInfo['tag_id'] : null;

			$params['city_id']        = isset($bizInfo['city_id']) ? $bizInfo['city_id'] : 0;
			$params['area_id']        = isset($bizInfo['area_id']) ? $bizInfo['area_id'] : 0;
			$params['region_id']      = isset($bizInfo['region_id']) ? $bizInfo['region_id'] : 0;
			$params['start_location'] = isset($bizInfo['biz_location']) ? $bizInfo['biz_location'] : null;
			$params['start_address']  = $biz_address . "," . $biz_address_ext;
			$params['user_mobile']    = isset($bizInfo['biz_mobile']) ? $bizInfo['biz_mobile'] : 0;
			$params['cate_id']        = Ref::CATE_ID_FOR_BIZ_SEND;
			$params['content']        = BizHelper::getTagNameById($tag_id);

			$tmp_no             = date('YmdHis') . rand(00, 99) . sprintf("%02d", $params['qty']);
			$batch_no           = "B" . $tmp_no;
			$model              = new BizTmpOrder();
			$model->tmp_no      = $tmp_no;
			$model->tmp_qty     = $params['qty'];
			$model->tmp_status  = Ref::BIZ_TMP_STATUS_WAITE;
			$model->attributes  = $params;
			$model->batch_no    = $batch_no;
			$model->create_time = time();
			$result             = $model->save();
			if ($result) {
				$result              = [
					'batch_no' => $batch_no,
					'tmp_no'   => $tmp_no
				];
				$params ['biz_name'] = isset($params['biz_name']) ? $params['biz_name'] : "无忧企业";
				$params['tmp_no']    = $tmp_no;
				QueueHelper::newOrderNotice($batch_no, 'biz_send');

				QueueHelper::bizSendOrder($batch_no);
			}
		}

		return $result;
	}

	/**
	 * 临时订单抢单
	 *
	 * @param $params
	 * @return bool
	 */
	public static function saveTmpRobbing($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$bizTmpOrder = BizTmpOrder::findOne(['tmp_no' => $params['tmp_no'], 'robbed' => Ref::ORDER_ROB_NEW, 'tmp_status' => Ref::BIZ_TMP_STATUS_WAITE]);    //TODO 处于进行中 才能抢单 如果取消的情况。
			if ($bizTmpOrder) {

				$updateData              = [
					'robbed'            => Ref::ORDER_ROBBED,
					'robbed_time'       => time(),
					'provider_location' => $params['provider_location'],
					'provider_address'  => $params['provider_address'],
					'provider_mobile'   => $params['provider_mobile'],
					'provider_id'       => $params['provider_id'],
					'starting_distance' => $params['starting_distance'],
					'tmp_status'        => Ref::BIZ_TMP_STATUS_PICKED, //小帮已接单
				];
				$bizTmpOrder->attributes = $updateData;
				$bizTmpOrder->save() ? $result = true : Yii::$app->debug->log_info('biz_save_robbing_error', $bizTmpOrder->getErrors());

				if ($result) {
					$result = [
						'tmp_no' => $bizTmpOrder->tmp_no
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

	//抢单成功任务页和详情 临时订单详情
	public static function tmpOrderDetail($params)
	{
		$result      = false;
		$bizTmpOrder = BizTmpOrder::findOne($params);
		if ($bizTmpOrder) {
			$result = [
				'tmp_no'            => $bizTmpOrder->tmp_no,
				'tmp_status'        => $bizTmpOrder->tmp_status,
				'tmp_qty'           => $bizTmpOrder->tmp_qty,
				'user_mobile'       => $bizTmpOrder->user_mobile,
				'provider_mobile'   => $bizTmpOrder->provider_mobile,
				'provider_location' => $bizTmpOrder->provider_location,
				'provider_address'  => $bizTmpOrder->provider_address,
				'start_location'    => $bizTmpOrder->start_location,
				'start_address'     => $bizTmpOrder->start_address,
				'starting_distance' => $bizTmpOrder->starting_distance,
				'content'           => $bizTmpOrder->content,
				'delivery_area'     => $bizTmpOrder->delivery_area,
				'robbed'            => $bizTmpOrder->robbed,
				'order_time'        => date("Y-m-d H:i", $bizTmpOrder->create_time),
				'batch_no'          => $bizTmpOrder->batch_no,
				'cancel_type'       => $bizTmpOrder->cancel_type
			];
			//企业送信息
			$bizInfo                  = BizHelper::getBizData($bizTmpOrder->user_id);
			$result ['biz_name']      = isset($bizInfo['biz_name']) ? $bizInfo['biz_name'] : "无忧企业";
			$result['biz_avatar_url'] = ImageHelper::getBizAvatarUrl(0);    //TODO 后续修改

			//用户信息
			$userInfo               = UserHelper::getUserInfo($bizTmpOrder->user_id);
			$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "帮帮用户";
			$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
			$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);

			//小帮信息
			$provider = ShopHelper::providerForOrderView($bizTmpOrder->provider_id, $bizTmpOrder->provider_mobile, $bizTmpOrder->provider_address);
			$result   = array_merge($result, $provider);
		}

		return $result;
	}

	//自动取消临时发单
	public static function autoCancelBizTmpOrder($batch_no)
	{
		$condition  = ['batch_no' => $batch_no, 'tmp_status' => Ref::BIZ_TMP_STATUS_WAITE, 'robbed' => Ref::ORDER_ROB_NEW];
		$updateData = ['tmp_status' => Ref::BIZ_TMP_STATUS_CALL_OFF, 'update_time' => time(), 'cancel_type' => Ref::ERRAND_CANCEL_AUTO];

		return BizTmpOrder::updateAll($updateData, $condition);
	}

	//用户任务页和详情页
	public static function userTaskAndDetail($params)
	{

		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {

				$distance_text = "约" . UtilsHelper::distance($errand->order_distance);
				$photo_url     = ImageHelper::getErrandImageUrlByIdRef($order->order_id);
				$result        = [
					'order_no'        => $order->order_no,
					'order_time'      => isset($order->create_time) ? date("m-d H:i", $order->create_time) : null,        //发单时间 就是支付时间
					'order_type'      => "小帮快送-" . ErrandHelper::getErrandType($errand->errand_type),                    //发单类型
					'content'         => $errand->errand_content,                                                            //发单内容
					'start_address'   => $order->start_address,                                                              //购买地址
					'end_address'     => $order->end_address,                                                                //收货地址
					'distance_text'   => $distance_text,                                                                     //取货地址距离收货地址多远
					'service_time'    => isset($errand->service_time) ? date("Y-m-d H:i:00", $errand->service_time) : '',    //收货时间
					'service_price'   => sprintf("%.2f", $errand->service_price),                                            //服务费用
					'payment_type'    => TransactionHelper::getPaymentType($order->payment_id),                              //支付方式
					'order_amount'    => sprintf("%.2f", $order->order_amount),                                                    //订单总金额
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
					'amount_payable'  => $order->amount_payable,
					'card_id'         => $order->card_id,
					'discount'        => $order->discount,
					'payment_status'  => $order->payment_status,    //支付状态
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
					$result['spend_time']  = $errand->begin_time ? time() - $errand->begin_time : 0;    //在任务页是计算的时间
				} else {

					if ($errand->begin_time && $order->order_status != Ref::ORDER_STATUS_DOING) {    //取消的时长
						$result['spend_time'] = $errand->begin_time ? $order->cancel_time - $errand->begin_time : 0;
					}

					if ($errand->finish_time) {    //完成的时长
						$result['spend_time'] = $errand->begin_time ? $errand->finish_time - $errand->begin_time : 0;
					}
				}
				//取消后 数据显示
				if ($params['current_page'] == 'cancel') {
					$result['order_status'] = OrderHelper::getOrderTypeShow($order->order_status);  //TODO 按照文档显示正确的状态
				}

				$result['auto_over_time'] = 0;    //自动扣款小帮到账剩余时间
				if ($errand->finish_time) {
					$over = $errand->finish_time + 60 * 60 * 12 - time();

					$result['auto_over_time'] = $over > 0 ? $over : 0;
				}
			}
		}

		return $result;
	}

	/**
	 * @param $params
	 */
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
				$result        = [
					'order_no'          => $order->order_no,
					'content'           => $errand->errand_content,                                    //配送物品
					'distance_text'     => $distance_text,                 //取货地址距离收货地址多远
					'ending_distance'   => UtilsHelper::distance(0),                                    //终点与小帮距离
					'order_amount'      => sprintf("%.2f", $order_amount),  //服务费用（包含小费）
					'receiver_mobile'   => $errand->mobile,                    //收货电话
					'errand_status'     => $errand->errand_status,
					'cancel_type'       => $errand->cancel_type,
					'order_time'        => isset($order->create_time) ? date("m-d H:i", $order->create_time) : null,   //订单时间
					"order_status_text" => OrderHelper::getOrderTypeShow($order->order_status),      // 按照文档显示正确的状态
					'user_mobile'       => $order->user_mobile,                    //发货商家
					'start_address'     => $order->start_address,
					'start_location'    => $order->start_location,
					'end_address'       => $order->end_address,  //收货地址
					'end_location'      => $order->end_location,  //收货地址坐标
					'spend_time'        => 0,
					'payment_status'    => $order->payment_status,  //支付状态
				];

				if ($order->order_status == Ref::ORDER_STATUS_DEFAULT) {
					$result['order_status_text'] = '配送中';
				}

				//企业送信息
				$bizInfo                  = BizHelper::getBizData($order->user_id);
				$result ['biz_name']      = isset($bizInfo['biz_name']) ? $bizInfo['biz_name'] : "无忧企业";
				$result['biz_avatar_url'] = ImageHelper::getBizAvatarUrl(0);    //TODO 后续修改

				//用户信息
				$userInfo               = UserHelper::getUserInfo($order->user_id);
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
					$result['spend_time'] = $errand->begin_time ? time() - $errand->begin_time : null;    //在任务页是计算的时间
				} else {

					if ($errand->begin_time && $order->order_status != Ref::ORDER_STATUS_DOING) {    //取消的时长
						$result['spend_time'] = $errand->begin_time ? $order->cancel_time - $errand->begin_time : null;
					}

					if ($errand->finish_time) {    //完成的时长
						$result['spend_time'] = $errand->begin_time ? $errand->finish_time - $errand->begin_time : null;
					}
				}

			}
		}

		return $result;
	}

	/**
	 * @param $params
	 * @return bool
	 */
	public static function createOrder($params)
	{

		$result  = false;
		$mobiles = json_decode($params['mobile_group'], true);

		$mobile_group = [];    //过滤号码
		foreach ($mobiles as $mobile) {
			$mobile = strval($mobile);
			if (strlen($mobile) == 11) {
				$mobile_group[] = $mobile;
			}
		}

		$model = BizTmpOrder::findOne(['tmp_no' => $params['tmp_no'], 'tmp_status' => Ref::BIZ_TMP_STATUS_PICKED, 'provider_id' => $params['provider_id']]);
		if ($model && $mobile_group) {
			$transaction = Yii::$app->db->beginTransaction();
			try {

				$order_num = 0;
				$order_ids = [];
				$result    = true;
				foreach ($mobile_group as $mobile) {
					$mobile     = strval($mobile);
					$saveParams = [
						"base"     => [
							'cate_id'           => Ref::CATE_ID_FOR_BIZ_SEND,
							'city_id'           => $model->city_id,
							'region_id'         => $model->area_id ? $model->area_id : $model->city_id,
							'area_id'           => $model->area_id,
							'order_from'        => $model->tmp_from,
							'user_mobile'       => $model->user_mobile,//发货人
							'order_type'        => Ref::ORDER_TYPE_ERRAND,
							'user_id'           => $model->user_id,
							'provider_id'       => $model->provider_id,
							'provider_mobile'   => $model->provider_mobile,
							'provider_location' => $model->provider_location,
							'provider_address'  => $model->provider_address,
							'robbed'            => $model->robbed,
							'robbed_time'       => $model->robbed_time,
							'order_status'      => Ref::ORDER_STATUS_DEFAULT,
						],
						'location' => [
							'user_location'  => $model->user_location,
							'user_address'   => $model->user_address,
							'start_location' => $model->start_location,
							'start_address'  => $model->start_address,
							'end_location'   => null,
							'end_address'    => null
						],
						'errand'   => [
							'errand_status'     => Ref::ERRAND_STATUS_PICKED,
							'service_price'     => 0,                    //小帮赏金
							'service_time'      => $model->create_time,  //发单时间
							'maybe_time'        => $model->create_time + 1800,        //预约收货时间
							'errand_type'       => Ref::ERRAND_TYPE_BIZ,
							'errand_content'    => $model->content,
							'mobile'            => $mobile,    //收货人
							'service_qty'       => 1,
							'starting_distance' => $model->starting_distance,
							'begin_time'        => time(),
							'begin_location'    => $params['current_location'],
							'begin_address'     => $params['current_address'],
						]
					];

					$orderHelper = new OrderHelper();
					$orderHelper->setOrderParams($saveParams);
					$res = $orderHelper->save();
					if (is_array($res)) {
						$order_ids[] = $res['order_id'];
						$order_num   += 1;
						//发送收货人告知配送信息
						QueueHelper::receiverSmsNotice($res['order_no']);
					} else {
						$result = false;
					}

					sleep(0.5);    //防止数据保存重复
				}
				//更新主表
				if ($result) {
					$model->order_ids   = implode(",", $order_ids);
					$model->order_num   = $order_num;
					$model->tmp_status  = Ref::BIZ_TMP_STATUS_INPUT;
					$model->update_time = time();
					$result             = $model->save();
				}

				if ($result) {
					$result = [
						'tmp_no' => $params['tmp_no']
					];
					$transaction->commit();
				}
			}
			catch (Exception $e) {
				$transaction->rollBack();
			}
		}

		return $result;
	}

	public static function getArrivalPrice($start_location, $end_location, $time, $city_id, $area_id, $cate_id_type)
	{
		$route         = AMapHelper::bicycling(AMapHelper::coordToStr($start_location), AMapHelper::coordToStr($end_location)); //订单起点坐标，商家当前坐标
		$distance      = 0;
		$distance_text = '0米';

		if (is_array($route)) {
			$distance      = $route['distance'];
			$distance_text = UtilsHelper::distance($distance);
		}

		$city_price = RegionHelper::getPriceByTime($time, $city_id, $area_id, $cate_id_type);

		$range_init = $city_price['range_init'];//初始距离
		$range      = $distance - $range_init;
		$price      = $city_price['range_init_price'];

		//根据距离得出价格
		if ($range > 0) {
			$price = $city_price['range_init_price'] + $range / 1000 * $city_price['range_unit_price'];
		}

		$price += $city_price['service_fee'];

		return [
			'distance'      => $distance,
			'distance_text' => '约' . $distance_text,
			'price'         => sprintf("%.2f", $price)
		];
	}

	//小帮配送到达
	public static function workerDeliveryArrival($params)
	{

		//开发思路
		//1、价格计算和距离
		//2、订单主表更新状态
		//3、订单从表更新
		//4、推送消息给用户

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $params['order_no'], 'order_status' => Ref::ORDER_STATUS_DEFAULT, 'provider_id' => $params['provider_id']]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
				if ($errand) {

					//计费规则
					$cityInfo['city_id'] = $order->city_id;
					$cityPrice           = self::getArrivalPrice($order->start_location, $params['current_location'], $order->create_time, $order->city_id, $order->area_id, Ref::CATE_ID_FOR_BIZ_SEND);

					// 计价和距离
					$order_amount      = $cityPrice['price'];
					$starting_distance = $cityPrice['distance'];
					$updateOrder       = [
						'order_status' => Ref::ORDER_STATUS_AWAITING_PAY,
						'order_amount' => $order_amount,
						'end_location' => $params['current_location'],
						'end_address'  => $params['current_address'],
						'update_time'  => time(),
					];

					$updateErrand = [
						'errand_status'   => Ref::ERRAND_STATUS_FINISH,
						'order_distance'  => $starting_distance,
						'finish_time'     => time(),
						'finish_location' => $params['current_location'],
						'finish_address'  => $params['current_address'],
						'service_price'   => $order_amount,
						'actual_time'     => time()

					];

					$order->attributes  = $updateOrder;
					$errand->attributes = $updateErrand;

					$result = $order->save();
					$result &= $errand->save();

					$logData = array_merge($updateOrder, $updateErrand);
					//记录业务日志
					$result &= self::saveLogContent($order->order_id, 'delivery_arrival', $logData, '企业送配送到达');

				}

				if ($result) {
					$transaction->commit();
					$data['errand_type']   = Ref::ERRAND_TYPE_BIZ;
					$data['order_no']      = $params['order_no'];
					$data['user_id']       = $order->user_id;
					$data['errand_status'] = Ref::ERRAND_STATUS_FINISH;

					self::pushToUserNotice($params['order_no'], self::PUSH_USER_TYPE_TASK_PROGRESS, $data);
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;

	}

	//后台更新企业送配送到达位置
	public static function updateDeliveryPrice($params)
	{

		//开发思路
		//1、价格计算和距离
		//2、订单主表更新状态
		//3、订单从表更新
		//4、推送消息给用户

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $params['order_no'], 'order_status' => Ref::ORDER_STATUS_AWAITING_PAY]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
				if ($errand) {

					//计费规则
					$cityInfo['city_id'] = $order->city_id;
					$cityPrice           = self::getArrivalPrice($order->start_location, $params['current_location'], $order->create_time, $order->city_id, $order->area_id, Ref::CATE_ID_FOR_BIZ_SEND);

					// 计价和距离
					$order_amount      = $cityPrice['price'];
					$starting_distance = $cityPrice['distance'];
					$updateOrder       = [
						'order_status' => Ref::ORDER_STATUS_AWAITING_PAY,
						'order_amount' => $order_amount,
						'end_location' => $params['current_location'],
						'end_address'  => $params['current_address'],
						'update_time'  => time(),
					];

					$updateErrand = [
						'errand_status'   => Ref::ERRAND_STATUS_FINISH,
						'order_distance'  => $starting_distance,
						'finish_time'     => time(),
						'finish_location' => $params['current_location'],
						'finish_address'  => $params['current_address'],
						'service_price'   => $order_amount,
						'actual_time'     => time()

					];

					$order->attributes  = $updateOrder;
					$errand->attributes = $updateErrand;

					$result = $order->save();
					$result &= $errand->save();

					$logData = array_merge($updateOrder, $updateErrand);
					//记录业务日志
					$result &= self::saveLogContent($order->order_id, 'update_delivery_price', $logData, '后台修改企业送价格');

				}

				if ($result) {
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;

	}

	//临时订单 推送给小帮端
	public static function pushTmpToProviderNotice($tmp_no, $type, $oldProviderId = null)
	{

		$order = BizTmpOrder::findOne(['tmp_no' => $tmp_no]);
		if ($order) {
			$pushData = [];

			if ($type == self::PUSH_TMP_TYPE_ASSIGN_PROVIDER) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_PROVIDER;//个推类型
				$pushData['to']             = Ref::GETUI_TO_BIZ_ORDER;
				$pushData['url']            = PushHelper::TMP_BIZ_SEND_ASSIGN_PROVIDER_NOTICE;
				$pushData['inform_content'] = "平台给您派送了一张企业送订单，请注意查看!";
			}

			if ($type == self::PUSH_TMP_TYPE_CANCEL_PROVIDER) {
				$pushData['glx']            = Ref::GETUI_TYPE_CANCEL_NOTICE;//个推类型
				$pushData['to']             = Ref::GETUI_TO_BIZ_ORDER;
				$pushData['url']            = PushHelper::TMP_BIZ_SEND_PLATFORM_CANCEL;
				$pushData['inform_content'] = "平台已经把您接的企业送订单取消了，请注意查看!";
			}

			if ($type == self::PUSH_TMP_TYPE_REASSIGN_PROVIDER) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_PROVIDER;//个推类型
				$pushData['to']             = Ref::GETUI_TO_BIZ_ORDER;
				$pushData['url']            = PushHelper::TMP_BIZ_REASSIGN_BEFORE;
				$pushData['inform_content'] = "平台把您接的企业送订单改派给别的小帮";
			}

			$provider_id              = $oldProviderId ? $oldProviderId : $order->provider_id;
			$pushData['tmp_no']       = $tmp_no;
			$pushData['provider_id']  = $order->provider_id;
			$pushData['user_id']      = $order->user_id;
			$pushData['push_user_id'] = $order->user_id;
			$pushData['push_role']    = Ref::PUSH_ROLE_PROVIDER;
			$pushData['log_time']     = date("Y-m-d H:i:s");
			QueueHelper::toOneTransmissionForProvider($provider_id, $pushData, 'biz_tmp_send_order');
		}
	}

	//临时订单分割后被抢 推送给用户
	public static function pushTmpToUserNotice($tmp_no, $type)
	{
		$order = BizTmpOrder::findOne(['tmp_no' => $tmp_no]);
		if ($order) {
			$pushData = [];

			$pushData['glx'] = Ref::GETUI_TYPE_GRAB_NOTICE;//个推类型
			$pushData['to']  = Ref::GETUI_TO_ERRAND_ORDER;
			$pushData['url'] = $type;

			$inform = [
				PushHelper::TMP_BIZ_SEND_WORKER_TASK   => '您的订单已经被小帮接受，请查看!',
				PushHelper::TMP_BIZ_SEND_WORKER_INPUT  => '小帮已经录入订单',
				PushHelper::TMP_BIZ_SEND_WORKER_CANCEL => '小帮取消临时订单',
			];

			$pushData['inform_content']  = isset($inform[$type]) ? $inform[$type] : '您的订单已经被小帮接受，请查看!';
			$providerInfo                = UserHelper::getShopInfo($order->provider_id);
			$id_image                    = isset($providerInfo['shops_photo']) ? $providerInfo['shops_photo'] : 0;
			$pushData['provider_name']   = isset($providerInfo['shops_name']) ? $providerInfo['shops_name'] : "无忧帮帮";
			$pushData['provider_mobile'] = $order->provider_mobile;
			$pushData['provider_photo']  = ImageHelper::getUserPhoto($id_image);
			$pushData['tmp_no']          = $order->tmp_no;
			$pushData['batch_no']        = $order->batch_no;
			$pushData['provider_id']     = $order->provider_id;
			$pushData['user_id']         = $order->user_id;
			$pushData['push_user_id']    = $order->user_id;
			$pushData['push_role']       = Ref::PUSH_ROLE_USER;
			$pushData['log_time']        = date("Y-m-d H:i:s");
			QueueHelper::toOneTransmissionForUser($order->user_id, $pushData, 'biz_tmp_send_order');
		}

	}

	//订单内容推送给用户
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
				$pushData['url']            = PushHelper::BIZ_SEND_WORKER_TASK;
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
				$pushData['url']            = PushHelper::BIZ_SEND_WORKER_TASK;
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

				$cancel_type  = $params['cancel_type'];
				$current_page = 'task';
				if ($cancel_type == Ref::ERRAND_CANCEL_USER_AGREE || $cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE)
					$current_page = 'cancel';

				$pushData['glx']               = Ref::GETUI_TYPE_CANCEL_NOTICE; //个推类型
				$pushData['to']                = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']               = PushHelper::BIZ_SEND_WORKER_CANCEL;    //TODO 更改
				$pushData['cancel_type']       = $cancel_type;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!';
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

	//订单内容推送给小帮
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
				$pushData['url']            = PushHelper::BIZ_SEND_USER_CONFIRM;
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
				$pushData['url']               = PushHelper::BIZ_SEND_USER_CANCEL;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!';
				$pushData['cancel_type']       = $cancel_type;

				$pushData['current_page'] = 'task';
				if ($cancel_type == Ref::ERRAND_CANCEL_USER_AGREE || $cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE)
					$pushData['current_page'] = 'cancel';

				isset($inform[$cancel_type]) ? null : $canPush = false;
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

	/**
	 * 用户申请取消订单
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function userCancel($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$updateData = [
					'request_cancel_id'   => $order->user_id,
					'request_cancel_time' => time(),    //数据没有完成时间
					'cancel_type'         => Ref::ERRAND_CANCEL_USER_APPLY
				];

				$order->attributes  = $updateData;
				$errand->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("order user cancel:" . json_encode($order->getErrors()));
				$errand->save() ? $result &= true : Yii::error("errand user cancel:" . json_encode($errand->getErrors()));

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'user_cancel', $updateData, '用户发起取消订单');
				if ($result) {
					$result = [
						'order_no'          => $order->order_no,
						'robbed'            => $order->robbed,
						'cancel_type'       => Ref::ERRAND_CANCEL_USER_APPLY,
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						'errand_type'       => $errand->errand_type,
						'order_from'        => $order->order_from,
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
	 * user取消流程
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function userCancelFlow($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$status = [
				Ref::ORDER_STATUS_DEFAULT,
				Ref::ORDER_STATUS_CANCEL,
				Ref::ORDER_STATUS_CALL_OFF
			];
			$order  = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => $status]);

			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$push_cancel_type          = '';
				$updateData['update_time'] = time();
				$label                     = '用户清空提示信息';

				if ($params['agreed'] == 'yes') { //同意

					$label                      = '用户同意取消订单';
					$push_cancel_type           = Ref::ERRAND_CANCEL_USER_AGREE;
					$updateData['order_status'] = Ref::ORDER_STATUS_DECLINE;
					$updateData['cancel_time']  = time();
				}

				if ($params['agreed'] == 'no') {
					$label            = '用户不同意取消订单';
					$push_cancel_type = Ref::ERRAND_CANCEL_USER_DISAGREE;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE
					|| $errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_DISAGREE
					|| $errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_NOTIFY
				) {
					$push_cancel_type = null;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY) {
					$push_cancel_type = Ref::ERRAND_CANCEL_PROVIDER_NOTIFY;
				}

				$updateData['cancel_type'] = $push_cancel_type;

				$order->attributes  = $updateData;
				$errand->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("order_user_cancel_flow:" . json_encode($order->getErrors()));
				$errand->save() ? $result &= true : Yii::error("error_user_cancel_flow:" . json_encode($errand->getErrors()));
				$result &= self::saveLogContent($order->order_id, 'user_cancel_flow', $updateData, $label);

				if ($result) {
					$result = [
						'order_no'          => $order->order_no,
						'robbed'            => $order->robbed,
						'cancel_type'       => $push_cancel_type,
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						'payment_id'        => $order->payment_id,
						'errand_type'       => $errand->errand_type,
						'order_from'        => $order->order_from,
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
	 * 小帮申请取消
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function workerCancel($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$errand     = OrderErrand::findOne(['order_id' => $order->order_id]);
				$updateData = [
					'request_cancel_id'   => $order->provider_id,
					'request_cancel_time' => time(),
					'cancel_type'         => Ref::ERRAND_CANCEL_PROVIDER_APPLY
				];

				$order->attributes  = $updateData;
				$errand->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("order worker cancel:" . json_encode($order->getErrors()));
				$errand->save() ? $result &= true : Yii::error("errand worker cancel:" . json_encode($errand->getErrors()));

				$result &= self::saveLogContent($order->order_id, 'worker_cancel', $updateData, '小帮发起取消订单');
				if ($result) {
					$result = [
						'order_no'          => $order->order_no,
						'cancel_type'       => Ref::ERRAND_CANCEL_PROVIDER_APPLY,
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						"errand_type"       => $errand->errand_type,
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
	 * worker取消流程
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function workerCancelFlow($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$status = [
				Ref::ORDER_STATUS_DEFAULT,
				Ref::ORDER_STATUS_DECLINE,
				Ref::ORDER_STATUS_CALL_OFF
			];
			$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => $status]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$push_cancel_type          = '';
				$updateData['update_time'] = time();
				$label                     = '小帮清空提示信息';
				if ($params['agreed'] == 'yes') { //同意

					$label                      = '小帮同意取消订单';
					$push_cancel_type           = Ref::ERRAND_CANCEL_PROVIDER_AGREE;
					$updateData['order_status'] = Ref::ORDER_STATUS_CANCEL;
					$updateData['cancel_time']  = time();
				}

				if ($params['agreed'] == 'no') {
					$label            = '小帮不同意取消订单';
					$push_cancel_type = Ref::ERRAND_CANCEL_PROVIDER_DISAGREE;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_AGREE
					|| $errand->cancel_type == Ref::ERRAND_CANCEL_USER_DISAGREE
					|| $errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_NOTIFY
				) {
					$push_cancel_type = null;    //清空type
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY) {
					$push_cancel_type = Ref::ERRAND_CANCEL_USER_NOTIFY;
				}

				$updateData['cancel_type'] = $push_cancel_type;

				$order->attributes  = $updateData;
				$errand->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("order_worker_cancel_flow:" . json_encode($order->getErrors()));
				$errand->save() ? $result &= true : Yii::error("error_worker_cancel_flow:" . json_encode($errand->getErrors()));
				$result &= self::saveLogContent($order->order_id, 'worker_cancel_flow', $updateData, $label);

				if ($result) {

					$result = [
						'order_no'          => $order->order_no,
						'cancel_type'       => $push_cancel_type,
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						'payment_id'        => $order->payment_id,
						'errand_type'       => $errand->errand_type,
						'order_from'        => $order->order_from,
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

	//1、支付成功更新并推送通知
	public static function orderPaymentSuccess($transaction_no, $trade_no, $payment_id, $fee, $remark = null, $data = null, $isDelay = false)
	{
		//开发思路
		//1、流水状态更新
		//2、订单状态更新
		//3、更新优惠券状态
		//4、冻结用户费用
		//5、用户收支明细表

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$data = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);
			if ($data) {

				$result      = true;
				$ids_ref     = $data['ids_ref'];
				$ids_arr     = explode(",", $ids_ref);
				$amount      = $data['fee'];
				$confirmData = [];
				if ($ids_arr) {
					$user_id = 0;
					foreach ($ids_arr as $order_id) {
						$model = Order::findOne(['order_id' => $order_id]);
						if ($model) {
							$updateData        = [
								'order_status'   => Ref::ORDER_STATUS_DOING,
								'payment_status' => Ref::PAY_STATUS_COMPLETE,
								'payment_time'   => time(),
								'payment_id'     => $payment_id,
								'update_time'    => time()
							];
							$model->attributes = $updateData;
							$user_id           = $model->user_id;
							$model->save() ? $result = true : $result = false;
							$result ? null : Yii::error("order pay success save:" . json_encode($model->getErrors()));
							//记录业务日志
							$result &= self::saveLogContent($model->order_id, 'pay_success', $updateData, '支付成功更新订单');

							CouponHelper::beUseCard($model->card_id);//更新卡券
							$confirmData[] = [
								'order_no'    => $model->order_no,
								'user_id'     => $model->user_id,
								'errand_type' => Ref::ERRAND_TYPE_BIZ,
							];
						}
					}

					if ($payment_id == Ref::PAYMENT_TYPE_BALANCE && $amount > 0) {
						//冻结用户金额
						$result &= WalletHelper::frozenMoney($user_id, $amount);
					}

					//用户收支明细表
					if ($amount > 0) {
						$label  = $isDelay ? '自动扣款' : '';
						$result &= WalletHelper::userIncomePay('2', '5', $user_id, $data['transaction_no'], $amount, "小帮快送-企业送费用" . $label);
					}

				} else {
					$result = false;
				}

				if ($result) {
					$transaction->commit();

					if ($isDelay) {    //系统扣款的延迟到账
						foreach ($confirmData as $item) {
							$ttl = YII_ENV_PROD ? 60 * 60 * 12 : 60 * 12;
							QueueHelper::autoConfirmErrandOrder($item, $ttl);
						}
					} else {

						foreach ($confirmData as $item) {    //所有同步确认，后续再更改为队列

							self::userConfirm($item);
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

	//用户确认订单
	public static function userConfirm($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {
				$errand          = OrderErrand::findOne(['order_id' => $order->order_id]);
				$status          = $errand->errand_status;
				$estimate_amount = $order->order_amount;
				$pay_amount      = $order->amount_payable;
				$orderData       = ArrayHelper::toArray($order);
				$actual_amount   = WalletHelper::takeMoney($orderData, $estimate_amount); //小帮金额是已经抽佣后的金额
				$online_money    = $order->online_money;
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
					$result  &= WalletHelper::handleIncomeShop($order->provider_id, $provider_user_id, $order->order_no, $actual_amount, "企业送，收入" . $actual_amount . "元", Ref::PROVIDER_BALANCE_IN, Ref::BALANCE_TYPE_IN, $balance);

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
	 * 取消临时订单
	 * @param $params
	 * @return array|bool
	 */
	public static function workerTmpOrderCancel($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$bizTmpOrder = BizTmpOrder::findOne(['tmp_no' => $params['tmp_no'], 'provider_id' => $params['provider_id'], 'tmp_status' => Ref::BIZ_TMP_STATUS_PICKED]);
			if ($bizTmpOrder) {

				$updateData              = [
					'tmp_status'  => Ref::BIZ_TMP_STATUS_DECLINE,//小帮取消订单
					'cancel_time' => time(),
					'cancel_type' => $params['cancel_type'],
				];
				$bizTmpOrder->attributes = $updateData;
				$bizTmpOrder->save() ? $result = true : Yii::$app->debug->log_info('biz_save_robbing_error', $bizTmpOrder->getErrors());

				if ($result) {
					$result = [
						'tmp_no' => $bizTmpOrder->tmp_no
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
	 * 用户确认取消信息
	 * @param $params
	 * @return array|bool
	 */
	public static function userTmpOrderCancelConfirm($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$bizTmpOrder = BizTmpOrder::findOne(['tmp_no' => $params['tmp_no'], 'user_id' => $params['user_id']]);
			if ($bizTmpOrder) {

				$updateData              = [
					'cancel_time' => time(),
					'cancel_type' => null,
				];
				$bizTmpOrder->attributes = $updateData;
				$bizTmpOrder->save() ? $result = true : Yii::$app->debug->log_info('biz_save_robbing_error', $bizTmpOrder->getErrors());

				if ($result) {
					$result = [
						'tmp_no' => $bizTmpOrder->tmp_no
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


	//批量计算订单使用的优惠券
	public static function getBatchPaymentDetail($params)
	{
		//开发思路
		//1、列出前30条配送到达订单
		//2、循环获取可用优惠券
		//3、把订单数据存储到缓存中防止自动生成的优惠券被别的订单使用

		$result    = false;
		$user_id   = $params['user_id'];
		$orderData = Order::find()->select(['order_id', 'order_amount'])->where(['user_id' => $params['user_id'], 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND, 'order_status' => Ref::ORDER_STATUS_AWAITING_PAY])
			->orderBy(['create_time' => SORT_ASC])->limit(10)->asArray()->all();
		if ($orderData) {

			$cardList     = CouponHelper::bizCardAvailableData($user_id);
			$data         = [
				'order_num'      => 0,//总订单
				'total_amount'   => 0,//总计金额
				'discount'       => 0,//优惠金额
				'amount_payable' => 0,//实付金额
				'card_num'       => 0,//优惠券张数
				'card_available' => $cardList ? count($cardList) : 0,    //可用优惠券数量
			];
			$orderDataArr = ArrayHelper::getColumn($orderData, 'order_amount');
			array_multisort($orderDataArr, SORT_DESC, $orderData);

			$data['order_num'] = count($orderData);
			$cardIdArr         = [];
			foreach ($orderData as $key => $item) {
				$order_amount                      = $item['order_amount'];
				$data['total_amount']              += $order_amount;
				$cardData                          = CouponHelper::bizCardDataByAuto($user_id, $order_amount, $cardIdArr);
				$orderData[$key]['card_id']        = 0;
				$orderData[$key]['card_parent_id'] = 0;
				$orderData[$key]['discount']       = 0;
				if ($cardData) {
					$cardIdArr[]                       = $cardData['card_id'];
					$data['card_num']                  += 1;
					$data['discount']                  += $cardData['price'];
					$orderData[$key]['card_id']        = $cardData['card_id'];
					$orderData[$key]['discount']       = $cardData['price'];
					$orderData[$key]['card_parent_id'] = $cardData['parent_id'];
				}
			}

			$amount_payable           = $data['total_amount'] - $data['discount'];
			$result                   = $data;
			$result['amount_payable'] = sprintf("%.2f", $amount_payable);
			$result['discount']       = sprintf("%.2f", $data['discount']);
			$result['total_amount']   = sprintf("%.2f", $data['total_amount']);
			$user_info                = UserHelper::getUserInfo($params['user_id'], 'money');
			$result['balance_pay']    = $user_info['money'] > $result['amount_payable'] ? 1 : 0;

			$order_key = self::BATCH_PAYMENT_KEY . $user_id;
			$orderData = ArrayHelper::index($orderData, 'order_id');
			Yii::$app->cache->set($order_key, $orderData, 120);
		}

		return $result;
	}

	public static function getPaymentCache($params)
	{
		$order_key = self::BATCH_PAYMENT_KEY . $params['user_id'];

		return Yii::$app->cache->get($order_key);
	}


	//获取优惠券匹配页数据
	public static function getCouponMatchData($orderData, $user_id)
	{
		//开发思路
		//1、可用优惠券列表
		//2、计算order data里面使用
		$cardList = CouponHelper::bizCardAvailableData($user_id);

		$data = [
			'order_num'      => 0,//总订单
			'total_amount'   => 0,//总计金额
			'discount'       => 0,//优惠金额
			'amount_payable' => 0,//实付金额
			'card_num'       => 0,//优惠券张数
			'card_list'      => null,//订单列表
			'card_available' => count($cardList),
		];

		$cardCount         = [];
		$data['order_num'] = count($orderData);
		foreach ($orderData as $order) {

			$parent_id = $order['card_parent_id'];
			isset($cardCount[$parent_id]) ? $cardCount[$parent_id] += 1 : $cardCount[$parent_id] = 1;

			$data['total_amount'] += $order['order_amount'];
			$data['discount']     += $order['discount'];
			$data['card_num']     += $order['card_id'] > 0 ? 1 : 0;
		}

		if ($cardList) {
			foreach ($cardList as $key => $item) {
				$cardList[$key]['selected'] = 0;
				if (isset($cardCount[$item['parent_id']])) {
					$cardList[$key]['selected'] = $cardCount[$item['parent_id']];
				}
			}

			$data['card_list'] = $cardList;
		}

		$amount_payable           = $data['total_amount'] - $data['discount'];
		$result                   = $data;
		$result['amount_payable'] = sprintf("%.2f", $amount_payable);
		$result['discount']       = sprintf("%.2f", $data['discount']);
		$result['total_amount']   = sprintf("%.2f", $data['total_amount']);
		$user_info                = UserHelper::getUserInfo($user_id, 'money');
		$result['balance_pay']    = $user_info['money'] > $result['amount_payable'] ? 1 : 0;
		$order_key                = self::BATCH_PAYMENT_KEY . $user_id;
		Yii::$app->cache->set($order_key, $orderData, 120);    //继续保存订单数据

		return $result;
	}

	//优惠劵智能计算
	public static function smartCouponCal($orderData, $params)
	{
		//开发思路
		//1、订单数据从高到低排序
		//2、选择优惠券的数据从高到低排序
		//2.1、查询数据表card_use具体优惠券数据并合并为1个数组
		//3、订单从高到低排序 循环计算优惠券
		//4、查询可用优惠券，返回对应的选择列表

		$user_id    = $params['user_id'];
		$selectCard = $params['select_card'];
		$cardList   = CouponHelper::bizCardAvailableData($user_id);

		$data = [
			'order_num'      => 0,//总订单
			'total_amount'   => 0,//总计金额
			'discount'       => 0,//优惠金额
			'amount_payable' => 0,//实付金额
			'card_num'       => 0,//优惠券张数
			'card_list'      => null,//订单列表	//前端安卓判断
			'card_available' => count($cardList),
		];


		$selectCardList = [];
		//2.1查询数据表card_use具体优惠券数据并合并为1个数组
		if ($selectCard) {
			$newSelectCard = [];
			//过滤传过来的数据 避免重复数据
			foreach ($selectCard as $item) {
				$newSelectCard[$item['parent_id']] = $item;
			}
			$selectCardList    = CouponHelper::bizCardListForSmart($newSelectCard, $user_id);
			$selectCardListArr = ArrayHelper::getColumn($selectCardList, 'price');
			array_multisort($selectCardListArr, SORT_DESC, $selectCardList);
		}

		$cardCount         = [];    //同类型的卡券使用数汇总
		$data['order_num'] = count($orderData);
		foreach ($orderData as $key => $order) {

			$data['total_amount']              += $order['order_amount'];
			$orderData[$key]['discount']       = 0;
			$orderData[$key]['card_parent_id'] = 0;
			$orderData[$key]['card_id']        = 0;

			$cardOne = current($selectCardList);    //使用第一组数据
			if ($cardOne) {
				$selectCardList = array_slice($selectCardList, 1);//移除使用的第一组数据

				//计算优惠
				$order_amount = $order['order_amount'];
				$card_money   = $cardOne['price'];
				$discount_res = bcsub($order_amount, $card_money, 2);

				$orderData[$key]['discount']       = $discount_res > 0 ? $card_money : $order_amount;
				$orderData[$key]['card_parent_id'] = $cardOne['parent_id'];
				$orderData[$key]['card_id']        = $cardOne['card_id'];
				$data['card_num']                  += 1;

				//计算使用了多少同类型的卡券
				$parent_id = $cardOne['parent_id'];
				isset($cardCount[$parent_id]) ? $cardCount[$parent_id] += 1 : $cardCount[$parent_id] = 1;
			}

			$data['discount'] += $orderData[$key]['discount'];
		}

		if ($cardList) {
			foreach ($cardList as $key => $item) {
				$cardList[$key]['selected'] = 0;
				if (isset($cardCount[$item['parent_id']])) {
					$cardList[$key]['selected'] = $cardCount[$item['parent_id']];
				}
			}
			$data['card_list'] = $cardList;
		}

		$amount_payable           = $data['total_amount'] - $data['discount'];
		$result                   = $data;
		$result['amount_payable'] = sprintf("%.2f", $amount_payable);
		$result['discount']       = sprintf("%.2f", $data['discount']);
		$result['total_amount']   = sprintf("%.2f", $data['total_amount']);
		$user_info                = UserHelper::getUserInfo($params['user_id'], 'money');
		$result['balance_pay']    = $user_info['money'] > $result['amount_payable'] ? 1 : 0;

		$order_key = self::BATCH_PAYMENT_KEY . $user_id;
		Yii::$app->cache->set($order_key, $orderData, 120);    //继续保存订单数据

		return $result;
	}

	/**
	 * 平台取消订单
	 * @param $params
	 * @return array|bool
	 */
	public static function platTmpOrderCancel($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$bizTmpOrder = BizTmpOrder::findOne(['tmp_no' => $params['tmp_no'], 'tmp_status' => [Ref::BIZ_TMP_STATUS_WAITE, Ref::BIZ_TMP_STATUS_PICKED, Ref::BIZ_TMP_STATUS_INPUT]]);
			if ($bizTmpOrder) {

				$updateData              = [
					'tmp_status'  => Ref::BIZ_TMP_STATUS_CALL_OFF,
					'cancel_time' => time(),
					'cancel_type' => Ref::ERRAND_CANCEL_DEAL_NOTIFY,
				];
				$bizTmpOrder->attributes = $updateData;
				$bizTmpOrder->save() ? $result = true : Yii::$app->debug->log_info('biz_save_robbing_error', $bizTmpOrder->getErrors());

				if ($result) {
					$result = [
						'tmp_no' => $bizTmpOrder->tmp_no
					];
					$transaction->commit();

					if ($bizTmpOrder->robbed == Ref::ORDER_ROBBED) {
						BizSendHelper::pushTmpToProviderNotice($bizTmpOrder->tmp_no, BizSendHelper::PUSH_TMP_TYPE_CANCEL_PROVIDER);    //推送给小帮
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
	 * 检查最旧一条订单是否支付
	 */
	public static function checkBizOrderStatus($user_id)
	{
		$result = false;
		$where  = [
			'user_id'      => $user_id,
			'cate_id'      => Ref::CATE_ID_FOR_BIZ_SEND,
			'order_status' => Ref::ORDER_STATUS_AWAITING_PAY
		];

		$order = Order::find()->innerErrand()->where($where)->select(OrderErrand::tableName() . ".finish_time")->asArray()->one();
		if ($order) {
			$finish_time = $order['finish_time'];
			$now         = YII_ENV_PROD ? time() - 60 * 60 * 4 : time() - 60 * 4;    //线上环境4小时，测试环境4分钟
			if ($finish_time < $now) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * 检测首页是否可以下单
	 * @return bool
	 */
	public static function getBizSendHome($user_id)
	{
		$result = false;
		$where  = [
			'user_id'      => $user_id,
			'cate_id'      => Ref::CATE_ID_FOR_BIZ_SEND,
			'order_status' => Ref::ORDER_STATUS_DEFAULT,
		];
		$order  = Order::find()->where($where)->one();

		$order ? $result = true : null;

		return $result;
	}

	/**
	 * 检测用户账户余额是否低于30元
	 * @param $user_id
	 * @return bool
	 */
	public static function checkBizUserBalance($user_id)
	{
		$result   = false;
		$userInfo = UserHelper::getUserInfo($user_id);
		$balance  = $userInfo['money'];
		if ($balance < 30) {
			$result = true;
		}

		return $result;
	}

	//取消订单
	public static function cancelOrder($user_id, $batch_no)
	{
		$condition = ['batch_no' => $batch_no, 'user_id' => $user_id, 'tmp_status' => Ref::BIZ_TMP_STATUS_WAITE, 'robbed' => Ref::ORDER_ROB_NEW];

		return BizTmpOrder::updateAll(['tmp_status' => Ref::BIZ_TMP_STATUS_CANCEL, 'update_time' => time()], $condition);
	}

	/**
	 * 获取配送区域
	 * @param $user_id
	 * @return array
	 */
	public static function getDistrict($user_id)
	{
		$result = [];

		//用户设置区域
		$bizUserDistrict = BizUserDistrict::find()->select('district')->where(['user_id' => $user_id])->asArray()->all();
		$user_district   = array_column($bizUserDistrict, 'district');

		//代理商设置区域
		$bizInfo = BizInfo::find()->select('area_id,city_id')->where(['user_id' => $user_id])->asArray()->one();
		$area_id = $bizInfo['area_id'] ? $bizInfo['area_id'] : 0;
		if ($bizInfo['city_id']) {
			$city_id = $bizInfo['city_id'];
		} else {
			$userInfo = UserHelper::getUserInfo($user_id, 'city_id');
			$city_id  = $userInfo['city_id'] ? $userInfo['city_id'] : 0;
		}
		$bizAgentDistrict = BizAgentDistrict::find()->select('district_list')->where(['city_id' => $city_id, 'area_id' => $area_id])->asArray()->one();
		if (!$bizAgentDistrict) {
			$bizAgentDistrict = BizAgentDistrict::find()->select('district_list')->where(['city_id' => $city_id, 'area_id' => 0])->asArray()->one();
		}
		$agent_district = $bizAgentDistrict['district_list'];
		if (strpos($agent_district, ',')) {
			$agent_district = explode(',', $agent_district);
		} elseif (strpos($agent_district, '，')) {
			$agent_district = explode('，', $agent_district);
		} elseif ($agent_district) {
			$agent_district = [$agent_district];
		} else {
			$agent_district = [];
		}

		//合并用户和代理商设置区域
		$merge_district = array_merge($agent_district, $user_district);

		//订单区域 (用户最近30天)
		$time           = time() - 60 * 60 * 24 * 30;
		$bizTmpOrder    = BizTmpOrder::find()->select('delivery_area')->where(['user_id' => $user_id])->andWhere(['>', 'create_time', $time])->asArray()->all();
		$bizTmpOrder    = array_column($bizTmpOrder, 'delivery_area');
		$order_district = [];
		foreach ($bizTmpOrder as $value) {
			if ($value) {
				$order_district = array_merge($order_district, explode(',', $value));
			}
		}
		if ($order_district) {
			//订单区域去重 排序 取键值
			$order_district = array_count_values($order_district);
			arsort($order_district);
			$order_district = array_keys($order_district);

			//区域过滤
			foreach ($order_district as $value) {
				if (in_array($value, $merge_district)) {
					$result[] = $value;
				}
			}
			foreach ($merge_district as $value) {
				if (!in_array($value, $result)) {
					$result[] = $value;
				}
			}
		} else {
			$result = $merge_district;
		}

		//保留25个区域
		$count = count($result);
		if ($count >= 25) {
			$result = array_slice($result, 0, 25);
		}

		//用户和代理商未设置区域
		if (!$result) {
			$bizInfo = BizInfo::find()->select('area_id')->where(['user_id' => $user_id])->asArray()->one();
			$region  = Region::find()->select('region_name')->where(['region_id' => $bizInfo['area_id']])->asArray()->one();
			if ($region) {
				$result = [$region['region_name']];
			}
		}

		//添加其他选项
		array_push($result, '其他');

		return $result;
	}

	/**
	 * 配送区域是否存在
	 * @param $user_id
	 * @param $district
	 * @return bool
	 */
	public static function checkDistrictExist($user_id, $district)
	{
		$bizDistrict = self::getDistrict($user_id);
		$result      = in_array($district, $bizDistrict);

		return $result;
	}

	/**
	 * 配送区域数量是否超限
	 * @param $user_id
	 * @return bool
	 */
	public static function checkDistrictNum($user_id)
	{
		$result      = true;
		$bizDistrict = self::getDistrict($user_id);
		$count       = count($bizDistrict);
		if ($count >= 26) {
			$result = false;
		}

		return $result;
	}

	/**
	 * 添加配送区域
	 * @param $user_id
	 * @param $district
	 * @return bool
	 */
	public static function addDistrict($user_id, $district)
	{

		$bizUserDistrict             = new BizUserDistrict();
		$params                      = [
			'user_id'     => $user_id,
			'district'    => $district,
			'create_time' => time(),
			'update_time' => null
		];
		$bizUserDistrict->attributes = $params;
		$result                      = $bizUserDistrict->save() ? true : false;

		return $result;
	}

	/**
	 * 删除用户配送区域
	 * @param $user_id
	 * @param $district
	 * @return bool
	 */
	public static function deleteDistrict($user_id, $district)
	{
		$result = BizUserDistrict::deleteAll(['user_id' => $user_id, 'district' => $district]) ? true : false;

		return $result;
	}

	/**
	 * 订单自动匹配数据
	 * @param $params
	 * @return array|bool
	 */
	public static function getAutoCouponPayment($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id']]);
		if ($model) {
			$order_amount = $model->order_amount;

			//自动匹配一张优惠券
			if ($params['card_id'] == '-1') {
				$now_time  = time();
				$card_data = (new Query())->from("bb_card_user as cu")
					->select("cu.id")
					->leftJoin("bb_card as c", "cu.c_id = c.id")
					->where(['cu.uid' => $params['user_id'], 'cu.status' => Ref::CARD_STATUS_NEW])// 'c.belong_type' => Ref::BELONG_TYPE_BIZ
					->andWhere([">", 'cu.end_time', $now_time])
					->andWhere(["<=", "cu.price", $order_amount])
					->orderBy("cu.price desc,cu.end_time")
					->one();
				if ($card_data) {
					$params['card_id'] = $card_data['id'];
				}
			}

			$orderHelper            = new OrderHelper();
			$result                 = $orderHelper->getOrderCalc($order_amount, $params);
			$user_info              = UserHelper::getUserInfo($params['user_id'], 'money');
			$result['balance_pay']  = $user_info['money'] > $result['amount_payable'] ? 1 : 0;
			$result['order_amount'] = $order_amount;

			$cardList                 = CouponHelper::bizCardAvailableData($params['user_id']);
			$result['card_available'] = count($cardList);    //可用优惠券数量
		}

		return $result;
	}


	/**
	 * 企业送订单（不包括发布中）
	 * @param $params
	 * @return array
	 */
	public static function getBizOrderList($params)
	{
		$result           = [];
		$arrive_data      = ['order_amount' => 0, 'order_count' => 0];
		$where            = [];
		$where['user_id'] = $params['user_id'];
		$where['cate_id'] = Ref::CATE_ID_FOR_BIZ_SEND;
		if (isset($params['status'])) {
			switch ($params['status']) {
				case 1://进行中
					$where['order_status'] = [0];
					break;
				case 2://待支付
					$where['order_status'] = Ref::ORDER_STATUS_AWAITING_PAY;
					$arrive_data           = Order::find()->select(['sum(order_amount) as order_amount', 'count(order_id) as order_count'])->where(['order_status' => 1, 'user_id' => $params['user_id'], 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND])->asArray()->one();
					if (empty($arrive_data['order_amount'])) {
						$arrive_data['order_amount'] = 0;
					}
					break;
				case 3://已经完成
					$where['order_status'] = [5, 6, 7];
					break;
				case 4://取消
					$where['order_status'] = [3, 4, 9];
					break;
				default:
					break;
			}
		}

		$current_page = !empty($params['page']) ? intval($params['page']) : 1;                 //分页属性从0开始的
		$pageSize     = !empty($params['page_size']) ? intval($params['page_size']) : 20;    //每页数量
		$subQuery     = Order::find()->select('order_id')->where($where);
		$count        = $subQuery->count();
		$page         = 0;
		if ($current_page > 0) {
			$page = $current_page - 1;
		}
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $pageSize;
		$result['pagination']
							  = [
			'page'       => $current_page,
			'pageSize'   => $pageSize,
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount
		];

		//进行中的订单正序，其他状态的订单倒叙
		if ($params['status'] == 1) {
			$subQuery->offset($pagination->offset)->orderBy(['order_status' => SORT_ASC, 'create_time' => SORT_ASC])->limit($pagination->limit);
		} else {
			$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		}

		$data = Order::find()->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->asArray()->all();

		$list = [];
		foreach ($data as $key => $value) {
			$order_status = self::getBizOrderStatus($value['order_status']);
			$orderErrand  = OrderErrand::findOne(['order_id' => $value['order_id']]);
			if (!$orderErrand) {
				continue;
			}
			$cate_data                   = CateListHelper::getCateListName($value['cate_id'], $orderErrand->errand_type);
			$list[$key]['order_no']      = $value['order_no'];
			$list[$key]['order_id']      = $value['order_id'];
			$list[$key]['start_address'] = $value['start_address'];
			$list[$key]['end_address']   = $value['end_address'];
			$list[$key]['order_time']    = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['order_amount']  = $value['order_amount'];
			$list[$key]['status']        = isset($order_status['status']) ? $order_status['status'] : 0;
			$list[$key]['status_text']   = isset($order_status['status_text']) ? $order_status['status_text'] : null;
			$list[$key]['cate_name']     = isset($cate_data['cate_name']) ? $cate_data['cate_name'] : null;
			$list[$key]['cate_id']       = isset($cate_data['cate_id']) ? $cate_data['cate_id'] : null;
			$list[$key]['content']       = $orderErrand->errand_content;
			$list[$key]['mobile']        = $orderErrand->mobile;
			$list[$key]['mobile_text']   = substr_replace($orderErrand->mobile, '****', 3, 4);
			$list[$key]['order_status']  = $value['order_status'];
		}

		$result['list']   = $list;
		$result['arrive'] = $arrive_data;

		return $result;
	}

	/**
	 * 企业送发布中的订单
	 * @param $user_id
	 * @return array
	 */
	public static function getBizPublishOrder($user_id)
	{
		$result     = [];
		$where      = ['user_id' => $user_id, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND, 'tmp_status' => [Ref::BIZ_TMP_STATUS_WAITE, Ref::BIZ_TMP_STATUS_PICKED]];
		$select     = ['batch_no', 'robbed', 'delivery_area', 'start_location', 'start_address'];
		$issue_data = BizTmpOrder::find()
			->select($select)
			->where($where)
			->andWhere(['>', 'create_time', time() - 86400 * 2])
			->groupBy("batch_no")
			->orderBy(['create_time' => SORT_DESC])
			->asArray()->all();
		if ($issue_data) {
			foreach ($issue_data as $key => $value) {
				$data         = BizTmpOrder::find()->select(['sum(tmp_qty) as order_count', 'count(tmp_id) as provider_count', 'create_time'])->where(['batch_no' => $value['batch_no']])->asArray()->one();
				$rob_count    = BizTmpOrder::find()->where(['batch_no' => $value['batch_no'], 'tmp_status' => [1, 2, 3], 'robbed' => Ref::ORDER_ROBBED])->count();
				$cancel_count = BizTmpOrder::find()->where(['batch_no' => $value['batch_no'], 'tmp_status' => [4, 5, 6]])->count();

				$result[$key]['create_time']    = date('Y-m-d H:i:s', $data['create_time']);
				$result[$key]['order_count']    = $data['order_count'];
				$result[$key]['batch_no']       = $value['batch_no'];
				$result[$key]['need_provider']  = $data['provider_count'];
				$result[$key]['rob_count']      = $rob_count;
				$result[$key]['cancel_count']   = $cancel_count;
				$result[$key]['robbed']         = $value['robbed'];
				$result[$key]['start_address']  = $value['start_address'];
				$result[$key]['start_location'] = $value['start_location'];
				$result[$key]['delivery_area']  = $value['delivery_area'] ? $value['delivery_area'] : "无";
			}
		}

		return $result;
	}

	/**
	 * 企业送发布中的订单
	 * @param $user_id
	 * @return array
	 */
	public static function getBizPublishOrderV11($user_id)
	{
		$result     = [];
		$where      = ['user_id' => $user_id, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND, 'tmp_status' => [Ref::BIZ_TMP_STATUS_WAITE, Ref::BIZ_TMP_STATUS_PICKED]];
		$select     = ['batch_no', 'robbed', 'delivery_area', 'start_location', 'start_address', 'tmp_no', 'create_time'];
		$issue_data = BizTmpOrder::find()
			->select($select)
			->where($where)
			->andWhere(['>', 'create_time', time() - 86400 * 2])
			->orderBy(['create_time' => SORT_DESC])
			->asArray()->all();
		if ($issue_data) {
			foreach ($issue_data as $key => $value) {
				$result[$key]                  = $value;
				$result[$key]['order_time']    = date('Y-m-d H:i:s', $value['create_time']);
				$result[$key]['delivery_area'] = $value['delivery_area'] ? $value['delivery_area'] : "其他";
				unset($result[$key]['create_time']);

			}
		}

		return $result;
	}

	/**
	 * 自动扣款 扣款
	 */
	public static function autoFreezeBalance()
	{
		//开发思路
		//1、查找12小时前的订单
		//2、循环查找订单数据
		//3、循环扣除余额
		//3.1、余额不足发送短信
		$startTime = YII_ENV_PROD ? time() - 60 * 60 * 16 : time() - 60 * 16;
		$endTime   = YII_ENV_PROD ? time() - 60 * 60 * 4 : time() - 60 * 4;

		$checker = Order::find()->innerErrand()->select(['user_id', 'user_mobile'])->where(['order_status' => Ref::ORDER_STATUS_AWAITING_PAY, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND])
			->andWhere(['>', OrderErrand::tableName() . '.finish_time', $startTime])
			->andWhere(['<', OrderErrand::tableName() . '.finish_time', $endTime])->groupBy(['user_id'])->asArray()->all();

		foreach ($checker as $rows) {
			$rows['start_time'] = $startTime;
			$rows['end_time']   = $endTime;

			QueueHelper::autoBizFreezeBalance($rows);
		}
	}

	/**
	 * 自动扣款的补充
	 * @param $data
	 */
	public static function autoFreezeBalanceExt($data)
	{
		$user_id     = $data['user_id'];
		$user_mobile = $data['user_mobile'];
		$startTime   = $data['start_time'];
		$endTime     = $data['end_time'];
		$orderData   = Order::find()->innerErrand()->select([Order::tableName() . '.order_id', 'user_id', 'order_no', 'order_amount'])
			->where(['order_status' => Ref::ORDER_STATUS_AWAITING_PAY, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND, 'user_id' => $user_id])
			->andWhere(['>', OrderErrand::tableName() . '.finish_time', $startTime])
			->andWhere(['<', OrderErrand::tableName() . '.finish_time', $endTime])->orderBy(['order_amount' => SORT_ASC])->asArray()->all();

		if ($orderData) {
			//获取余额
			$balance          = WalletHelper::getBalance($user_id);
			$balance          = $balance['balance'];  //用户余额
			$orderTotal       = count($orderData);    //总订单数
			$payOrderTotal    = 0;                    //支付订单数
			$orderAmountTotal = 0;                    //订单总金额
			$payAmount        = 0;                    //支付的金额

			foreach ($orderData as $order) {

				$order_amount     = $order['order_amount'];
				$orderAmountTotal = bcadd($orderAmountTotal, $order_amount, 2);
				$checkBalance     = bcsub($balance, $order_amount, 2);

				if ($checkBalance > 0) {
					$params['order_no']   = $order['order_no'];
					$params['payment_id'] = Ref::PAYMENT_TYPE_BALANCE;
					$orderHelper          = new OrderHelper();
					$orderRes             = $orderHelper->generatePrePaymentSingle($params);
					if ($orderRes) {
						$trade_no = date("YmdHis");
						BizSendHelper::orderPaymentSuccess($orderRes['transaction_no'], $trade_no, Ref::PAYMENT_TYPE_BALANCE, $orderRes['fee'], "余额支付", null, true);

						//当前记录支付成功后更新
						$balance = $checkBalance;    //成功支付 余额更新
						$payOrderTotal++;
						$payAmount = bcadd($payAmount, $order_amount, 2);
					}
				}
			}

			if ($orderTotal > $payOrderTotal && $payOrderTotal > 0) {

				$params = [
					'buckle_num'    => $payOrderTotal,        //订单数
					'buckle_amount' => $payAmount,    //扣款金额
					'owe_num'       => $orderTotal - $payOrderTotal,            //欠款订单数
					'owe_amount'    => bcsub($orderAmountTotal, $payAmount, 2),    //欠款金额
				];

				SmsHelper::sendBizCutPaymentPart($user_mobile, $params);    //部分扣除;

			} else if ($orderTotal == $payOrderTotal) {

				$params = [
					'buckle_num'    => $payOrderTotal,        //订单数
					'buckle_amount' => $payAmount    //扣款金额
				];
				SmsHelper::sendBizCutPaymentAll($user_mobile, $params);    //全部扣除;

			} else {

				SmsHelper::sendBizNotPay($user_mobile);    //余额不足
			}
			echo $user_mobile;
		}//if ($orderData)
	}

	/**
	 * 返回企业送订单状态的信息
	 * @param $status
	 * @return array
	 */
	public static function getBizOrderStatus($status)
	{
		$result = [];
		if ($status == 0) {
			$result['status']      = 1;
			$result['status_text'] = '配送中';
		} elseif ($status == 1) {
			$result['status']      = 2;
			$result['status_text'] = '配送到达';
		} elseif ($status == 5) {
			$result['status']      = 3;
			$result['status_text'] = '已扣款';
		} elseif ($status == 6) {
			$result['status']      = 3;
			$result['status_text'] = '未评价';
		} elseif ($status == 7) {
			$result['status']      = 4;
			$result['status_text'] = '已评价';
		} elseif ($status == 3 || $status == 4 || $status == 9) {
			$result['status']      = 5;
			$result['status_text'] = '取消';
		}

		return $result;
	}

	/**
	 * 企业送临时订单 改派
	 */
	public static function saveTmpReassign($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$bizTmpOrder = BizTmpOrder::findOne(['tmp_no' => $params['tmp_no'], 'robbed' => Ref::ORDER_ROBBED, 'tmp_status' => Ref::BIZ_TMP_STATUS_PICKED]);    //TODO 处于进行中 才能抢单 如果取消的情况。
			if ($bizTmpOrder) {

				$oldProviderId           = $bizTmpOrder->provider_id;
				$updateData              = [
					'robbed_time'       => time(),
					'provider_location' => $params['provider_location'],
					'provider_address'  => $params['provider_address'],
					'provider_mobile'   => $params['provider_mobile'],
					'provider_id'       => $params['provider_id'],
				];
				$bizTmpOrder->attributes = $updateData;
				$bizTmpOrder->save() ? $result = true : Yii::$app->debug->log_info('biz_save_robbing_error', $bizTmpOrder->getErrors());

				if ($result) {
					$result = [
						'tmp_no' => $bizTmpOrder->tmp_no
					];
					$transaction->commit();

					BizSendHelper::pushTmpToProviderNotice($bizTmpOrder->tmp_no, BizSendHelper::PUSH_TMP_TYPE_REASSIGN_PROVIDER, $oldProviderId);    //推送给小帮
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	public static function revokePayment($order_no)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $order_no]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$updateData = [
					'order_status' => Ref::ORDER_STATUS_AWAITING_PAY,
					'update_time'  => time(),
				];

				$order->attributes = $updateData;

				$order->save() ? $result = true : Yii::$app->debug->log_info("order_user_cancel_flow", $order->getErrors());

				$result &= self::saveLogContent($order->order_id, 'revoke_payment', $updateData, "平台撤销扣款");

				//创建交易退款流水记录
				$tradeRes = false;
				if ($order->payment_status == Ref::PAY_STATUS_COMPLETE) {
					$tradeRes = TransactionHelper::createOrderRefund($order->order_id, $order->payment_id);
					$result   &= $tradeRes;
				}

				if ($result) {

					if ($order->order_status == Ref::ORDER_STATUS_AWAITING_PAY) {
						$params = [
							'user_id'           => $order->user_id,
							'provider_id'       => $order->provider_id,
							'order_no'          => $order->order_no,
							'request_cancel_id' => $order->request_cancel_id,
							'cancel_type'       => $errand->cancel_type,
							'robbed'            => $order->robbed,
							"trade"             => $tradeRes,
							'payment_id'        => $order->payment_id,
							'order_from'        => $order->order_from,
							"errand_type"       => $errand->errand_type,
						];
						QueueHelper::preRefund($params);
						//TODO 这里要不要发短信通知撤销扣款
					}
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