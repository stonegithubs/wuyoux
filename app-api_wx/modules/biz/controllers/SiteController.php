<?php

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\helpers\StateCode;
use api_wx\modules\biz\helpers\WxBizSendHelper;
use common\components\ControllerAPI;
use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\BizHelper;
use common\helpers\users\UserHelper;
use api_wx\modules\biz\helpers\BizUserHelper;
use common\helpers\wechat\MiniAppOauth;

/**
 * Default controller for the `biz` module
 */
class SiteController extends ControllerAPI
{
	/**
	 * 注册验证码
	 * @return array
	 */
	public function actionSignUpCode()
	{
		$params['mobile'] = SecurityHelper::getBodyParam('mobile');
		if (empty($params['mobile'])) {

			return $this->response();
		}
		if (UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::OTHER_MOBILE_EXIST;
			$this->_message = StateCode::get(StateCode::OTHER_MOBILE_EXIST);

			return $this->response();
		}

		$data = SmsHelper::sendSignUpCode($params['mobile']);
		if ($data) {

		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "获取验证码失败";
		}

		return $this->response();
	}

	/**
	 * 找回密码验证码
	 * @return array
	 */
	public function actionFindPasswordCode()
	{
		$params['mobile'] = SecurityHelper::getBodyParam('mobile');
		if (!UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::OTHER_MOBILE_NO_EXIST;
			$this->_message = StateCode::get(StateCode::OTHER_MOBILE_NO_EXIST);

			return $this->response();
		}
		//TODO 校验页面来源
		$data = SmsHelper::sendFindPasswordCode($params['mobile']);
		if ($data) {

		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "获取验证码失败";
		}

		return $this->response();
	}

