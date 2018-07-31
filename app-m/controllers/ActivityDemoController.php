<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/3/1
 * Time: 9:42
 */

namespace m\controllers;

use common\components\ControllerBase;

class ActivityDemoController extends ControllerBase
{

	/**
	 * 小帮货车未登录
	 * @return string
	 */
	public function actionTruck()
	{
		return $this->render("truck");
	}
}