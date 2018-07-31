<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace common\helpers\utils;

use api_worker\modules\v1\helpers\WorkerOrderHelper;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderTrip;
use common\models\users\BizInfo;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class PushHelper extends HelperBase
{

	//公共
	const ERRAND_SHOP_ORDER_PUSH  = 'errand.shop.order.push';    //抢单推送	小帮端
	const GRAB_BIZ_TMP_ORDER      = 'grab.biz.tmp.order';        //企业送抢单推送查看	小帮端
	const TRIP_SHOP_ORDER_PUSH    = 'trip.shop.order.push';        //小帮出行推送抢单   小帮端
	const ERRAND_REASSIGN_BEFORE  = 'errand.reassign.previous.notice';    //快送类 改派订单 推送给前一个小帮
	const TMP_BIZ_REASSIGN_BEFORE = 'biz.send.tmp.reassign.previous.notice';    //企业临时订单 改派订单 推送给前一个小帮
	const TRIP_REASSIGN_BEFORE    = 'trip.reassign.previous.notice';                //出行类 改派订单 推送给前一个小帮

	//帮我办
	//To User
	const ERRAND_DO_WORKER_TASK        = 'errand.helper.worker.task';        //小帮任务页
	const ERRAND_DO_WORKER_CANCEL      = 'errand.helper.worker.cancel';      //小帮订单取消
	const ERRAND_DO_AUTO_CANCEL        = 'errand.helper.auto.cancel';        //订单自动取消
	const ERRAND_DO_ASSIGN_USER_NOTICE = 'errand.helper.assign.user.notice';    //订单改派或派单推送消息给用户
	//To worker
	const ERRAND_DO_USER_CONFIRM           = 'errand.helper.user.confirm';        //订单用户确认
	const ERRAND_DO_USER_CANCEL            = 'errand.helper.user.cancel';        //用户订单取消
	const ERRAND_DO_FEE_NOTICE             = 'errand.helper.small.fee.notice';    //添加小费通知
	const ERRAND_DO_PLATFORM_CANCEL        = 'errand.helper.platform.cancel';    //平台取消订单
	const ERRAND_DO_ASSIGN_PROVIDER_NOTICE = 'errand.helper.assign.provider.notice';    //订单指派给商家

	//帮我买
	//To User
	const ERRAND_BUY_WORKER_TASK        = 'errand.buy.worker.task';        //小帮任务页
	const ERRAND_BUY_WORKER_CANCEL      = 'errand.buy.worker.cancel';      //小帮订单取消
	const ERRAND_BUY_AUTO_CANCEL        = 'errand.buy.auto.cancel';        //订单自动取消
	const ERRAND_BUY_WORKER_EXPENSE     = 'errand.buy.worker.expense';     //小帮添加配送费用
	const ERRAND_BUY_ASSIGN_USER_NOTICE = 'errand.buy.assign.user.notice';    //订单改派或派单推送消息给用户
	//To worker
	const ERRAND_BUY_USER_CONFIRM           = 'errand.buy.user.confirm';        //订单用户确认
	const ERRAND_BUY_USER_CANCEL            = 'errand.buy.user.cancel';         //用户订单取消
	const ERRAND_BUY_USER_EXPENSE           = 'errand.buy.user.pay.expense';    //用户已付配送费用通知
	const ERRAND_BUY_PLATFORM_CANCEL        = 'errand.buy.platform.cancel';    //平台取消订单
	const ERRAND_BUY_ASSIGN_PROVIDER_NOTICE = 'errand.buy.assign.provider.notice';    //订单指派给商家

	//帮我送
	//To User
	const ERRAND_SEND_WORKER_TASK        = 'errand.send.worker.task';        //小帮任务页
	const ERRAND_SEND_WORKER_CANCEL      = 'errand.send.worker.cancel';      //小帮订单取消
	const ERRAND_SEND_AUTO_CANCEL        = 'errand.send.auto.cancel';        //订单自动取消
	const ERRAND_SEND_ASSIGN_USER_NOTICE = 'errand.send.assign.user.notice';    //订单改派或派单推送消息给用户
	//To worker
	const ERRAND_SEND_USER_CONFIRM           = 'errand.send.user.confirm';        //订单用户确认
	const ERRAND_SEND_USER_CANCEL            = 'errand.send.user.cancel';        //用户订单取消
	const ERRAND_SEND_FEE_NOTICE             = 'errand.send.small.fee.notice';    //添加小费通知
	const ERRAND_SEND_PLATFORM_CANCEL        = 'errand.send.platform.cancel';    //平台取消订单
	const ERRAND_SEND_ASSIGN_PROVIDER_NOTICE = 'errand.send.assign.provider.notice';    //订单指派给商家

	//企业送临时发单
	//To User
	const TMP_BIZ_SEND_WORKER_TASK            = 'biz.send.tmp.worker.accept';    //小帮已经接单
	const TMP_BIZ_SEND_WORKER_INPUT           = 'biz.send.tmp.worker.input';     //小帮已经录入订单
	const TMP_BIZ_SEND_WORKER_CANCEL          = 'biz.send.tmp.worker.cancel';    //小帮取消临时订单
	const TMP_BIZ_SEND_PLATFORM_CANCEL        = 'biz.send.tmp.platform.cancel';    //平台取消临时订单
	const TMP_BIZ_SEND_ASSIGN_PROVIDER_NOTICE = 'biz.send.tmp.assign.provider.notice';    //订单指派给小帮

	//企业送正式发单流程
	//To User
	const BIZ_SEND_WORKER_TASK        = 'biz.send.worker.task';        //小帮任务页(发单)
	const BIZ_SEND_WORKER_CANCEL      = 'biz.send.worker.cancel';      //小帮订单取消
	const BIZ_SEND_AUTO_CANCEL        = 'biz.send.auto.cancel';        //订单自动取消
	const BIZ_SEND_ASSIGN_USER_NOTICE = 'biz.send.assign.user.notice';    //订单改派或派单推送消息给用户
	//To worker
	const BIZ_SEND_USER_CONFIRM           = 'biz.send.user.confirm';        //订单用户确认
	const BIZ_SEND_USER_CANCEL            = 'biz.send.user.cancel';        //用户订单取消
	const BIZ_SEND_FEE_NOTICE             = 'biz.send.small.fee.notice';    //添加小费通知
	const BIZ_SEND_PLATFORM_CANCEL        = 'biz.send.platform.cancel';    //平台取消订单
	const BIZ_SEND_ASSIGN_PROVIDER_NOTICE = 'biz.send.assign.provider.notice';    //订单指派给商家

	//小帮出行
	//To User
	const TRIP_BIKE_WAITE_TIP          = 'trip.bike.waite.tip';        //等待接单提示
	const TRIP_BIKE_WORKER_TASK        = 'trip.bike.worker.task';        //小帮任务页
	const TRIP_BIKE_WORKER_CANCEL      = 'trip.bike.worker.cancel';      //小帮订单取消
	const TRIP_BIKE_AUTO_CANCEL        = 'trip.bike.auto.cancel';        //订单自动取消
	const TRIP_BIKE_ASSIGN_USER_NOTICE = 'trip.bike.assign.user.notice';    //订单改派或派单推送消息给用户
	//To worker
	const TRIP_BIKE_USER_PAY               = 'trip.bike.user.pay';        //订单用户支付
	const TRIP_BIKE_USER_CANCEL            = 'trip.bike.user.cancel';         //用户订单取消
	const TRIP_BIKE_USER_PRESS             = 'trip.bike.user.press';    //用户催一催
	const TRIP_BIKE_PLATFORM_CANCEL        = 'trip.bike.platform.cancel';    //平台取消订单
	const TRIP_BIKE_ASSIGN_PROVIDER_NOTICE = 'trip.bike.assign.provider.notice';    //订单指派给小帮

	/**
	 * 用户端的透传数据
	 * @param $cid
	 * @param $content
	 * @param $inFormContent
	 * @param $inFormTitle
	 * @return mixed
	 */
	public static function oldUserSendOneTransmission($cid, $content, $inFormContent = null, $inFormTitle = null)
	{
		$result = false;
		if ($cid) {
			$result = Yii::$app->GTPush->sendToOneTransmission($cid, $content, $inFormContent, $inFormTitle);
		} else {
			Yii::$app->debug->log_info("userSendToOneTransmission", 'cid为空');
		}

		return $result;
	}


	/**
	 * 用户端的透传数据
	 * @param $cid
	 * @param $content
	 * @param $inFormContent
	 * @param $inFormTitle
	 * @return mixed
	 */
	public static function userSendOneTransmission($cid, $content, $inFormContent = null, $inFormTitle = null)
	{
		$result = false;
		if ($cid) {
			$result = Yii::$app->GTPushUser->sendToOneTransmission($cid, $content, $inFormContent, $inFormTitle);
		} else {
			Yii::$app->debug->log_info("userSendToOneTransmission", 'cid为空');
		}

		return $result;
	}


	/**
	 * 小帮端的透传数据
	 *
	 * @param $cid
	 * @param $content
	 * @param $inFormContent
	 * @param $inFormTitle
	 * @return mixed
	 */
	public static function providerSendOneTransmission($cid, $content, $inFormContent = null, $inFormTitle = null)
	{
		$result = false;
		if ($cid) {
			$result = Yii::$app->GTPushProvider->sendToOneTransmission($cid, $content, $inFormContent, $inFormTitle);
		} else {
			Yii::$app->debug->log_info("providerSendToOneTransmission", 'cid为空');
		}

		return $result;
	}

	/**
	 * 获取指定区域的数据
	 * @param $provider_id
	 * @param $range
	 * @return int
	 */
	public static function getProviderRange($provider_id, $range)
	{
		$data = UserHelper::getShopInfo($provider_id, ['city_id', 'area_id', 'range']);

		if ($data) {
			$res   = WorkerOrderHelper::getProviderRange($data, 1);
			$range = $res * 1000;
		}

		return $range;
	}

	/**
	 * 查找附近商家的数据
	 * @param $center_location
	 * @param $cate_id
	 * @return array|bool
	 */
	public static function nearbyShop($center_location, $cate_id)
	{
		$data   = false;
		$center = AMapHelper::convert($center_location);

		if ($center) {
			$filter      = ['cate_id:' . $cate_id];
			$nearbyShops = AMapHelper::around($center, $filter);
		} else {
			Yii::$app->debug->job_info('中心点坐标无法解析为高德坐标');

			return $data;
		}

		if ($nearbyShops) {
			foreach ($nearbyShops as $key => $value) {

				$location = AMapHelper::coordToStr('[' . $center_location . ']');
				//坐标值
				$shop_location      = is_array($value['original_coord']) ? json_encode($value['original_coord']) : $value['original_coord'];
				$route              = LBSHelper::routeMatrix(AMapHelper::coordToStr($shop_location), $location); //订单起点坐标，商家当前坐标
				$distance_text      = is_array($route) ? $route['distance']['text'] : '无数据';                    //司机起点与订单起点的距离
				$value['distance']  = $distance_text;
				$value['cate_name'] = CateListHelper::getCateName($value['cate_id']);
				$data[]             = $value;

			} //foreach
		}//if

		return $data;
	}


	/**
	 * 小帮快送订单推送公共接口
	 * @param $order_id
	 */
	public static function errandSendOrder($order_id)
	{
		//1、查找订单信息
		//2、基于LBS查找附近的小帮
		//2-1、判断小帮是否空闲
		//2-2、排除发单人是本人
		//2-3、筛选符合条件的小帮
		//3、推送一次。（队列中处理）

		$order = Order::findOne(['order_id' => $order_id, 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DOING]);
		if ($order) {

			//重发推送
			$ttl = YII_ENV_PROD ? 60 * 2 : 60 * 1;
			QueueHelper::errandSendOrder($order->order_id, true, $ttl);

			$start_location = $order->start_location;

			$filter      = [
				'cate_id:' . Ref::CATE_ID_FOR_ERRAND
			];
			$nearbyShops = AMapHelper::around($start_location, $filter, 10000);
			$errand      = OrderErrand::findOne(['order_id' => $order_id]);

			Yii::$app->debug->push_info("附近商家", $nearbyShops);
			if ($nearbyShops && $errand) {

				$providerData = [];
				foreach ($nearbyShops as $key => $provider) {

					if ($provider['user_id'] == $order->user_id) {
						Yii::$app->debug->push_info("发单人和推送是同一个人", $order->user_id);
						continue;
					}

					$range             = self::getProviderRange($provider['provider_id'], $provider['range']);    //小帮接单范围
					$provider_location = $provider['_location'];
					$route             = AMapHelper::bicycling($provider_location, AMapHelper::coordToStr($start_location)); //小帮当前坐标，商家坐标
					$distance          = is_array($route) ? $route['distance'] : '-1';
					$distance_text     = is_array($route) ? UtilsHelper::distance($route['distance']) : '-1';
					$duration          = is_array($route) ? $route['duration'] : '-1';
					$duration_text     = is_array($route) ? UtilsHelper::durationLabel($route['duration']) : '-1';


					Yii::$app->debug->push_info($provider['mobile'] . "接单范围：" . $range . "司机起点与订单起点的距离", $distance);

					if ($range > $distance && $distance != '-1')    //如果司机的接单范围大于司机起点与订单起点的距离
					{
						$providerData[] = [
							'provider_id'      => $provider['provider_id'],
							'provider_mobile'  => $provider['mobile'],
							'distance'         => $distance,
							'distance_text'    => $distance_text,
							'provider_user_id' => $provider['user_id'],
							'duration'         => $duration,
							'duration_text'    => $duration_text,
						];

					} else {
						Yii::$app->debug->push_info("不符合条件" . $provider['user_id'] . json_encode($route));
					}
				}//foreach

				if ($providerData) {

					$order_title  = ErrandHelper::getErrandType($errand->errand_type);
					$today        = strtotime(date('Y-m-d', time()));
					$service_time = strtotime(date('Y-m-d', $errand->service_time));
					if ($today == $service_time) {
						//今天
						$service_time = '今天' . date('H:i', $errand->service_time);
					} else {
						$service_time = date('m-d H:i', $errand->service_time);
					}

					$push_info = [
						"glx"            => Ref::GETUI_TYPE_GRAB,//个推类型
						"to"             => Ref::GETUI_TO_ERRAND_ORDER,
						"url"            => PushHelper::ERRAND_SHOP_ORDER_PUSH,
						"order_no"       => $order->order_no,
						"title"          => $order_title,                     //标题
						"inform_content" => '有新的用户订单消息，请查看！',
						"errand_type"    => $errand->errand_type,
						"type"           => $order->order_type,
						'push_role'      => Ref::PUSH_ROLE_PROVIDER,
					];

					if ($errand->errand_type == Ref::ERRAND_TYPE_DO) {

						$push_info["order_amount"]  = $order->order_amount;                //办事费
						$push_info["content"]       = $errand->errand_content;            //办事内容
						$push_info["start_address"] = $order->start_address;            //办事地点
						$push_info["service_qty"]   = $errand->service_qty . "小时";        //办事时长
						$push_info["service_time"]  = $service_time;                    //时间
					}

					if ($errand->errand_type == Ref::ERRAND_TYPE_BUY) {
						$push_info["order_amount"]  = $order->order_amount;              //快送费
						$push_info["content"]       = $errand->errand_content;           //需要买
						$push_info["start_address"] = $order->start_address;            //购买地
						$push_info["end_address"]   = $order->end_address;                //收货地
						$push_info["service_time"]  = $service_time;                    //收货时间
					}

					if ($errand->errand_type == Ref::ERRAND_TYPE_SEND) {
						$push_info["order_amount"]  = $order->order_amount;              //快送费
						$push_info["content"]       = $errand->errand_content;           //需要买
						$push_info["start_address"] = $order->start_address;            //购买地
						$push_info["end_address"]   = $order->end_address;                //收货地
						$push_info["service_time"]  = $service_time;                    //收货时间
					}

					//根据时间排序一下
					ArrayHelper::multisort($providerData, 'duration');
					foreach ($providerData as $item) {

						$push_info['provider_mobile'] = $item['provider_mobile'];
						$push_info["distance"]        = $item['distance'];//推送距离商家多远
						$push_info["distance_tip"]    = '距您约' . $item['distance_text'];      //小帮与起点距离
						$push_info["push_user_id"]    = $item['provider_user_id'];
						$push_info['duration']        = $item['duration'];
						$push_info['duration_text']   = $item['duration_text'];
						$push_info['log']             = date("Y-m-d H:i:s");
						Yii::$app->debug->job_info('push_info', $push_info);
						QueueHelper::toOneTransmissionForProvider($item['provider_id'], $push_info, $errand->errand_type . 'errand_send_order');
					}
				}
			}//if
		}//if
	}

	/**
	 * 小帮快送辅助推送
	 */
	public static function errandSendOrderPlus($orderId)
	{

		//1、查找订单信息
		//1-1、根据订单调价
		//2、基于SHOP表查找上班的小帮
		//2-1、判断小帮是否空闲
		//2-2、排除发单人是本人
		//2-3、筛选符合条件的小帮
		//3、推送一次。（队列中处理）

		$order = Order::findOne(['order_id' => $orderId, 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DOING]);
		if ($order) {
			$start_location = $order->start_location;
			$orderData = ArrayHelper::toArray($order);

			$nearbyShops = ShopHelper::getOnlineProviderData($orderData);
			$errand      = OrderErrand::findOne(['order_id' => $order->order_id]);

			Yii::$app->debug->push_info("附近商家", $nearbyShops);
			if ($nearbyShops && $errand) {

				$providerData = [];
				foreach ($nearbyShops as $key => $provider) {

					if ($provider['user_id'] == $order->user_id) {
						Yii::$app->debug->push_info("发单人和推送是同一个人", $order->user_id);
						continue;
					}

					$range             = self::getProviderRange($provider['provider_id'], $provider['range']);    //小帮接单范围
					$provider_location = $provider['_location'];
					$route             = AMapHelper::bicycling(AMapHelper::coordToStr($provider_location), AMapHelper::coordToStr($start_location)); //小帮当前坐标，商家坐标
					$distance          = is_array($route) ? $route['distance'] : '-1';
					$distance_text     = is_array($route) ? UtilsHelper::distance($route['distance']) : '-1';
					$duration          = is_array($route) ? $route['duration'] : '-1';
					$duration_text     = is_array($route) ? UtilsHelper::durationLabel($route['duration']) : '-1';


					Yii::$app->debug->push_info($provider['mobile'] . "接单范围：" . $range . "司机起点与订单起点的距离", $distance);

					if ($range > $distance && $distance != '-1')    //如果司机的接单范围大于司机起点与订单起点的距离
					{
						$providerData[] = [
							'provider_id'      => $provider['provider_id'],
							'provider_mobile'  => $provider['mobile'],
							'distance'         => $distance,
							'distance_text'    => $distance_text,
							'provider_user_id' => $provider['user_id'],
							'duration'         => $duration,
							'duration_text'    => $duration_text,
						];

					} else {
						Yii::$app->debug->push_info("不符合条件" . $provider['user_id'] . json_encode($route));
					}
				}//foreach

				if ($providerData) {

					$order_title  = ErrandHelper::getErrandType($errand->errand_type);
					$today        = strtotime(date('Y-m-d', time()));
					$service_time = strtotime(date('Y-m-d', $errand->service_time));
					if ($today == $service_time) {
						//今天
						$service_time = '今天' . date('H:i', $errand->service_time);
					} else {
						$service_time = date('m-d H:i', $errand->service_time);
					}

					$push_info = [
						"glx"            => Ref::GETUI_TYPE_GRAB,//个推类型
						"to"             => Ref::GETUI_TO_ERRAND_ORDER,
						"url"            => PushHelper::ERRAND_SHOP_ORDER_PUSH,
						"order_no"       => $order->order_no,
						"title"          => $order_title,                     //标题
						"inform_content" => '您有新的订单！',
						"errand_type"    => $errand->errand_type,
						"type"           => $order->order_type,
						'push_role'      => Ref::PUSH_ROLE_PROVIDER,
					];

					if ($errand->errand_type == Ref::ERRAND_TYPE_DO) {

						$push_info["order_amount"]  = $order->order_amount;                //办事费
						$push_info["content"]       = $errand->errand_content;            //办事内容
						$push_info["start_address"] = $order->start_address;            //办事地点
						$push_info["service_qty"]   = $errand->service_qty . "小时";        //办事时长
						$push_info["service_time"]  = $service_time;                    //时间
					}

					if ($errand->errand_type == Ref::ERRAND_TYPE_BUY) {
						$push_info["order_amount"]  = $order->order_amount;              //快送费
						$push_info["content"]       = $errand->errand_content;           //需要买
						$push_info["start_address"] = $order->start_address;            //购买地
						$push_info["end_address"]   = $order->end_address;                //收货地
						$push_info["service_time"]  = $service_time;                    //收货时间
					}

					if ($errand->errand_type == Ref::ERRAND_TYPE_SEND) {
						$push_info["order_amount"]  = $order->order_amount;              //快送费
						$push_info["content"]       = $errand->errand_content;           //需要买
						$push_info["start_address"] = $order->start_address;            //购买地
						$push_info["end_address"]   = $order->end_address;                //收货地
						$push_info["service_time"]  = $service_time;                    //收货时间
					}

					//根据时间排序一下
					ArrayHelper::multisort($providerData, 'duration');
					foreach ($providerData as $item) {

						$push_info['provider_mobile'] = $item['provider_mobile'];
						$push_info["distance"]        = $item['distance'];//推送距离商家多远
						$push_info["distance_tip"]    = '距您约' . $item['distance_text'];      //小帮与起点距离
						$push_info["push_user_id"]    = $item['provider_user_id'];
						$push_info['duration']        = $item['duration'];
						$push_info['duration_text']   = $item['duration_text'];
						$push_info['log']             = date("Y-m-d H:i:s");
						Yii::$app->debug->job_info('push_info', $push_info);
						QueueHelper::toOneTransmissionForProvider($item['provider_id'], $push_info, $errand->errand_type . 'errand_send_order');
					}
				}
			}//if
		}//if

	}


	/**
	 * 企业送订单推送提醒
	 * @param $params
	 */
	public static function bizSendOrder($batchNo)
	{
		//1、查找订单信息
		//2、基于LBS查找附近的小帮
		//2-1、判断小帮是否空闲
		//2-2、排除发单人是本人
		//2-3、筛选符合条件的小帮
		//3、推送一次。（队列中处理）

		$tmpOrder = BizTmpOrder::findOne(['batch_no' => $batchNo, 'robbed' => Ref::ORDER_ROB_NEW, 'tmp_status' => Ref::BIZ_TMP_STATUS_WAITE]);
		if ($tmpOrder) {

			//重发推送
			$ttl = YII_ENV_PROD ? 60 * 2 : 60 * 1;
			QueueHelper::bizSendOrder($tmpOrder->batch_no, true, $ttl);

			$start_location = $tmpOrder->start_location;

			$filter      = [
				'cate_id:' . Ref::CATE_ID_FOR_ERRAND
			];
			$nearbyShops = AMapHelper::around($start_location, $filter);
			Yii::$app->debug->push_info("附近商家", $nearbyShops);

			$order_user_id = $tmpOrder->user_id;

			if ($nearbyShops) {

				$providerData = [];
				foreach ($nearbyShops as $key => $provider) {

					if ($provider['user_id'] == $order_user_id) {
						Yii::$app->debug->push_info("发单人和推送是同一个人", $order_user_id);
						continue;
					}

					//TODO 判断有没有进行中的企业送
					$range             = self::getProviderRange($provider['provider_id'], $provider['range']);    //小帮接单范围
					$provider_location = $provider['_location'];
					$route             = AMapHelper::bicycling($provider_location, AMapHelper::coordToStr($start_location)); //小帮当前坐标，商家坐标
					$distance          = is_array($route) ? $route['distance'] : '-1';
					$distance_text     = is_array($route) ? UtilsHelper::distance($route['distance']) : '-1';
					$duration          = is_array($route) ? $route['duration'] : '-1';
					$duration_text     = is_array($route) ? UtilsHelper::durationLabel($route['duration']) : '-1';


					Yii::$app->debug->push_info($provider['mobile'] . "接单范围：" . $range . "司机起点与订单起点的距离", $distance);

					if ($range > $distance && $distance != '-1')    //如果司机的接单范围大于司机起点与订单起点的距离
					{
						$providerData[] = [
							'provider_id'      => $provider['provider_id'],
							'provider_mobile'  => $provider['mobile'],
							'distance'         => $distance,
							'distance_text'    => $distance_text,
							'provider_user_id' => $provider['user_id'],
							'duration'         => $duration,
							'duration_text'    => $duration_text,
						];

					} else {
						Yii::$app->debug->push_info("不符合条件" . $provider['user_id'] . json_encode($route));
					}
				}//foreach

				if ($providerData) {

					$biz       = BizInfo::findOne(['user_id' => $order_user_id]);
					$push_info = [
						"glx"            => Ref::GETUI_TYPE_BIZ_GRAB,//个推类型
						"to"             => Ref::GETUI_TO_BIZ_ORDER,
						"url"            => PushHelper::GRAB_BIZ_TMP_ORDER,
						"title"          => "企业送",
						"inform_content" => '有新的用户订单消息，请查看！',
						"content"        => $tmpOrder->content,
						"start_address"  => $tmpOrder->start_address,
						'start_location' => $tmpOrder->start_location,
						'push_role'      => Ref::PUSH_ROLE_PROVIDER,
						'qty'            => $tmpOrder->tmp_qty,
						'service_time'   => '立即取货',
						//迭代新增加内容
						'delivery_area'  => isset($tmpOrder->delivery_area) ? $tmpOrder->delivery_area : '其他',
						'biz_name'       => $biz ? $biz->biz_name : '无忧企业',
						'tmp_no'         => isset($tmpOrder->tmp_no) ? $tmpOrder->tmp_no : 0,
					];

					//根据时间排序一下
					ArrayHelper::multisort($providerData, 'duration');
					foreach ($providerData as $item) {

						$push_info['provider_mobile'] = $item['provider_mobile'];
						$push_info["distance"]        = $item['distance'];//推送距离商家多远
						$push_info["distance_tip"]    = '距您约' . $item['distance_text'];      //小帮与起点距离
						$push_info["push_user_id"]    = $item['provider_user_id'];
						$push_info['duration']        = $item['duration'];
						$push_info['duration_text']   = $item['duration_text'];
						$push_info['log']             = date("Y-m-d H:i:s");
						Yii::$app->debug->job_info('push_info', $push_info);
						QueueHelper::toOneTransmissionForProvider($item['provider_id'], $push_info, 'biz_send_order');
					}
				}
			}//if
		}//if
	}

	/**
	 * 企业送订单辅助推送
	 * @param $params
	 */
	public static function bizSendOrderPlus($batchNo)
	{
		//1、查找订单信息
		//2、基于SHOP表查找上班的小帮
		//2-1、判断小帮是否空闲
		//2-2、排除发单人是本人
		//2-3、筛选符合条件的小帮
		//3、推送一次。（队列中处理）

		$tmpOrder = BizTmpOrder::findOne(['batch_no' => $batchNo, 'robbed' => Ref::ORDER_ROB_NEW, 'tmp_status' => Ref::BIZ_TMP_STATUS_WAITE]);
		if ($tmpOrder) {
			$start_location = $tmpOrder->start_location;
			$orderData   = ArrayHelper::toArray($tmpOrder);
			$nearbyShops = ShopHelper::getOnlineProviderData($orderData);
			Yii::$app->debug->push_info("附近商家", $nearbyShops);

			$order_user_id = $tmpOrder->user_id;

			if ($nearbyShops) {

				$providerData = [];
				foreach ($nearbyShops as $key => $provider) {

					if ($provider['user_id'] == $order_user_id) {
						Yii::$app->debug->push_info("发单人和推送是同一个人", $order_user_id);
						continue;
					}

					//TODO 判断有没有进行中的企业送
					$range             = self::getProviderRange($provider['provider_id'], $provider['range']);    //小帮接单范围
					$provider_location = $provider['_location'];
					$route             = AMapHelper::bicycling(AMapHelper::coordToStr($provider_location), AMapHelper::coordToStr($start_location)); //小帮当前坐标，商家坐标
					$distance          = is_array($route) ? $route['distance'] : '-1';
					$distance_text     = is_array($route) ? UtilsHelper::distance($route['distance']) : '-1';
					$duration          = is_array($route) ? $route['duration'] : '-1';
					$duration_text     = is_array($route) ? UtilsHelper::durationLabel($route['duration']) : '-1';


					Yii::$app->debug->push_info($provider['mobile'] . "接单范围：" . $range . "司机起点与订单起点的距离", $distance);

					if ($range > $distance && $distance != '-1')    //如果司机的接单范围大于司机起点与订单起点的距离
					{
						$providerData[] = [
							'provider_id'      => $provider['provider_id'],
							'provider_mobile'  => $provider['mobile'],
							'distance'         => $distance,
							'distance_text'    => $distance_text,
							'provider_user_id' => $provider['user_id'],
							'duration'         => $duration,
							'duration_text'    => $duration_text,
						];

					} else {
						Yii::$app->debug->push_info("不符合条件" . $provider['user_id'] . json_encode($route));
					}
				}//foreach

				if ($providerData) {

					$biz       = BizInfo::findOne(['user_id' => $order_user_id]);
					$push_info = [
						"glx"            => Ref::GETUI_TYPE_BIZ_GRAB,//个推类型
						"to"             => Ref::GETUI_TO_BIZ_ORDER,
						"url"            => PushHelper::GRAB_BIZ_TMP_ORDER,
						"title"          => "企业送",
						"inform_content" => '您有新的订单！',
						"content"        => $tmpOrder->content,
						"start_address"  => $tmpOrder->start_address,
						'start_location' => $tmpOrder->start_location,
						'push_role'      => Ref::PUSH_ROLE_PROVIDER,
						'qty'            => $tmpOrder->tmp_qty,
						'service_time'   => '立即取货',
						//迭代新增加内容
						'delivery_area'  => isset($tmpOrder->delivery_area) ? $tmpOrder->delivery_area : '其他',
						'biz_name'       => $biz ? $biz->biz_name : '无忧企业',
						'tmp_no'         => isset($tmpOrder->tmp_no) ? $tmpOrder->tmp_no : 0,
					];

					//根据时间排序一下
					ArrayHelper::multisort($providerData, 'duration');
					foreach ($providerData as $item) {

						$push_info['provider_mobile'] = $item['provider_mobile'];
						$push_info["distance"]        = $item['distance'];//推送距离商家多远
						$push_info["distance_tip"]    = '距您约' . $item['distance_text'];      //小帮与起点距离
						$push_info["push_user_id"]    = $item['provider_user_id'];
						$push_info['duration']        = $item['duration'];
						$push_info['duration_text']   = $item['duration_text'];
						$push_info['log']             = date("Y-m-d H:i:s");
						Yii::$app->debug->job_info('push_info', $push_info);
						QueueHelper::toOneTransmissionForProvider($item['provider_id'], $push_info, 'biz_send_order');
					}
				}
			}//if
		}//if
	}

	//推送给旧的摩的小帮版
	public static function pushToOldModi($order_id, $shop_id, $type)
	{

		//订单推送	modi.shop.order.push
		//用户取消订单	modi.shop.order.user.cancel
		//用户申请取消订单	modi.shop.order.user.cancel.apply
		//用户付款	modi.shop.order.user.pay

		$push_info = [];
		if ($type == 'cancel') {
			$push_info['glx']            = 3;
			$push_info['to']             = 'modi.order';
			$push_info['url']            = 'modi.shop.order.user.cancel';
			$push_info['ulx']            = 1;
			$push_info['inform_content'] = "用户取消订单";
			$push_info['orderid']        = $order_id;
		}

		if ($type == 'apply_cancel') {
			$content                     = '用户申请取消订单，请查看';
			$push_info['glx']            = 7;
			$push_info['to']             = 'modi.order';
			$push_info['url']            = 'modi.shop.order.user.cancel.apply';
			$push_info['ulx']            = 2;
			$push_info['cancel_content'] = $content;
			$push_info['content']        = $content;
			$push_info['inform_content'] = $content;
			$push_info['orderid']        = $order_id;
		}

		if ($type == 'pay') {
			$content                     = '用户已经支付了订单，请查看!';
			$push_info['glx']            = 5;
			$push_info['to']             = 'modi.order';
			$push_info['url']            = 'modi.shop.order.user.pay';
			$push_info['inform_content'] = $content;
			$push_info['orderid']        = $order_id;
		}

		count($push_info) > 0 ? QueueHelper::toOneTransmissionForProvider($shop_id, $push_info, 'pushToOldModi' . $type) : null;
	}

	//摩的推送给用户版
	public static function pushToUserOldModi($order_id, $user_id, $type)
	{
		//订单被抢  modi.user.order.grab
		//小帮取消订单 modi.user.order.shop.cancel
		//小帮申请取消订单 modi.user.order.shop.cancel.apply
		//小帮不同意申请取消订单  modi.user.order.shop.noagree
		//小帮同意申请取消订单    modi.user.order.shop.agree
		//小帮已接乘客   modi.user.order.shop.touch
		//安全到达目的地  modi.user.order.reach

		$push_info = [];
		if ($type == 'grab') {
			$order_data                  = (new Query())->from("bb_51_orders")->select(['s_uid'])->where(['orderid' => $order_id])->one();
			$push_info['glx']            = 2;
			$push_info['to']             = 'modi.order';
			$push_info['url']            = 'modi.user.order.grab';
			$push_info['uid']            = $user_id;
			$push_info['cate_id']        = Ref::CATE_ID_FOR_MOTOR;
			$push_info['orderid']        = $order_id;
			$push_info['s_uid']          = $order_data['s_uid'];
			$push_info['inform_content'] = "您的订单已经被小帮接受，请查看!";
		}

		if ($type == 'cancel') {
			$push_info['glx']            = 3;
			$push_info['to']             = 'modi.order';
			$push_info['url']            = 'modi.user.order.shop.cancel';
			$push_info['uid']            = $user_id;
			$push_info['cate_id']        = Ref::CATE_ID_FOR_MOTOR;
			$push_info['orderid']        = $order_id;
			$push_info['ulx']            = 2;
			$push_info['inform_content'] = '小帮已经取消了订单，请查看!';
		}

		if ($type == 'apply_cancel') {
			$push_info['glx']            = 7;
			$push_info['orderid']        = $order_id;
			$push_info['to']             = 'modi.order';
			$push_info['url']            = 'modi.user.order.shop.cancel.apply';
			$push_info['uid']            = $user_id;
			$push_info['ulx']            = 2;
			$push_info['cancel_content'] = "小帮取消";
			$push_info['content']        = '小帮申请取消订单，请查看';
		}

		if ($type == 'no_agree') {
			$push_info['glx']            = 8;
			$push_info['orderid']        = $order_id;
			$push_info['to']             = "modi.order";
			$push_info['url']            = "modi.user.order.shop.noagree";
			$push_info['uid']            = $user_id;
			$push_info['ulx']            = 1;
			$push_info['cancel_content'] = '小帮拒绝您的退单请求';
			$push_info['content']        = '小帮已拒绝取消您的取消订单申请，请查看';
			$push_info['inform_content'] = '小帮已拒绝取消您的取消订单申请，请查看';
		}

		if ($type == "agree") {
			$push_info['glx'] = 8;
		}

		if ($type == "touch") {
			$push_info['glx']            = 6;
			$push_info['to']             = "modi.order";
			$push_info['url']            = "modi.user.order.shop.touch";
			$push_info['cate_id']        = Ref::CATE_ID_FOR_MOTOR;
			$push_info['orderid']        = $order_id;
			$push_info['uid']            = $user_id;
			$push_info['trip_status']    = Ref::TRIP_STATUS_PICKED;
			$push_info['inform_content'] = '小帮已接乘客！';

		}

		if ($type == "reach") {
			$push_info['glx']            = 6;
			$push_info['to']             = "modi.order";
			$push_info['url']            = "modi.user.order.reach";
			$push_info['cate_id']        = Ref::CATE_ID_FOR_MOTOR;
			$push_info['orderid']        = $order_id;
			$push_info['uid']            = $user_id;
			$push_info['trip_status']    = 3;
			$push_info['inform_content'] = '地点已到达!';
		}

		count($push_info) > 0 ? QueueHelper::toOneTransmissionForUser($user_id, $push_info, 'pushToOldModi' . $type) : null;
	}

	/**
	 * 推送链接给过度用户
	 * @param $cid
	 * @param $title
	 * @param $content
	 * @param $link
	 * @return bool
	 */
	public static function pushLinkToOverUser($cid, $title, $content, $link)
	{
		$result = false;
		$push   = Yii::$app->GTPush->sendToOneHtmlLink($cid, $title, $content, $link);
		($push['result'] == "ok") ? $result = true : Yii::$app->debug->log_info('push_link', $push);

		return $result;
	}

	/**
	 * 推送链接给用户
	 * @param $cid
	 * @param $title
	 * @param $content
	 * @param $link
	 * @return bool
	 */
	public static function pushLinkToUser($cid, $title, $content, $link)
	{
		$result = false;
		$push   = Yii::$app->GTPushUser->sendToOneHtmlLink($cid, $title, $content, $link);
		($push['result'] == "ok") ? $result = true : Yii::$app->debug->log_info('push_link', $push);

		return $result;
	}

	/**
	 * 推送链接给用户
	 * @param $cid
	 * @param $title
	 * @param $content
	 * @param $link
	 * @return bool
	 */
	public static function pushLinkToPrivider($cid, $title, $content, $link)
	{
		$result = false;
		$push   = Yii::$app->GTPushProvider->sendToOneHtmlLink($cid, $title, $content, $link);
		($push['result'] == "ok") ? $result = true : Yii::$app->debug->log_info('push_link', $push);

		return $result;
	}

	/**
	 * 小帮出行订单推送公共接口
	 * @param $order_id
	 */
	public static function tripSendOrder($order_id)
	{
		//1、查找订单信息
		//2、基于LBS查找附近的小帮
		//2-1、判断小帮是否空闲
		//2-2、排除发单人是本人
		//2-3、筛选符合条件的小帮
		//3、推送一次。（队列中处理）

		$order = Order::findOne(['order_id' => $order_id, 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DEFAULT]);
		if ($order) {

			if ($order->create_time > (time() - 1800)) {

				$ttl = YII_ENV_PROD ? 60 * 2 : 60 * 1;
				QueueHelper::tripOrder($order->order_id, true, $ttl);
			}

			$start_location = $order->start_location;

			$filter      = [
				'cate_id:' . Ref::CATE_ID_FOR_MOTOR
			];
			$nearbyShops = AMapHelper::around($start_location, $filter, 10000);
			$trip        = OrderTrip::findOne(['order_id' => $order_id]);


			Yii::$app->debug->push_info("附近商家", $nearbyShops);
			if ($nearbyShops && $trip) {

				$providerData = [];
				foreach ($nearbyShops as $key => $provider) {

					if ($provider['user_id'] == $order->user_id) {
						Yii::$app->debug->push_info("发单人和推送是同一个人", $order->user_id);
						continue;
					}

					//TODO 判断小帮是否有正在进行的订单，如果有就不推送
//					$modiWhere      = ['s_uid' => $provider['user_id'], 's_sure' => 1, 'robbed' => 1, 'order_style' => 1, 'status' => 1, 'trip_status' => ['in', '1, 2']];
//					$modiOrderCount = (new Query())->select("id'")->from('bb_51_orders')->where($modiWhere)->count('id');
//
//					if ($modiOrderCount > 0) {
//						Yii::$app->debug->push_info($provider['mobile'] . '接单人有摩的订单，订单数', $modiOrderCount);
//						continue;
//					}

					$range = $provider['range'];//小帮接单范围

					$provider_location = $provider['_location'];
					$route             = AMapHelper::bicycling($provider_location, AMapHelper::coordToStr($start_location)); //小帮当前坐标，商家坐标
					$distance          = is_array($route) ? $route['distance'] : '-1';
					$distance_text     = is_array($route) ? UtilsHelper::distance($route['distance']) : '-1';
					$duration          = is_array($route) ? $route['duration'] : '-1';
					$duration_text     = is_array($route) ? UtilsHelper::durationLabel($route['duration']) : '-1';

					Yii::$app->debug->push_info($provider['mobile'] . "接单范围：" . $range . "司机起点与订单起点的距离", $distance);

					if ($range > $distance && $distance != '-1')    //如果司机的接单范围大于司机起点与订单起点的距离
					{
						$providerData[] = [
							'provider_id'      => $provider['provider_id'],
							'provider_mobile'  => $provider['mobile'],
							'distance'         => $distance,
							'distance_text'    => $distance_text,
							'provider_user_id' => $provider['user_id'],
							'duration'         => $duration,
							'duration_text'    => $duration_text,
						];

					} else {
						Yii::$app->debug->push_info("不符合条件" . $provider['user_id'] . json_encode($route));
					}
				}//foreach

				if ($providerData) {

					$order_title = "小帮出行";
					$push_info   = [
						"glx"                    => Ref::GETUI_TYPE_GRAB,
						"to"                     => Ref::GETUI_TO_ERRAND_ORDER,
						"url"                    => PushHelper::TRIP_SHOP_ORDER_PUSH,
						"order_no"               => $order->order_no,
						"title"                  => $order_title,
						"inform_content"         => '有新的用户订单消息，请查看！',
						"type"                   => $order->order_type,
						"push_role"              => Ref::PUSH_ROLE_PROVIDER,
						"start_location"         => $order->start_location,
						"start_address"          => $order->start_address,
						"end_location"           => $order->end_location,
						"end_address"            => $order->end_address,
						"estimate_amount"        => bcadd($trip->estimate_amount, $trip->amount_ext, 2),
						"estimate_distance"      => $trip->estimate_distance,
						'estimate_distance_text' => UtilsHelper::distance($trip->estimate_distance),   //预估行程距离
					];

					//根据时间排序一下
					ArrayHelper::multisort($providerData, 'duration');
					foreach ($providerData as $item) {

						$push_info['provider_mobile'] = $item['provider_mobile'];
						$push_info["distance"]        = $item['distance'];//推送距离商家多远
						$push_info["distance_tip"]    = '距您约' . $item['distance_text'];      //小帮与起点距离
						$push_info["push_user_id"]    = $item['provider_user_id'];
						$push_info['duration']        = $item['duration'];
						$push_info['duration_text']   = $item['duration_text'];
						$push_info['log']             = date("Y-m-d H:i:s");
						Yii::$app->debug->job_info('push_info', $push_info);
						QueueHelper::toOneTransmissionForProvider($item['provider_id'], $push_info, $trip->trip_type . 'trip_send_order');
					}
				}
			}//if
		}//if
	}

}