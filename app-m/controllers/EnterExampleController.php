<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/11
 */

namespace m\controllers;

class EnterExampleController extends ControllerWeb
{
	/**
	 * 小帮快送入驻示例
	 * @return string
	 */
	public function actionErrand()
	{
		return $this->renderPartial('errand');
	}

	/**
	 * 小帮出行入驻示例
	 * @return string
	 */
	public function actionTripBike()
	{
		return $this->renderPartial('trip-bike');
	}
}