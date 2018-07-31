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
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\QueueHelper;


class ErrandAPI extends HelperBase
{
	/**
	 * 通用抢单接口
	 *
	 * @param $user_id
	 * @param $provider_id
	 *
	 * @return bool
	 */
	public static function robbingV10($user_id, $provider_id)
	{

		$shopInfo                    = ShopHelper::getShopInfoByUserId($user_id); //获取是商家信息
		$params['order_no']          = SecurityHelper::getBodyParam('order_no'); //订单号
		$params['provider_id']       = $provider_id;
		$params['provider_location'] = SecurityHelper::getBodyParam('provider_location'); //百度转高德地图
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = isset($shopInfo['utel']) ? $shopInfo['utel'] : null;
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');

		return ErrandHelper::saveRobbing($params);
	}

	/**
	 * 通用小帮删除订单
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerDeleteV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		return ErrandHelper::workerDelete($params);
	}


	/**
	 * 通用小帮申请取消订单
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerCancelV10($provider_id)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		$data = ErrandHelper::workerCancel($params);
		if ($data) {
			self::_workerCancelUserNotice($data);
		}

		return $data;
	}

	/**
	 * 通用小帮取消订单流程
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerCancelFlowV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		$params['agreed']      = SecurityHelper::getBodyParam('agreed');
		$data                  = ErrandHelper::workerCancelFlow($params);
		if ($data) {
			self::_workerCancelUserNotice($data);
			if ($params['agreed'] == 'yes') {    //进入退款流程
				QueueHelper::preRefund($data);
			}
		}

		return $data;
	}

	/**
	 * 小帮工作流1.0
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerProgressV10($provider_id)
	{
		$result                     = false;
		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['errand_status']    = SecurityHelper::getBodyParam('errand_status');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam('current_location'));

		$params['provider_id'] = $provider_id;
		$data                  = ErrandHelper::workerProgress($params);
		if ($data) {

			if ($params['errand_status'] == Ref::ERRAND_STATUS_FINISH) {
				$ttl = YII_ENV_DEV ? 60 * 5 : Ref::TTL_AUTO_CONFIRM_ERRAND_ORDER;    //测试环境5分钟自动确认收货，正式环境24小时
				QueueHelper::autoConfirmErrandOrder($data, $ttl);
			}

			self::_workerProgressUserNotice($data);
			$result = $data;
		}

		return $result;
	}


	/**
	 * 小帮取消通知发给用户
	 *
	 * @param $data
	 */
	private static function _workerProgressUserNotice($data)
	{
		if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY)
			ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_DO)
			ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_SEND)
			ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandSendHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);
	}


	/**
	 * 小帮取消通知发给用户
	 *
	 * @param $data
	 */
	private static function _workerCancelUserNotice($data)
	{
		if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY)
			ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_DO)
			ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_SEND)
			ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandSendHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);
	}
}