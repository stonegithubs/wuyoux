<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace common\helpers\orders;

use common\components\Ref;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use yii\base\Exception;
use Yii;

//快送类
abstract class ErrandHelper extends OrderHelper
{

	const CANCEL_USER_APPLY_MSG        = '用户申请取消订单!';
	const CANCEL_USER_AGREE_MSG        = '用户同意取消订单!';
	const CANCEL_USER_DISAGREE_MSG     = '用户不同意取消订单!';
	const CANCEL_PROVIDER_APPLY_MSG    = '小帮申请取消订单';
	const CANCEL_PROVIDER_AGREE_MSG    = '小帮同意取消订单!';
	const CANCEL_PROVIDER_DISAGREE_MSG = '小帮不同意取消订单!';
	const CANCEL_DEAL_NOTIFY_MSG       = '平台客服介入处理完毕';
	const CANCEL_ORDER_NOTIFY_MSG      = '平台客服取消该订单！';
	const ADD_CUSTOM_FEE_MSG           = '用户添加了{fee}元小费';
	const CANCEL_AUTO_CANCEL           = "您的订单暂无人接单\n 系统已自动取消";

	const PUSH_USER_TYPE_TASK_PROGRESS       = 'task_progress';
	const PUSH_USER_TYPE_AUTO_CANCEL         = 'auto_cancel';
	const PUSH_USER_TYPE_CANCEL_PROGRESS     = 'cancel_progress';
	const PUSH_PROVIDER_TYPE_CONFIRM         = 'confirm';            //订单确认
	const PUSH_PROVIDER_TYPE_CANCEL_PROGRESS = 'cancel_progress';    //取消订单
	const PUSH_PROVIDER_SMALL_FEE            = 'small_fee';    //小费
	const PUSH_USER_TYPE_EXPENSE             = 'user_expense';            //商品费用
	const PUSH_PROVIDER_TYPE_EXPENSE         = 'provider_expenses';        //商品费用


	const PUSH_PROVIDER_TYPE_ORDER_ASSIGN       = 'provider_order_assign';    //指派给小帮的订单通知
	const PUSH_PROVIDER_TYPE_ORDER_REASSIGN     = 'provider_order_reassign';    //改派给小帮的订单通知
	const PUSH_PROVIDER_TYPE_ORDER_REASSIGN_OLD = 'provider_order_reassign_old';    //改派给小帮的订单通知
	const PUSH_USER_TYPE_CHANGE_ORDER           = 'user_order_change';        //改派给别的小帮的通知

