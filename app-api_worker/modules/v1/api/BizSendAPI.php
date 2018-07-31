<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/16
 */

namespace api_worker\modules\v1\api;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\BizSendHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\AMapHelper;

class BizSendAPI extends HelperBase
{

	public static function robbingV10($user_id, $provider_id)
	{
		$shopInfo                    = ShopHelper::getShopInfoByUserId($user_id); //获取是商家信息
		$params['tmp_no']            = SecurityHelper::getBodyParam('tmp_no'); //订单号
		$params['provider_id']       = $provider_id;
		$params['provider_location'] = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam('provider_location')); //百度转高德地图
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = isset($shopInfo['utel']) ? $shopInfo['utel'] : null;
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');

		return BizSendHelper::saveTmpRobbing($params);
	}

	public static function tmpTaskV10($provider_id)
	{

		$params['tmp_no']      = SecurityHelper::getBodyParam('tmp_no');
		$params['provider_id'] = $provider_id;

		return BizSendHelper::tmpOrderDetail($params);
	}


	public static function workerDetailV10($provider_id)
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'detail';

		return BizSendHelper::workerTaskAndDetail($params);
	}


	public static function workerTaskV10($provider_id)
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'task';

		return BizSendHelper::workerTaskAndDetail($params);
	}

	//创建订单

	/**
	 * @param $provider_id
	 * @return bool
	 */
	public static function createOrderV10($provider_id)
	{
		$params['tmp_no']           = SecurityHelper::getBodyParam('tmp_no');
		$params['provider_id']      = $provider_id;
		$params['mobile_group']     = SecurityHelper::getBodyParam('mobile_group');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = SecurityHelper::getBodyParam('current_location');

		return BizSendHelper::createOrder($params);
	}

	//小帮删除订单
	public static function workerDeleteV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		return BizSendHelper::workerDelete($params);
	}

	public static function deliveryArrivalV10($provider_id)
	{

		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = SecurityHelper::getBodyParam('current_location');
		$params['provider_id']      = $provider_id;

		return BizSendHelper::workerDeliveryArrival($params);
	}

	/**
	 * @param $provider_id
	 */
	public static function workerCancelV10($provider_id)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		$data = BizSendHelper::workerCancel($params);
		if ($data) {
			//TODO 推送
//			self::_workerCancelUserNotice($data);
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

		return BizSendHelper::workerCancelFlow($params);
	}

	public static function tmpOrderCancelV10($provider_id)
	{
		$params['tmp_no']      = SecurityHelper::getBodyParam('tmp_no');
		$params['provider_id'] = $provider_id;
		$params['cancel_type'] = Ref::ERRAND_CANCEL_PROVIDER_APPLY;

		return BizSendHelper::workerTmpOrderCancel($params);
	}
}