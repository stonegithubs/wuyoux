<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api\modules\v1\api;

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
			$result = WorkerOrderHelper::getOrderSeaV10($shopInfo);
		}

		return $result;
	}
}