	/**
	 * 找回密码
	 * @return array
	 */
	public function actionFindPassword()
	{
		$params['mobile']       = SecurityHelper::getBodyParam('mobile');
		$params['code']         = SecurityHelper::getBodyParam('code');
		$params['new_password'] = SecurityHelper::getBodyParam('new_password');

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_FIND_PASSWORD)) {
			$this->_code    = StateCode::OTHER_SMS_INCORRECT_CODE;
			$this->_message = "输入的验证码错误";

			return $this->response();
		}

		if (UserHelper::checkPassword($params)) {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = StateCode::get(StateCode::OTHER_PWD_SAME);

			return $this->response();
		}

		$data = UserHelper::findPassword($params);
		if ($data) {

		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "修改登陆密码失败";
		}

		return $this->response();
	}

	/**
	 * 验证手机是否存在
	 * @return array
	 */
	public function actionCheckMobile()
	{
		$mobile = SecurityHelper::getBodyParam('mobile');
		$data   = UserHelper::checkMobileExist($mobile);
		if ($data) {
			$result['exist'] = 1;
		} else {
			$result['exist'] = 0;
			$this->_code     = StateCode::OTHER_MOBILE_NO_EXIST;
			$this->_message  = StateCode::get(StateCode::OTHER_MOBILE_NO_EXIST);
		}
		$this->_data = $result;

		return $this->response();
	}

	/**
	 * 小程序注册并登录
	 * @return array
	 */
	public function actionSignUp()
	{
		$access_token            = SecurityHelper::getBodyParam('access_token');
		$params['mobile']        = SecurityHelper::getBodyParam('mobile');
		$params['password']      = SecurityHelper::getBodyParam('password');
		$params['code']          = SecurityHelper::getBodyParam('code');
		$params['user_location'] = SecurityHelper::getBodyParam('user_location');
		$params['city_name']     = SecurityHelper::getBodyParam('city_name');
		$params['register_src']  = 3;    //注册来源：0未知 1苹果 2安卓 3微信 4虚拟用户

		if (UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::OTHER_MOBILE_EXIST;
			$this->_message = StateCode::get(StateCode::OTHER_MOBILE_EXIST);

			return $this->response();
		}

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_LOGIN_PASSWORD)) {
			$this->_code    = StateCode::OTHER_SMS_INCORRECT_CODE;
			$this->_message = StateCode::get(StateCode::OTHER_SMS_INCORRECT_CODE);

			return $this->response();
		}

		$result = BizUserHelper::BizSignUp($params);

		if ($result) {
			//登录
			$userInfo = BizUserHelper::verifyBizUserLoginInfo($params);
			if ($userInfo) {

				$saveRes = MiniAppOauth::setUserId($access_token, $userInfo['uid'], Ref::MINI_TYPE_BIZ);
				if ($saveRes) {
					$data['nickname']     = $userInfo['nickname'];           //昵称
					$data['mobile']       = $userInfo['mobile'];        //登录手机
					$data['avatar']       = ImageHelper::getUserPhoto($userInfo['userphoto']);     //头像
					$data['access_token'] = $saveRes['access_token'];
					$this->_data          = $data;
				} else {
					$this->_code    = StateCode::SET_USERID_INCORRECT;
					$this->_message = '关联账号失败,请重试';
				}
			}
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "注册失败,请重新填写";
		}

		return $this->response();
	}

	/**
	 * 小程序获取(登录/注册)短信验证码
	 * @return array
	 */
	public function actionGetCode()
	{
		//TODO 超过一定的次数要求前端展示图形验证码
		//TODO 根据不同类型 注册， 登录 都要区分
		$params['mobile'] = SecurityHelper::getBodyParam('mobile');

		$countRes = BizUserHelper::checkMobileCount($params['mobile']);
		if ($countRes) {
			$this->_code    = StateCode::OVER_CODE_MAX_NUM;
			$this->_message = "验证码超过今天最大次数";

			return $this->response();
		}

		$data['has_account'] = 0;    //注册
		if (UserHelper::checkMobileExist($params['mobile'])) {
			$data['has_account'] = 1;    //登录
		}
		$res = SmsHelper::sendLoginCode($params['mobile']);
		if (!$res) {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "获取验证码失败";
		}

		$this->_data = $data;

		return $this->response();
	}

	/**小程序登录
	 * @return array
	 */
	public function actionLogin()
	{
		$params['access_token'] = SecurityHelper::getBodyParam('access_token');
		$params['mobile']       = SecurityHelper::getBodyParam('mobile');
		$params['code']         = SecurityHelper::getBodyParam('code');

		if (!UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::OTHER_MOBILE_NO_EXIST;
			$this->_message = StateCode::get(StateCode::OTHER_MOBILE_NO_EXIST);

			return $this->response();
		}

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_LOGIN_PASSWORD)) {
			$this->_code    = StateCode::OTHER_SMS_INCORRECT_CODE;
			$this->_message = StateCode::get(StateCode::OTHER_SMS_INCORRECT_CODE);

			return $this->response();
		}

		$userInfo = BizUserHelper::verifyBizUserLoginInfo($params);
		if ($userInfo) {

			$saveRes = MiniAppOauth::setUserId($params['access_token'], $userInfo['uid'], Ref::MINI_TYPE_BIZ);
			if (!$saveRes) {
				$this->_code    = StateCode::SET_USERID_INCORRECT;
				$this->_message = '关联账号失败,请重试';

				return $this->response();
			}

			$data['nickname']     = $userInfo['nickname'];           //昵称
			$data['mobile']       = $userInfo['mobile'];        //登录手机
			$data['avatar']       = ImageHelper::getUserPhoto($userInfo['userphoto']);     //头像
			$data['access_token'] = $saveRes['access_token'];

			$data['biz_status'] = 2;//0待审核;1是企业送;2:不是企业送（包括审核失败的）
			$bizStatusRes       = BizHelper::getBizStatus($userInfo['uid']);
			$bizStatusRes ? $data['biz_status'] = $bizStatusRes['status'] : null;

			$this->_data = $data;
		}

		return $this->response();
	}


	/**
	 * 小程序启动 app launch
	 * @return array
	 */
	public function actionLaunch()
	{
		$code               = SecurityHelper::getBodyParam('js_code');
		$data               = MiniAppOauth::getBizMiniOauth($code);
		$data['biz_status'] = 3;        //0待审核;1是企业送;2:不是企业送（包括审核失败的）
		$user_id            = $data['user_id'];
		if ($user_id > 0) {

			//TODO 封号的问题
			$userInfo         = UserHelper::getUserInfo($data['user_id'], 'nickname,mobile,userphoto');
			$data['nickname'] = $userInfo ? $userInfo['nickname'] : null;           //昵称
			$data['mobile']   = $userInfo ? $userInfo['mobile'] : null;        //登录手机
			$data['avatar']   = $userInfo ? ImageHelper::getUserPhoto($userInfo['userphoto']) : null;     //头像

			$bizStatusRes = WxBizSendHelper::getWxBizStatus($user_id);
			$bizStatusRes ? $data['biz_status'] = $bizStatusRes['status'] : null;
		}
		$this->_data = $data;

		return $this->response();
	}


	/**小程序退出登录
	 * @return array
	 */
	public function actionLogout()
	{
		$access_token = SecurityHelper::getBodyParam('access_token');
		$saveRes      = MiniAppOauth::setUserId($access_token, 0, Ref::MINI_TYPE_BIZ);
		if ($saveRes) {
			$data['access_token'] = $saveRes['access_token'];
			$this->_data          = $data;
		}

		return $this->response();
	}
}