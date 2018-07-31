<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_worker\modules\v1\api;

use common\helpers\HelperBase;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\security\SecurityHelper;

/**
 * 帮我送API版本控制
 * Class ErrandBuyAPI
 * @package api_worker\modules\v1\api
 */
class ErrandSendAPI extends HelperBase
{
	/**
	 * 小帮任务页获取任务数据1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerTaskV10($provider_id, $appData)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'task';
		$params['app_data']     = $appData;

		return ErrandSendHelper::workerTaskAndDetail($params);
	}

	/**
	 * 小帮详情页1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerDetailV10($provider_id, $appData)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'detail';
		$params['app_data']     = $appData;

		return ErrandSendHelper::workerTaskAndDetail($params);
	}

	/**
	 * 用户确认1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function takePhotoV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['image_id']    = SecurityHelper::getBodyParam('image_id');
		$params['provider_id'] = $provider_id;

		return ErrandSendHelper::takePhoto($params);
	}

}