	/**
	 * 保存被抢数据
	 *
	 * @param $params
	 *
	 * @return bool
	 */
	public static function saveRobbing($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			//查找没有被抢的进行中的订单
			$order = Order::findOne(['order_no' => $params['order_no'], 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DOING]);    //TODO 处于进行中 才能抢单 如果取消的情况。
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]); //查找该订单的小费

				$updateData = [
					'robbed'            => Ref::ORDER_ROBBED, //已抢
					'robbed_time'       => time(),
					'provider_location' => $params['provider_location'],
					'provider_address'  => $params['provider_address'],
					'provider_mobile'   => $params['provider_mobile'],
					'provider_id'       => $params['provider_id'],
					'errand_status'     => Ref::ERRAND_STATUS_PICKED, //小帮已接单
				];
				//var_dump($updateData);exit;
				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::$app->debug->log_info('save_robbing_error', $order->getErrors());

				//记录业务日志
				$result                    &= self::saveLogContent($order->order_id, 'robbing', $updateData, '抢单更新');
				$errand->errand_status     = Ref::ERRAND_STATUS_PICKED; //小帮已接单
				$errand->starting_distance = $params['starting_distance'];
				$errandRes                 = $errand->save();

				if (!$errandRes) {    //保存失败
					$result &= false;
					Yii::$app->debug->log_info('save_robbing_res', $order->getErrors());
				}

				if ($result) {
					$result = [
						'order_no'      => $order->order_no,
						'errand_type'   => $errand->errand_type,
						'errand_status' => Ref::ERRAND_STATUS_PICKED //小帮已接单
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

	public static function getRangePriceDataForAMap($start_location, $end_location, $userInfo, $cate_id_type)
	{
		$route         = AMapHelper::bicycling(AMapHelper::coordToStr($start_location), AMapHelper::coordToStr($end_location)); //订单起点坐标，商家当前坐标
		$distance      = 0;
		$distance_text = '0米';

		if (is_array($route)) {
			$distance      = $route['distance'];
			$distance_text = UtilsHelper::distance($distance);
		}

		$user_city_id = isset($userInfo['city_id']) ? $userInfo['city_id'] : null;
		$city_price   = RegionHelper::getCityPrice($start_location, $user_city_id, $cate_id_type);

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


	/**
	 * 类型转label
	 *
	 * @param $id
	 *
	 * @return mixed|string
	 */
	public static function getErrandType($id)
	{
		$arr = [
			Ref::ERRAND_TYPE_BUY  => '帮我买',
			Ref::ERRAND_TYPE_SEND => '帮我送',
			Ref::ERRAND_TYPE_DO   => '帮我办',
			Ref::ERRAND_TYPE_BIZ  => '企业送',
		];

		$result = isset($arr[$id]) ? $arr[$id] : '';

		return $result;
	}

	//TODO 暂时统一为0.2
	public static function getOnlineDiscount($online_money)
	{
		return sprintf("%.2f", $online_money * 0.2);
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
			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {    //暂时无人取消
				$errand     = OrderErrand::findOne(['order_id' => $order->order_id]);
				$updateData = [];
				$tradeRes   = [];
				//1、未接单 取消订单 退款 取消推送
				if ($order->robbed == Ref::ORDER_ROB_NEW) {
					$updateData = [
						'order_status' => Ref::ORDER_STATUS_CANCEL,
						'cancel_time'  => time(),
					];

					$order->attributes = $updateData;
					$order->save() ? $result = true : Yii::error("user cancel and refund money:" . json_encode($order->getErrors()));

					//创建交易退款流水记录
					$tradeRes = TransactionHelper::createOrderRefund($order->order_id, $order->payment_id, $params);
					$result   &= $tradeRes;
				}

				if ($order->robbed == Ref::ORDER_ROBBED) {
					$updateData = [
						'request_cancel_id'   => $order->user_id,
						'request_cancel_time' => time(),
						'cancel_type'         => Ref::ERRAND_CANCEL_USER_APPLY
					];

					$order->attributes  = $updateData;
					$errand->attributes = $updateData;
					$order->save() ? $result = true : Yii::error("order user cancel:" . json_encode($order->getErrors()));
					$errand->save() ? $result &= true : Yii::error("errand user cancel:" . json_encode($errand->getErrors()));
				}

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
						'trade'             => $tradeRes,
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
			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
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
				Ref::ORDER_STATUS_DOING,
				Ref::ORDER_STATUS_DECLINE,
				Ref::ORDER_STATUS_CALL_OFF
			];
			$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => $status]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$push_cancel_type          = '';
				$updateData['update_time'] = time();
				$label                     = '小帮清空提示信息';
				if ($params['agreed'] == 'yes') { //同意 TODO 退款操作

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

				//T创建交易退款流水记录
				$tradeRes = false;
				if ($params['agreed'] == 'yes') {
					$params['user_id'] = $order->user_id;
					$tradeRes          = TransactionHelper::createOrderRefund($order->order_id, $order->payment_id, $params);
					$result            &= $tradeRes;
				}

				if ($result) {

					$result = [
						'order_no'          => $order->order_no,
						'cancel_type'       => $push_cancel_type,
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						"trade"             => $tradeRes,
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
				Ref::ORDER_STATUS_DOING,
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

				//创建交易退款流水记录
				$tradeRes = false;
				if ($params['agreed'] == 'yes') {
					$tradeRes = TransactionHelper::createOrderRefund($order->order_id, $order->payment_id, $params);
					$result   &= $tradeRes;
				}

				if ($result) {
					$result = [
						'order_no'          => $order->order_no,
						'robbed'            => $order->robbed,
						'cancel_type'       => $push_cancel_type,
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						"trade"             => $tradeRes,
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
	 * 平台取消订单
	 *
	 * @param $order_no
	 *
	 * @return bool
	 */
	public static function platformCancel($order_no)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $order_no]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$updateData = [
					'cancel_type'  => Ref::ERRAND_CANCEL_DEAL_NOTIFY,
					'order_status' => Ref::ORDER_STATUS_CALL_OFF,
					'update_time'  => time(),
				];

				$order->attributes  = $updateData;
				$errand->attributes = $updateData;
				$order->save() ? $result = true : Yii::$app->debug->log_info("order_user_cancel_flow", $order->getErrors());
				$errand->save() ? $result &= true : Yii::$app->debug->log_info("error_user_cancel_flow", $order->getErrors());

				$result &= self::saveLogContent($order->order_id, 'platform_cancel', $updateData, "平台取消订单");

				//创建交易退款流水记录
				$tradeRes = false;
				if ($order->payment_status == Ref::PAY_STATUS_COMPLETE) {
					$tradeRes = TransactionHelper::createOrderRefund($order->order_id, $order->payment_id);
					$result   &= $tradeRes;
				}

				if ($result) {

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY) {

						$msg = $order->order_status == Ref::ORDER_STATUS_CALL_OFF ? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;

						$params = [
							'deal_msg'          => $msg,
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

						//推送分类
						QueueHelper::errandPlatformCancelNotice($params, Ref::PUSH_ROLE_USER);
						if ($order->robbed == Ref::ORDER_ROBBED) {
							QueueHelper::errandPlatformCancelNotice($params, Ref::PUSH_ROLE_PROVIDER);
						}

						if ($order->order_status == Ref::ORDER_STATUS_CALL_OFF) {

							QueueHelper::preRefund($params);
						}
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

	//订单自动取消通知
	public static function pushAutoCancelNotice($order_no)
	{
		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			$url    = PushHelper::ERRAND_DO_AUTO_CANCEL;

			if ($errand->errand_type == Ref::ERRAND_TYPE_BUY) {
				$url = PushHelper::ERRAND_BUY_AUTO_CANCEL;

			} else if ($errand->errand_type == Ref::ERRAND_TYPE_SEND) {
				$url = PushHelper::ERRAND_SEND_AUTO_CANCEL;

			}
			//推送消息
			$push_info = [
				"glx"            => Ref::GETUI_TYPE_CANCEL_NOTICE,//个推类型
				"to"             => Ref::GETUI_TO_ERRAND_ORDER,
				"url"            => $url,
				"cancel_type"    => Ref::ERRAND_CANCEL_AUTO,            //自动取消
				"user_id"        => $order->user_id,
				"provider_id"    => $order->provider_id,
				"order_no"       => $order->order_no,
				"inform_content" => "您的订单暂无人接单\n 系统已自动取消",    //App针对\\处理
				'log_time'       => date("Y-m-d H:i:s"),
				'push_role'      => Ref::PUSH_ROLE_USER,
				'push_user_id'   => $order->user_id,
			];

			QueueHelper::toOneTransmissionForUser($order->user_id, $push_info, 'pushAutoCancelNotice');
		}
	}

	/**
	 * 平台取消订单
	 *
	 * @param        $params
	 * @param string $role
	 */
	public static function pushPlatformCancelMsg($params, $role = 'user')
	{

		$inform      = [
			Ref::ERRAND_CANCEL_DEAL_NOTIFY => isset($params['deal_msg']) ? $params['deal_msg'] : self::CANCEL_DEAL_NOTIFY_MSG,
		];
		$cancel_type = $params['cancel_type'];
		if (isset($inform[$cancel_type])) {

			$url          = '';
			$current_page = 'task';

			if ($params['errand_type'] == Ref::ERRAND_TYPE_DO)
				$url = PushHelper::ERRAND_DO_PLATFORM_CANCEL;

			if ($params['errand_type'] == Ref::ERRAND_TYPE_SEND)
				$url = PushHelper::ERRAND_SEND_PLATFORM_CANCEL;

			if ($params['errand_type'] == Ref::ERRAND_TYPE_BUY)
				$url = PushHelper::ERRAND_BUY_PLATFORM_CANCEL;

			if ($params['errand_type'] == Ref::ERRAND_TYPE_BIZ)
				$url = PushHelper::BIZ_SEND_PLATFORM_CANCEL;


			if ($cancel_type == Ref::ERRAND_CANCEL_USER_AGREE || $cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE)
				$current_page = 'cancel';

			//推送消息
			$push_info = [
				"glx"               => Ref::GETUI_TYPE_CANCEL_NOTICE,//个推类型
				"to"                => Ref::GETUI_TO_ERRAND_ORDER,
				"url"               => $url,
				'cancel_type'       => $cancel_type,
				"user_id"           => $params['user_id'],
				"provider_id"       => $params['provider_id'],
				"order_no"          => $params['order_no'],
				"request_cancel_id" => isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null,
				"inform_content"    => isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!',
				"current_page"      => $current_page,
				"push_role"         => $role,
				"push_user_id"      => $params['user_id']
			];

			if ($role == Ref::PUSH_ROLE_PROVIDER) {
				QueueHelper::toOneTransmissionForProvider($params['provider_id'], $push_info, 'pushPlatformCancelMsg_provider');
			}

			if ($role == Ref::PUSH_ROLE_USER) {
				QueueHelper::toOneTransmissionForUser($params['user_id'], $push_info, 'pushPlatformCancelMsg_user');
			}

		}
	}

	/**
	 * 小帮工作流程
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function workerProgress($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

				$updateData = [];
				if ($params['errand_status'] == Ref::ERRAND_STATUS_CONTACT) {
					$updateData = [
						'errand_status' => Ref::ERRAND_STATUS_CONTACT,
					];
				}

				if ($params['errand_status'] == Ref::ERRAND_STATUS_DOING) {
					$updateData = [
						'errand_status'  => Ref::ERRAND_STATUS_DOING,
						'begin_location' => $params['current_location'],
						'begin_address'  => $params['current_address'],
						'begin_time'     => time(),
					];
				}

				if ($params['errand_status'] == Ref::ERRAND_STATUS_FINISH) {
					$updateData = [
						'errand_status'   => Ref::ERRAND_STATUS_FINISH,
						'finish_location' => $params['current_location'],
						'finish_address'  => $params['current_address'],
						'actual_time'     => time(),
						'finish_time'     => time(),
					];
				}

				$errand->attributes = $updateData;
				$errand->save() ? $result = true : Yii::error("save worker progress errand:" . json_encode($order->getErrors()));
				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'worker_progress_errand', $updateData, '小帮工作流程');

				if ($result) {
					$result = [
						'order_no'      => $order->order_no,
						'errand_type'   => $errand->errand_type,
						'errand_status' => $params['errand_status'],
						'user_id'       => $order->user_id,
					];

					$transaction->commit();

					if ($params['errand_status'] == Ref::ERRAND_STATUS_DOING) {
						QueueHelper::receiverSmsNotice($order->order_no);
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
	 * 帮我送，帮我办添加小费
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function addCustomFee($params)
	{

		$result = false;
		$amount = doubleval($params['fee']);    //添加的费用
		if ($amount <= 0) {
			return $result;
		}

		$transaction = Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $params['order_no'], 'order_status' => Ref::ORDER_STATUS_DOING, 'user_id' => $params['user_id']]);
			if ($order) {
				$payment_id = $order->payment_id;
				$logParams  = [
					'ids_ref' => $order->order_id,
					'type'    => Ref::FEE_TYPE_PROD,
					'amount'  => $amount,
					'status'  => Ref::PAY_STATUS_WAIT
				];

				$feeData = self::addFee($logParams);    //记录小费
				$feeData ? $result = true : Yii::error("add custom fee fail");

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'add_fee', $logParams, '小帮添加小费');

				$fee_id = isset($feeData['fee_id']) ? $feeData['fee_id'] : 0;
				//流水表
				$tradeParams['user_id']    = $order->user_id;
				$tradeParams['type']       = Ref::TRANSACTION_TYPE_TIPS;
				$tradeParams['status']     = Ref::PAY_STATUS_WAIT;
				$tradeParams['payment_id'] = $payment_id;
				$tranRes                   = TransactionHelper::createTrade($fee_id, $amount, $tradeParams);
				$result                    = $tranRes;

				// 如果是余额支付的  冻结用户资金 并判断用户余额 是否够支付
				if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {
					$result &= WalletHelper::checkUserMoney($params['user_id'], $amount);
				}

				if ($result) {
					$result = [
						'payment_id'     => $order->payment_id,
						'fee_id'         => $fee_id,
						'transaction_no' => $tranRes['transaction_no'],
						'fee'            => $tranRes['fee'],
						'transaction_id' => $tranRes['id'],
						'order_id'       => $order->order_id,
						'order_amount'   => doubleval($order->order_amount) + $amount
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
	 * 保存改派信息
	 *
	 * @param $params
	 *
	 * @return bool
	 */
	public static function saveReassign($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'robbed' => Ref::ORDER_ROBBED, 'order_status' => [Ref::ORDER_STATUS_DEFAULT, Ref::ORDER_STATUS_DOING]]);    //TODO 处于进行中 才能抢单 如果取消的情况。
			if ($order) {

				$oldProviderId = $order->provider_id;
				$errand        = OrderErrand::findOne(['order_id' => $order->order_id]); //查找该订单的小费

				$updateData        = [
					'robbed'            => Ref::ORDER_ROBBED, //已抢
					'robbed_time'       => time(),
					'provider_location' => $params['provider_location'],
					'provider_address'  => $params['provider_address'],
					'provider_mobile'   => $params['provider_mobile'],
					'provider_id'       => $params['provider_id'],
				];
				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::$app->debug->log_info('save_robbing_error', $order->getErrors());

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'reassign', $updateData, '改派更新');

				if ($result) {
					$result = [
						'order_no'      => $order->order_no,
						'errand_type'   => $errand->errand_type,
						'errand_status' => Ref::ERRAND_STATUS_PICKED //小帮已接单
					];
					$transaction->commit();

					ErrandHelper::pushAssignToProvider($order->order_no, ErrandHelper::PUSH_PROVIDER_TYPE_ORDER_REASSIGN_OLD, $oldProviderId);
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}


	//小帮快送 指派/改派 推送给小帮端
	public static function pushAssignToProvider($order_no, $type, $oldProviderId = null)
	{
		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$pushData = [];

			//指派订单
			if ($type == self::PUSH_PROVIDER_TYPE_ORDER_ASSIGN) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_PROVIDER;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = self::pushToProviderNoticeAssignOrderUrl($order->cate_id);
				$pushData['order_amount']   = $order->order_amount;
				$pushData['inform_content'] = "您好，平台为您分发了一份订单";
			}

			//改派订单
			if ($type == self::PUSH_PROVIDER_TYPE_ORDER_REASSIGN) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_PROVIDER;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = self::pushToProviderNoticeAssignOrderUrl($order->cate_id);
				$pushData['order_amount']   = $order->order_amount;
				$pushData['inform_content'] = "您好，平台改派了一份订单给您";
			}

			//改派订单-原订单
			if ($type == self::PUSH_PROVIDER_TYPE_ORDER_REASSIGN_OLD) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_PROVIDER;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_REASSIGN_BEFORE;
				$pushData['order_amount']   = $order->order_amount;
				$pushData['inform_content'] = "您好，平台把您当前订单派送给别的小帮";
			}

			$provider_id = $oldProviderId ? $oldProviderId : $order->provider_id;

			$pushData['order_no']     = $order->order_no;
			$pushData['cate_id']      = $order->cate_id;
			$pushData['provider_id']  = $provider_id;
			$pushData['user_id']      = $order->user_id;
			$pushData['push_user_id'] = $provider_id;
			$pushData['push_role']    = Ref::PUSH_ROLE_PROVIDER;
			$pushData['log_time']     = date("Y-m-d H:i:s");

			QueueHelper::toOneTransmissionForProvider($provider_id, $pushData, 'errand_do_to_provider');
		}
	}

	/**
	 * 获取订单指派给商家推送-URL
	 * @return bool|string
	 */
	public static function pushToProviderNoticeAssignOrderUrl($cate_id)
	{
		$result = false;
		if ($cate_id == Ref::CATE_ID_FOR_BIZ_SEND) {
			$result = PushHelper::BIZ_SEND_ASSIGN_PROVIDER_NOTICE;
		}
		if ($cate_id == Ref::CATE_ID_FOR_ERRAND_BUY) {
			$result = PushHelper::ERRAND_BUY_ASSIGN_PROVIDER_NOTICE;
		}
		if ($cate_id == Ref::CATE_ID_FOR_ERRAND_SEND) {
			$result = PushHelper::ERRAND_SEND_ASSIGN_PROVIDER_NOTICE;
		}
		if ($cate_id == Ref::CATE_ID_FOR_ERRAND_DO) {
			$result = PushHelper::ERRAND_DO_ASSIGN_PROVIDER_NOTICE;
		}

		return $result;
	}
}

















