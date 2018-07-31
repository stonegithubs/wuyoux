<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace api_user\modules\v1\controllers;


use api_user\modules\v1\api\SecurityAPI;
use api_user\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;

class SecurityController extends ControllerAccess
{
	/**找回支付密码
	 * @return array
	 */
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
		}

		return $this->response();
	}

	/**找回支付密码验证码
	 * @return array
	 */
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

		}

		return $this->response();
	}

	/**修改登陆密码
	 * @return array
	 */
	public function actionLoginPassword()
	{
		if ($this->api_version == '1.0') {
			$data = SecurityAPI::loginPasswordV10($this->user_id);
			if ($data) {

			} else {
				$this->setCodeMessage(StateCode::PWD_CHANGE_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 通用检查支付密码 临时verify_code
	 * @return array
	 */
	public function actionVerifyPayPassword()
	{
		if ($this->api_version == '1.0') {
			$data        = SecurityAPI::verifyPayPasswordV10($this->user_id);
			$this->_data = $data;
			if ($data['status']) {
				$this->_message = '验证成功';
			} else {
				$this->_message = '验证失败';
			}
		}

		return $this->response();
	}

	/**
	 * 通用检查验证码 【废弃】
	 * @return array
	 */
	public function actionCheckCode()
	{
		if ($this->api_version == '1.0') {
			$result = SecurityAPI::checkCodeV10();
			if ($result) {
				$this->_message = '验证成功';
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
				$this->_message = '验证码不正确';
			}
		}

		return $this->response();
	}


}