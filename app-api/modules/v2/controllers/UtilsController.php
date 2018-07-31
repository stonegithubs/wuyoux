<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/3
 */

namespace api\modules\v2\controllers;

use api\modules\v2\helpers\PlatformHelper;
use common\helpers\security\SecurityHelper;

class UtilsController extends ControllerAccess
{

	//获取价格
	public function actionGetRange()
	{
		$params = [
			'user_id'        => $this->user_id,
			'start_location' => SecurityHelper::getBodyParam('start_location'),
			'end_location'   => SecurityHelper::getBodyParam('end_location'),
		];

		$this->_data = PlatformHelper::getRangePrice($params);

		return $this->response();
	}
}