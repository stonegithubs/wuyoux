<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/3
 */

namespace api\modules\v2\controllers;

use api\modules\v2\helpers\OpenAuthHelper;
use api\modules\v2\helpers\OpenStateCode;
use common\components\ControllerAPI;
use common\helpers\security\SecurityHelper;
use Yii;

class AuthController extends ControllerAPI
{
	public function actionToken()
	{
		$appId     = SecurityHelper::getBodyParam("app_id");
		$appSecret = SecurityHelper::getBodyParam("app_secret");

		$userId = OpenAuthHelper::login($appId, $appSecret);
		if ($userId) {
			$this->_data = OpenAuthHelper::setTokenData($userId);
		} else {
			$this->_message = "无效信息";
			$this->_code    = OpenStateCode::USER_PROVIDER_LOGIN_FAILED;
		}

		return $this->response();

	}
}

