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
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\AMapHelper;
/**
 * 帮我买API版本控制
 * Class ErrandBuyAPI
 * @package api_worker\modules\v1\api
 */
class ErrandBuyAPI extends HelperBase
{

	//小帮任务页
	//小帮详情
	//小帮添加费用

	/**
	 * 小帮任务页获取任务数据1.0
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerTaskV10($provider_id, $appData)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'task';
		$params['app_data']     = $appData;

		return ErrandBuyHelper::workerTaskAndDetail($params);
	}

	/**
	 * 小帮详情页1.0
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerDetailV10($provider_id, $appData)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'detail';
		$params['app_data']     = $appData;

		return ErrandBuyHelper::workerTaskAndDetail($params);
	}

	/**
	 * 小帮添加配送费用
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function addExpenseV10($provider_id)
	{

		$result                = false;
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['fee']         = SecurityHelper::getBodyParam('fee');
		$params['provider_id'] = $provider_id;
		if (!ErrandBuyHelper::checkExpense($params['order_no'])) {
			return $result;
		}

		$data = ErrandBuyHelper::addExpense($params);
		if ($data) {
			ErrandBuyHelper::pushToUserNotice($params['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_EXPENSE, $data);
			$result = $data;
		}

		return $result;
	}

	/**
	 * 配送到达并线下付款记录付款信息
	 */
	public static function arriveAndPayV10($provider_id)
	{

		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['fee']              = SecurityHelper::getBodyParam('fee');
		$params['provider_id']      = $provider_id;
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam('current_location'));

		return ErrandBuyHelper::arriveAndPayFinish($params);
	}

}