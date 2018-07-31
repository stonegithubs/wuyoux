<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/3
 */

namespace api\modules\v2\controllers;

use api\modules\v2\helpers\OpenStateCode;
use Yii;
use common\helpers\users\UserHelper;

class UserController extends ControllerAccess
{

	//获取用户信息
	public function actionGet()
	{
		$data = UserHelper::getUserInfo($this->user_id, 'mobile, money as balance');
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(OpenStateCode::USER_NOT_EXIST);
		}

		return $this->response();
	}
}