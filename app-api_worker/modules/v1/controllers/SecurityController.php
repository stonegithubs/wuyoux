<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace api_worker\modules\v1\controllers;


use api_worker\modules\v1\api\SecurityAPI;
use api_worker\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use Yii;

class SecurityController extends ControllerAccess
{
	public function actionPayPassword()
	{
		if ($this->api_version == '1.0') {
			$mobile = SecurityHelper::getBodyParam("mobile");
			$code   = SecurityHelper::getBodyParam('code');

			if (!UserHelper::checkUserMobile($mobile, $this->user_id)) {
				$this->setCodeMessage(StateCode::SMS_INCORRECT_MOBILE);

				return $this->response();
			}

			if (!SecurityHelper::checkCode($mobile, $code, Ref::SMS_CODE_PAY_PASSWORD)) {
				$this->setCodeMessage(StateCode::SMS_INCORRECT_CODE);

				return $this->response();
			}

			$data = SecurityAPI::payPasswordV10($this->user_id);
			if ($data) {

			} else {
				$this->setCodeMessage(StateCode::PWD_PAY_CHANGE_FAILED);
			}

			return $this->response();
		}
	}

	public function actionPayPasswordCode()
	{
		if ($this->api_version == '1.0') {
			$mobile = SecurityHelper::getBodyParam("mobile");

			if (!UserHelper::checkUserMobile($mobile, $this->user_id)) {
				$this->setCodeMessage(StateCode::SMS_INCORRECT_MOBILE);

				return $this->response();
			}
			$data = SecurityAPI::payPasswordCodeV10($mobile);
			if ($data) {

			} else {
				$this->setCodeMessage(StateCode::SMS_GET_CODE_FAILED);
			}

			return $this->response();
		}
	}

	public function actionLoginPassword()
	{
		if ($this->api_version == '1.0') {
			$data = SecurityAPI::loginPasswordV10($this->user_id);
			if ($data) {

			} else {
				$this->setCodeMessage(StateCode::PWD_CHANGE_FAILED);
			}

			return $this->response();
		}
	}

	public function actionChangeMobile()
	{
		if ($this->api_version == '1.0') {


		}
	}



}