<?php

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\api\SiteAPI;
use api_wx\modules\mpv1\helpers\StateCode;
use common\components\ControllerAPI;
use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\security\SecurityHelper;
use api_wx\modules\mpv1\helpers\WxUserHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\UserHelper;
use wechat\helpers\WxClientHelper;
use Yii;
use yii\web\User;

/**
 * Default controller for the `v1` module
 */
class SiteController extends ControllerAPI
{
	/**
	 * 发送短信验证码
	 * @return array
	 */
	public function actionSendCode()
	{
		$mobile         = SecurityHelper::getBodyParam('mobile');
		$type           = SecurityHelper::getBodyParam('type');    //验证码类型 1是注册类型，2是找回登录，3是支付密码,4是快捷登录
		$data['status'] = 1; //1是成功，2是失败，3是太频繁，4是图形验证码错误，5账号不存在 6账号已经存在

		if ($type == 1) {

			if (UserHelper::checkMobileExist($mobile)) {
				$data['status'] = 6;
				$this->_data    = $data;
				$this->_message = '账号已经存在';

				return $this->response();
			}

		} else {//不是新用户注册要检查号码是否存在
			if (!UserHelper::checkMobileExist($mobile)) {
				$data['status'] = 5;
				$this->_data    = $data;
				$this->_message = '账号不存在';

				return $this->response();
			}
		}
		$num = SecurityHelper::getCodeFreq($mobile);

		//4次的整数倍时需要图形验证码，校验之后才能继续获取短信验证码
		if ($num > 0 && (fmod($num, 4) == 0)) {
			$graphCode  = SecurityHelper::getBodyParam('graph_code');
			$confirmRes = SecurityHelper::confirmGraph($mobile, $type, $graphCode);
			if ($confirmRes > 0) {
				$data['status'] = $confirmRes == 1 ? 3 : 4;
				$this->_data    = $data;
				$this->_message = $confirmRes == 1 ? '获取太频繁' : '图形验证码错误';

				return $this->response();
			}
		}

		$res = SmsHelper::sendCode($mobile, $type);
		if ($res) {
			$this->_data    = $data;
			$this->_message = '获取验证码成功';
		} else {
			$data['status'] = 2;
			$this->_data    = $data;
			$this->_message = "获取验证码失败";
		}

		return $this->response();
	}

	/**
	 * 获取图形验证码
	 * @return array
	 */
	public function actionGetAuthCode()
	{
		$mobile   = SecurityHelper::getBodyParam('mobile');
		$type     = SecurityHelper::getBodyParam('type');    //验证码类型
		$imageUrl = SecurityHelper::getGraphCode($mobile, $type);
		if ($imageUrl) {
			$this->_data    = $imageUrl;
			$this->_message = '获取成功';
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = '获取验证码过于频繁！';
		}

		return $this->response();
	}


	//注册
	public function actionSignUp()
	{
		$params['openid']        = SecurityHelper::getBodyParam('openid');
		$params['nickname']      = SecurityHelper::getBodyParam('nickname');
		$params['mobile']        = SecurityHelper::getBodyParam('mobile');
		$params['password']      = SecurityHelper::getBodyParam('password');
		$params['code']          = SecurityHelper::getBodyParam('code');
		$params['invite_mobile'] = SecurityHelper::getBodyParam('invite_code');
		$params['user_location'] = SecurityHelper::getBodyParam('user_location');
		$params['register_src']  = Ref::ORDER_FROM_WECHAT;

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->_code    = StateCode::OTHER_SMS_INCORRECT_CODE;
			$this->_message = '验证码错误或失效';

			return $this->response();
		}
		$res = UserHelper::checkMobileExist($params['mobile']);
		if ($res) {
			$this->_code    = StateCode::OTHER_MOBILE_EXIST;
			$this->_message = "用户已存在";

			return $this->response();
		}

