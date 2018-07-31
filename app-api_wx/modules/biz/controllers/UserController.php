<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\helpers\WxUserHelper;
/**
 * 用户控制器
 * Class UserController
 * @package api_wx\modules\biz\controllers
 */
class UserController extends ControllerAccess
{
	/**
	 * 首页
	 * @return array
	 */
	public function actionUserInfo()
	{
		$data = WxUserHelper::getUserInfo($this->user_id);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}

}