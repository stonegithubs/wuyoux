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
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;

/**
 * 帮我办API版本控制
 * Class ErrandBuyAPI
 * @package api_worker\modules\v1\api
 */
class ErrandDoAPI extends HelperBase
{


	//小帮任务页
	//小帮详情页

	/**
	 * 小帮任务页获取任务数据1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerTaskV10($provider_id,$app_data)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		$params['app_data']    = $app_data;

		return ErrandDoHelper::workerTask($params);
	}

	/**
	 * 小帮详情页1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerDetailV10($provider_id, $app_data)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		$params['app_data']    = $app_data;

		return ErrandDoHelper::workerDetail($params);
	}
}