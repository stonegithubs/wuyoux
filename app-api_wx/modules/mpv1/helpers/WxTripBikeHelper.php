<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/10/9
 */

namespace api_wx\modules\mpv1\helpers;

use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\orders\EvaluateHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\orders\TripBikeHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderTrip;

class WxTripBikeHelper extends TripBikeHelper
{

	public static function checkOrderStatus($orderNo)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $orderNo]);
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
					'estimate_amount'        => sprintf("%.2f", $trip->estimate_amount),          //预估费用	TODO 抵扣券的费用
					'estimate_amount_text'   => $trip->estimate_amount . "元（劵已抵3元）",
					'estimate_distance'      => $trip->estimate_distance,        //预估行程距离
					'estimate_distance_text' => UtilsHelper::distance($trip->estimate_distance),   //预估行程距离
					'estimate_discount'      => "3",                            //预估折扣 //TODO 优惠金额
					'license_plate'          => $trip->license_plate,
					'trip_status'            => $trip->trip_status,
					'spend_time'             => 0,
					'robbed'                 => $order->robbed,
					'starting_distance'      => $trip->starting_distance,
					'starting_distance_text' => UtilsHelper::distance($trip->estimate_distance),
					'order_status'           => $order->order_status,
				];

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


				if ($trip->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_APPLY) {
					$result['cancel_content'] = self::CANCEL_PROVIDER_APPLY_MSG;
				}

				if ($trip->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE) {
					$result['cancel_content'] = self::CANCEL_PROVIDER_AGREE_MSG;
				}

				if ($trip->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_DISAGREE) {
					$result['cancel_content'] = self::CANCEL_PROVIDER_DISAGREE_MSG;
				}

				//如果是客服的消息
				if ($trip->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY || $trip->cancel_type == Ref::ERRAND_CANCEL_USER_NOTIFY) {
					$result['cancel_content'] = $order->order_status == Ref::ORDER_STATUS_CALL_OFF
						? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;
				}
				$result['cancel_type'] = $trip->cancel_type;
			}
		}

		return $result;
	}
}