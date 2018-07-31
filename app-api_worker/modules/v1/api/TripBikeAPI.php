<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_worker\modules\v1\api;

use api_worker\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\TripBikeHelper;
use common\helpers\orders\TripHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\QueueHelper;

class TripBikeAPI extends HelperBase
{
	/**
	 * 通用抢单接口
	 *
	 * @param $provider_id
	 * @return array
	 */
	public static function robbingV10($provider_id)
	{

		$result = [
			'code' => StateCode::ERRAND_ROBBING_FAIL,
			'data' => null
		];

		$shopInfo = UserHelper::getShopInfo($provider_id, ["plate_numbers", "utel", "guangbi"]);
		if ($shopInfo) {

			if ($shopInfo['guangbi'] != 1) {

				$result['code'] = StateCode::SHOP_BLACK;

				return $result;
			}

			$params['order_no']          = SecurityHelper::getBodyParam('order_no'); //订单号
			$params['provider_id']       = $provider_id;
			$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
			$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
			$params['provider_mobile']   = isset($shopInfo['utel']) ? $shopInfo['utel'] : null;
			$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');
			$params['license_plate']     = isset($shopInfo['plate_numbers']) ? $shopInfo['plate_numbers'] : null;

			$res = TripBikeHelper::saveRobbing($params);
			if (!$res) {
				$result['code'] = StateCode::ERRAND_ROBBING;
			} else {
				$result['code'] = 0;
				TripBikeHelper::pushToUserNotice($res['order_id'], TripBikeHelper::PUSH_USER_TYPE_TASK_PROGRESS, $res);
			}
		}

		return $result;
	}

	/**
	 * 通用小帮删除订单
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerDeleteV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		if ($params['order_no'] && strlen($params['order_no']) >= 18) {
			return TripBikeHelper::oldWorkerDelete($params); //旧表订单
		}

		return TripHelper::workerDelete($params);
	}

	/**
	 * 小帮工作流1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerProgressV10($provider_id)
	{
		$result                     = false;
		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['trip_status']      = SecurityHelper::getBodyParam('trip_status');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = SecurityHelper::getBodyParam('current_location');

		$params['provider_id'] = $provider_id;
		$data                  = TripBikeHelper::workerProgress($params);
		if ($data) {

			//推送给用户
			TripBikeHelper::pushToUserNotice($data['order_id'], TripBikeHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

			$result = $data;
		}

		return $result;
	}

	//任务页
	public static function workerTaskV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		return TripBikeHelper::workerTask($params);
	}

	//详细页
	public static function workerDetailV10($provider_id)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'detail';
		if ($params['order_no'] && strlen($params['order_no']) >= 18) {
			return TripBikeHelper::oldWorkerDetail($params); //旧表订单
		}

		return TripBikeHelper::workerDetail($params);
	}

	//小帮取消
	public static function workerCancelV10($provider_id)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$data = TripBikeHelper::workerCancel($params);
		if  ($data) {
			TripBikeHelper::pushToUserNotice($data['order_id'], TripBikeHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);
		}
		return $data;
	}
}