<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_worker\modules\v1\api;

use api_worker\modules\v1\helpers\WorkerOrderHelper;
use common\helpers\HelperBase;
use common\helpers\orders\OrderHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;


class OrderAPI extends HelperBase
{
	public static function seaV10($provider_id)
	{

		$result   = false;
		$shopInfo = UserHelper::getShopInfo($provider_id);
		if ($shopInfo) {

			$result = 	WorkerOrderHelper::getOrderSeaV10($shopInfo);
		}

		return $result;
	}

	public static function seaV11($provider_id)
	{

		$result   = false;
		$shopInfo = UserHelper::getShopInfo($provider_id);
		if ($shopInfo) {

			$result = 	WorkerOrderHelper::getOrderSeaV11($shopInfo);
		}

		return $result;
	}

	//订单海1.2
	//增加了小帮出行
	public static function seaV12($provider_id)
	{

		$result   = false;
		$shopInfo = UserHelper::getShopInfo($provider_id);
		if ($shopInfo) {

			$result = 	WorkerOrderHelper::getOrderSeaV12($shopInfo);
		}

		return $result;
	}


	//小帮订单列表1.0
	//不包括小帮出行
	public static function providerListV10($provider_id)
	{
		$params = [
			'user_id'   => $provider_id,
			'status'    => SecurityHelper::getBodyParam('status',0),
			'page'      => SecurityHelper::getBodyParam('page'),
			'page_size' => SecurityHelper::getBodyParam('page_size'),
		];

		return WorkerOrderHelper::getProviderList($params);
	}

	//小帮订单列表1.1
	//包括小帮出行
	public static function providerListV11($provider_id)
	{
		$params = [
			'user_id'   => $provider_id,
			'status'    => SecurityHelper::getBodyParam('status',0),
			'page'      => SecurityHelper::getBodyParam('page'),
			'page_size' => SecurityHelper::getBodyParam('page_size'),
		];

		return WorkerOrderHelper::providerListV11($params);
	}
	/**
	 * 小帮订单列表1.2
	 * 包括小帮出行,企业送
	 * @param $provider_id
	 * @return array
	 */
	public static function providerListV12($provider_id)
	{
		$params = [
			'user_id'   => $provider_id,
			'status'    => SecurityHelper::getBodyParam('status',0),
			'page'      => SecurityHelper::getBodyParam('page'),
			'page_size' => SecurityHelper::getBodyParam('page_size'),
		];

		return WorkerOrderHelper::providerListV12($params);
	}
}