		if ($params['invite_mobile']) {
			$res = UserHelper::checkMobileExist($params['invite_mobile']);
			if (!$res) {
				$this->_code    = StateCode::OTHER_MOBILE_NO_EXIST;
				$this->_message = "该邀请人号码不存在";

				return $this->response();
			} else {
				$params['invite_id'] = $res['uid'];
			}
		}

		$data = UserHelper::signUp($params);
		if ($data) {
			$conditions['mobile']     = $params['mobile'];
			$userInfo                 = UserHelper::selectUserInfo($conditions, ['uid', 'nickname', 'mobile', 'userphoto as avatar', 'money as balance']);
			$userInfo['avatar']       = ImageHelper::getUserPhoto($userInfo['avatar']);     //头像
			$userInfo['access_token'] = WxClientHelper::wxLogin($userInfo['uid'], $params['openid']);
			$this->_data              = $userInfo;

			$this->_message = "注册成功";
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "注册失败,请重新填写";
		}

		return $this->response();

	}


	//找回密码
	public function actionFindPassword()
	{
		$params['mobile']       = SecurityHelper::getBodyParam('mobile');
		$params['code']         = SecurityHelper::getBodyParam('code');
		$params['new_password'] = SecurityHelper::getBodyParam('new_password');

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_LOGIN_PASSWORD)) {
			$this->_code    = StateCode::OTHER_SMS_INCORRECT_CODE;
			$this->_message = "验证码错误或失效";

			return $this->response();
		}

		if (UserHelper::checkPassword($params)) {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = StateCode::get(StateCode::OTHER_PWD_SAME);

			return $this->response();
		}

		$data = UserHelper::findPassword($params);
		if ($data) {
			$this->_message = '密码修改成功';
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "修改登陆密码失败";
		}

		return $this->response();
	}


	//登录
	public function actionLogin()
	{
		$params = [
			'openid'   => SecurityHelper::getBodyParam('openid'),
			'unionid'  => SecurityHelper::getBodyParam('unionid'),
			'mobile'   => SecurityHelper::getBodyParam('mobile'),
			'password' => SecurityHelper::getBodyParam('password'),
			'code'     => SecurityHelper::getBodyParam('code'),    //password和code分别表示密码和验证码,只需要传其中一个就好
			'type'     => SecurityHelper::getBodyParam('type', 0), //type=0是密码登录,type=1是验证码登录
		];
		$user   = UserHelper::checkMobileExist($params['mobile']); //先检查账号是否存在
		if (!$user) {
			$this->_code    = StateCode::OTHER_MOBILE_NO_EXIST;
			$this->_message = StateCode::get(StateCode::OTHER_MOBILE_NO_EXIST);

			return $this->response();
		}

		//密码登录方式
		if ($params['type'] == 0) {
			if (SecurityHelper::encryptPassword($params['password']) != $user['password']) {
				$this->_code    = StateCode::OTHER_PWD_INCORRECT;
				$this->_message = StateCode::get(StateCode::OTHER_PWD_INCORRECT);

				return $this->response();
			}
		}
		//验证码登录方式
		if ($params['type'] == 1) {
			$res = SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_LOGIN_PASSWORD);
			if (!$res) {
				$this->_code    = StateCode::OTHER_SMS_INCORRECT_CODE;
				$this->_message = '验证码错误或失效';

				return $this->response();
			}
		}

		$accessToken              = WxClientHelper::wxLogin($user['uid'], $params['openid'], $params['unionid']);
		$userInfo                 = UserHelper::getUserInfo($user['uid'], ['uid', 'nickname', 'mobile', 'userphoto as avatar', 'money as balance']);
		$userInfo['avatar']       = ImageHelper::getUserPhoto($userInfo['avatar']);     //头像
		$userInfo['access_token'] = $accessToken;

		$this->_data = $userInfo;

		return $this->response();
	}

	//退出登录
	public function actionLogout()
	{
		$accessToken = SecurityHelper::getBodyParam('access_token');
		WxClientHelper::deleteAccessToken($accessToken);

		return $this->response();
	}

	//验证手机是否存在
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
}
