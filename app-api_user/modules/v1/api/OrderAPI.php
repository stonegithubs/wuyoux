<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_user\modules\v1\api;

use api_user\modules\v1\helpers\UserOrderHelper;
use common\helpers\HelperBase;
use common\helpers\orders\OrderHelper;
use common\helpers\security\SecurityHelper;

class OrderAPI extends HelperBase
{

	//用户订单列表
	//1.0版本
	public static function userOrderListV10($user_id)
	{
		$params = [
			'user_id'   => $user_id,
			'status'    => SecurityHelper::getBodyParam('status'),
			'page'      => SecurityHelper::getBodyParam('page'),
			'page_size' => SecurityHelper::getBodyParam('page_size'),
		];

		return UserOrderHelper::getOrderList($params);
	}

	//用户订单列表
	//1.1版本
	//增加了小帮出行
	public static function userOrderListV11($user_id)
	{
		$params = [
			'user_id'   => $user_id,
			'status'    => SecurityHelper::getBodyParam('status'),
			'page'      => SecurityHelper::getBodyParam('page'),
			'page_size' => SecurityHelper::getBodyParam('page_size'),
		];

		return UserOrderHelper::orderList($params);
	}


	//下单地址历史记录
	public static function HistoryAddressV10($user_id)
	{
		$result['list'] = OrderHelper::getHistoryAddress($user_id);

		return $result;
	}
}