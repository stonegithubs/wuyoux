<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/8
 */

namespace m\controllers;

use common\helpers\utils\UrlHelper;

class EnterProviderController extends ControllerWeb
{
	/**
	 * 入驻首页
	 * @return string
	 */
	public function actionIndex()
	{
		$data['trip_apply_url']   = UrlHelper::webLink('enter-provider/trip');
		$data['errand_apply_url'] = UrlHelper::webLink('enter-provider/errand');

		return $this->renderPartial("index", $data);
	}

	/**
	 * 小帮出行入驻
	 * @return string
	 */
	public function actionTrip()
	{
		return $this->renderPartial("trip");
	}

	/**
	 * 小帮快送入驻
	 * @return string
	 */
	public function actionErrand()
	{
		return $this->renderPartial('errand');
	}
}