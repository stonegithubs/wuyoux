<?php

namespace api\modules\v2\controllers;

use api\modules\v2\helpers\OpenAuthHelper;
use api\modules\v2\helpers\OpenStateCode;
use common\components\ControllerAPI;
use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * Default controller for the `v2` module
 */
class ControllerAccess extends ControllerAPI
{
	public $user_id;        //用户ID

	public function _init()
	{
		parent::_init(); // TODO: Change the autogenerated stub
	}

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		$access_token               = Yii::$app->request->getBodyParam("access_token");
		if (parent::beforeAction($action)) {

			$userId = OpenAuthHelper::getToken($access_token);
			if ($userId) {

				$this->user_id = $userId;
			} else {
				throw new UnauthorizedHttpException('You are requesting with an invalid credential.', 20000);
			}

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
		$this->_message = OpenStateCode::get($code);
	}
}
