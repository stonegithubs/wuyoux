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
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\OrderTrip;
use common\models\util\HistoryAddress;
use yii\data\Pagination;
use common\models\orders\Order;
use Yii;
use yii\base\Exception;

abstract class TripHelper extends OrderHelper
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

	const PUSH_USER_TYPE_SEARCH_WORKER   = 'search_worker';//检索小帮
	const PUSH_USER_TYPE_TASK_PROGRESS   = 'task_progress';
	const PUSH_USER_TYPE_AUTO_CANCEL     = 'auto_cancel';
	const PUSH_USER_TYPE_CANCEL_PROGRESS = 'cancel_progress';


	const PUSH_PROVIDER_TYPE_PAY             = 'confirm';            //订单确认
	const PUSH_PROVIDER_TYPE_CANCEL_PROGRESS = 'cancel_progress';    //取消订单
	const PUSH_PROVIDER_SMALL_FEE            = 'small_fee';    //小费
	const PUSH_PROVIDER_TYPE_PRESS           = 'user_press';        //用户催单
	const PUSH_PROVIDER_TYPE_ASSIGN          = 'assign';        //指派订单

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
			$order = Order::findOne(['order_no' => $params['order_no'], 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {
				$trip = OrderTrip::findOne(['order_id' => $order->order_id]);

				$updateData        = [
					'robbed'            => Ref::ORDER_ROBBED, //已抢
					'robbed_time'       => time(),
					'provider_location' => $params['provider_location'],
					'provider_address'  => $params['provider_address'],
					'provider_mobile'   => $params['provider_mobile'],
					'provider_id'       => $params['provider_id'],

					//trip
					'trip_status'       => Ref::TRIP_STATUS_PICKED,
					'starting_distance' => $params['starting_distance'],
					'license_plate'     => $params['license_plate']
				];
				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::$app->debug->log_info('save_robbing_error', $order->getErrors());

				//记录业务日志
				$result           &= self::saveLogContent($order->order_id, 'robbing', $updateData, '抢单更新');
				$trip->attributes = $updateData;
				if (!$trip->save()) {    //保存失败
					$result &= false;
					Yii::$app->debug->log_info('save_robbing_res', $order->getErrors());
				}

				if ($result) {
					$result = [
						'order_id'    => $order->order_id,
						'trip_type'   => $trip->trip_type,
						'trip_status' => Ref::TRIP_STATUS_PICKED
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
	 * 出行计费规则
	 * @param $start_location
	 * @param $end_location
	 * @param $userInfo
	 * @param $cate_id_type
	 * @return array
	 */
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
			'price'         => sprintf("%.2f", $price),
			'type'          => $city_price['type'],
			'service_fee'   => $city_price['service_fee'],
		];
	}


	/**
	 * 平台取消订单
	 *
	 * @param        $params
	 * @param string $role
	 */
	public static function pushPlatformCancelMsg($params, $role = 'user')
	{

		$inform = [
			Ref::ERRAND_CANCEL_DEAL_NOTIFY => isset($params['deal_msg']) ? $params['deal_msg'] : self::CANCEL_DEAL_NOTIFY_MSG,
		];

		$clientData  = [];
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
			];


			$push_info['push_user_id'] = $clientData['user_id'];

			if ($role == Ref::PUSH_ROLE_PROVIDER) {
				QueueHelper::toOneTransmissionForProvider($params['provider_id'], $push_info, 'pushPlatformCancelMsg_provider');
			}

			if ($role == Ref::PUSH_ROLE_USER) {
				QueueHelper::toOneTransmissionForUser($params['user_id'], $push_info, 'pushPlatformCancelMsg_user');
			}

		}
	}

	//自动取消订单
	public static function autoCancelTripOrder($order_id)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_id' => $order_id, 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
			if ($order) {

				$updateData        = [
					'order_status' => Ref::ORDER_STATUS_CALL_OFF,
					'cancel_time'  => time()
				];
				$order->attributes = $updateData;

				$order->save() ? $result = true : Yii::error("auto cancel order:" . json_encode($order->getErrors()));

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'auto_cancel_order', $updateData, '订单超时自动取消');

				$tradeRes = null;

				if ($result) {
					$transaction->commit();

					//推送消息
					$push_info = [
						"glx"            => Ref::GETUI_TYPE_CANCEL_NOTICE,//个推类型
						"to"             => Ref::GETUI_TO_TRIP_ORDER,
						"url"            => PushHelper::TRIP_BIKE_AUTO_CANCEL,
						"cancel_type"    => Ref::ERRAND_CANCEL_AUTO,            //自动取消
						"user_id"        => $order->user_id,
						"provider_id"    => $order->provider_id,
						"order_no"       => $order->order_no,
						"inform_content" => "订单长时间没有小帮接单,系统已自\n动取消了订单,建议更改地址再发单",// "您的订单暂无人接单\n 系统已自动取消",    //App针对\\处理
						'log_time'       => date("Y-m-d H:i:s"),
						'push_role'      => Ref::PUSH_ROLE_USER,
						'push_user_id'   => $order->user_id,
					];

					QueueHelper::toOneTransmissionForUser($order->user_id, $push_info, 'pushAutoCancelNotice');
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 分页取历史地址
	 * @param  int $user_id 用户id
	 * @param int  $page 页码
	 * @param int  $page_size 每页大小
	 * @param int  $type 分类id 1是小帮出行，2是小帮快送
	 * @return array          历史地址列表
	 */
	public static function historyAddress($user_id, $page = 1, $page_size = 20, $type = Ref::ORDER_TYPE_TRIP)
	{
		$result     = [
			'list'       => [],
			'pagination' => [],
		];
		$page       = $page > 0 ? $page : 1;
		$query      = HistoryAddress::find()->where(['user_id' => $user_id, 'type' => $type, 'status' => 1]);
		$countQuery = clone $query;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page - 1, 'pageSize' => $page_size]);
		$data       = $query->select(['end_location', 'end_address', 'end_address_detail'])
			->orderBy(['id' => SORT_DESC])
			->offset($pagination->offset)
			->limit($pagination->limit)
			->asArray()->all();

		if ($data) {
			foreach ($data as $k => $v) {
				$result['list'][$k]['name']     = $v['end_address'];
				$result['list'][$k]['location'] = $v['end_location'];
				$result['list'][$k]['address']  = $v['end_address_detail'];
			}
		}
		$result['pagination'] = [
			'page'       => $page,
			'pageSize'   => $page_size,
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}

	/**
	 * 检查某个状态的最新订单
	 * @param int $user_id 用户id
	 * @param int $cate_id 分类id
	 * @param int $status 状态
	 * @return array
	 */
	public static function checkOrder($user_id, $cate_id = Ref::CATE_ID_FOR_ERRAND_BUY, $status = Ref::ORDER_STATUS_DEFAULT)
	{

		$result = [
			'trip_status' => 0,
			'order_no'    => "0",
			'order_count' => 0,
		];

		$order = Order::find()->select(["order_no", "order_id"])
			->where(['user_id' => $user_id, 'order_status' => $status, 'cate_id' => $cate_id])
			->orderBy("order_id DESC")->limit(1)->asArray()
			->one();
		if ($order) {
			$orderTrip             = OrderTrip::findOne(['order_id' => $order['order_id']]);
			$result['trip_status'] = $orderTrip ? $orderTrip->trip_status : 1;
			$result['order_count'] = 1;
			$result['order_no']    = $order['order_no'];
		}

		return $result;
	}


	/**
	 * 平台取消出行订单
	 * @param  string $order_no
	 * @return bool
	 */
	public static function platformCancel($order_no)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::find()->where(['order_no' => $order_no])->andWhere("order_status in (0,1)")->one();
			if ($order) {
				$trip = OrderTrip::findOne(['order_id' => $order->order_id]);
				if ($trip) {
					$updateData        = [
						'order_status' => Ref::ORDER_STATUS_CALL_OFF,
						'update_time'  => time(),
						'cancel_time'  => time(),
					];
					$order->attributes = $updateData;
					$order->save() ? $result = true : Yii::$app->debug->log_info("order_platform_cancel_flow", $order->getErrors());

					$trip->cancel_type = Ref::ERRAND_CANCEL_DEAL_NOTIFY;
					$trip->save() ? $result &= true : Yii::$app->debug->log_info("error_platform_cancel_flow", $order->getErrors());

					$result &= self::saveLogContent($order->order_id, 'platform_cancel', $updateData, "平台取消出行订单");

					if ($result) {

						if ($trip->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY) {

							$msg = $order->order_status == Ref::ORDER_STATUS_CALL_OFF ? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;

							$params = [
								'deal_msg'          => $msg,
								'user_id'           => $order->user_id,
								'provider_id'       => $order->provider_id,
								'order_no'          => $order->order_no,
								'request_cancel_id' => $order->request_cancel_id,
								'cancel_type'       => $trip->cancel_type,
								'robbed'            => $order->robbed,
								'payment_id'        => $order->payment_id,
								'order_from'        => $order->order_from,
								'trip_type'         => $trip->trip_type,
								'order_id'          => $order->order_id,
								'url'               => PushHelper::TRIP_BIKE_PLATFORM_CANCEL
							];
							TripBikeHelper::pushToUserNotice($order->order_id, TripBikeHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $params);
							if ($order->robbed == Ref::ORDER_ROBBED) {
								TripBikeHelper::pushToProviderNotice($order->order_id, TripBikeHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $params);
							}

						}
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



	//出行派单
	//推送给小帮
	public static function assignTripOrder($order_no)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $order_no, 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
		if ($order->provider_id) {
			//TODO 推送成功返回true

		} else {
			Yii::$app->debug->push_info("assign_order_push" . $order->order_no, "没有商家接单");
		}

		return $result;

	}

	/**
	 * 更新订单消费金额
	 * @param $orderId
	 * @param $fee
	 * @return bool
	 */
	public static function updateOrderFee($orderId, $fee)
	{
		$result    = false;
		$orderTrip = OrderTrip::findOne(['order_id' => $orderId]);
		if ($orderTrip) {
			$orderTrip->total_fee = bcadd($orderTrip->total_fee, $fee, 2);
			$orderTrip->fee_time  = time();
			$orderTrip->save() ? $result = true : Yii::$app->debug->log_info("orderTrip", $orderTrip->getErrors());
		}

		return $result;
	}
}

