<?php

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\helpers\StateCode;
use common\components\ControllerAPI;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use wechat\helpers\WxClientHelper;
use Yii;
use yii\db\Query;
use yii\web\UnauthorizedHttpException;

/**
 * Default controller for the `v1` module
 */
class ControllerAccess extends ControllerAPI
{

	public $user_id;

	public function _init()
	{
		parent::_init();
	}

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		if (parent::beforeAction($action)) {

			$access_token = Yii::$app->request->getBodyParam("access_token");
			$userId = WxClientHelper::checkToken($access_token);

			if ($userId) {
				$this->user_id = $userId;

				return true;
			} else {

				throw new UnauthorizedHttpException('You are requesting with an invalid credential.', 20000);
			}
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
