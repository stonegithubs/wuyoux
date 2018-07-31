<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/17
 */

namespace api\modules\api\controllers;

use api\modules\api\blocks\PushBlock;
use common\helpers\security\SecurityHelper;

class MapController extends ControllerAccess
{

	public function actionFindNearbyProvider()
	{
		$center_location = SecurityHelper::getBodyParam("center_location");
		$cate_id         = SecurityHelper::getBodyParam("cate_id");
		$result          = PushBlock::nearbyShop($center_location, $cate_id);

		if (!$result) {
			$this->_code    = -200;
			$this->_message = "参数有误！";

		} else {
			$this->_data = $result;
		}

		return $this->response();
	}
}