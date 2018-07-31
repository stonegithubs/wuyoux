<?php

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\helpers\StateCode;
use common\components\ControllerAPI;
use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\wechat\MiniAppOauth;
use Yii;
use yii\db\Query;
use yii\web\UnauthorizedHttpException;

/**
 * Default controller for the `biz` module
 */
class ControllerAccess extends ControllerAPI
{
	public $user_id;

	public $openid;        //openID

	public $unionid;    //unionID

	public $session_key;    //session_key

	public function _init()
	{
		parent::_init();
	}

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		$access_token               = Yii::$app->request->getBodyParam("access_token");
		if (parent::beforeAction($action)) {

			$userToken = MiniAppOauth::getMiniToken($access_token, Ref::MINI_TYPE_BIZ);
			if (!$userToken) {
				throw new UnauthorizedHttpException('You are requesting with an invalid credential.', 20000);
			}
			$this->user_id     = $userToken['user_id'];
			$this->openid      = $userToken['openid'];
			$this->unionid     = $userToken['unionid'];
			$this->session_key = $userToken['session_key'];

			return true;
		} else {

			throw new UnauthorizedHttpException('No access token.', 401);
		}
	}

	public function verbs()
	{
		return ['*' => ['POST']];
	}

	public function setCodeMessage($code)
	{
		$this->_code    = $code;
		$this->_message = StateCode::get($code);
	}
}
