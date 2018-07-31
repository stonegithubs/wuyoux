<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/11/17
 */

namespace common\traits;

//API通用Site功能
use api_user\modules\v1\api\SecurityAPI;        //这里使用用户端的securityAPI
use api_user\modules\v1\helpers\StateCode;    //这里使用用户端的securityAPI
use common\helpers\activity\ActivityHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\components\Ref;
use common\helpers\utils\UtilsHelper;

trait APISiteTrait
{
	/**
	 * 找回密码获取验证码
	 * @return mixed
	 */
	public function actionFindPasswordCode()
	{
		$params['mobile'] = SecurityHelper::getBodyParam('mobile');
		if (!UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::SMS_INCORRECT_MOBILE;
			$this->_message = "没有该用户";

			return $this->response();
		}
		$data = SecurityAPI::findPasswordCodeV10($params['mobile']);
		if ($data) {

		} else {
			$this->_code    = StateCode::SMS_GET_CODE_FAILED;
			$this->_message = "获取验证码失败";
		}

		return $this->response();
	}

	/**
	 * 找回密码
	 * @return mixed
	 */
	public function actionFindPassword()
	{
		$params['mobile']       = SecurityHelper::getBodyParam('mobile');
		$params['code']         = SecurityHelper::getBodyParam('code');
		$params['new_password'] = SecurityHelper::getBodyParam('new_password');

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_FIND_PASSWORD)) {
			$this->_code    = StateCode::SMS_INCORRECT_CODE;
			$this->_message = "输入的验证码错误";

			return $this->response();
		}

		if (UserHelper::checkPassword($params)) {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "输入的新密码不能与旧密码相同";

			return $this->response();
		}

		$data = SecurityAPI::findPasswordV10($params);
		if ($data) {

		} else {
			$this->_code    = StateCode::PWD_CHANGE_FAILED;
			$this->_message = "修改登陆密码失败";
		}

		return $this->response();
	}

	/**
	 * 注册
	 * @return mixed
	 */
	public function actionSignUp()
	{
		$params['mobile']        = SecurityHelper::getBodyParam('mobile');
		$params['password']      = SecurityHelper::getBodyParam('password');
		$params['code']          = SecurityHelper::getBodyParam('code');
		$params['invite_mobile'] = SecurityHelper::getBodyParam('invite_code');
		$params['user_location'] = SecurityHelper::getBodyParam('user_location');
		$params['register_src']  = SecurityHelper::getBodyParam('register_src');
		$params['city_name']     = SecurityHelper::getBodyParam('city_name');

		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->_code    = StateCode::SMS_INCORRECT_CODE;
			$this->_message = "输入的验证码错误";

			return $this->response();
		}

		if (UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::OTHER_MOBILE_EXIST;
			$this->_message = "已经存在该用户了";

			return $this->response();
		}

		$invite_user_id = 0;
		if ($params['invite_mobile']) {
			$res = UserHelper::checkMobileExist($params['invite_mobile']);
			if (!$res) {
				$this->_code    = StateCode::SMS_INCORRECT_MOBILE;
				$this->_message = "推荐人手机不存在";

				return $this->response();
			}
			$invite_user_id = $res['uid'];
		}

		$userId = SecurityAPI::signUpV10($params);
		if ($userId) {

			$invite_user_id > 0 ? ActivityHelper::addUserMarketRelation($userId, $invite_user_id) : null;

		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "注册失败,请重新填写";
		}

		return $this->response();

	}

	/**
	 * 注册验证码
	 * @return mixed
	 */
	public function actionSignUpCode()
	{
		$params['mobile'] = SecurityHelper::getBodyParam('mobile');
		if (UserHelper::checkMobileExist($params['mobile'])) {
			$this->_code    = StateCode::SMS_INCORRECT_MOBILE;
			$this->_message = "已经存在该用户了";

			return $this->response();
		}

		$data = SecurityAPI::signUpCode($params['mobile']);
		if ($data) {

		} else {
			$this->_code    = StateCode::SMS_GET_CODE_FAILED;
			$this->_message = "获取验证码失败";
		}

		return $this->response();
	}

	/**
	 * 检查版本
	 * @return mixed
	 */
	public function actionCheckVersion()
	{
		$params['version']  = SecurityHelper::getBodyParam('version');
		$params['app_type'] = SecurityHelper::getBodyParam('app_type');//1苹果用户;2安卓用户;3苹果小帮;4安卓小帮

		$data = UtilsHelper::checkVersion($params);
		if (count($data) > 0) {
			$this->_data = $data;
		} else {
			$this->_code    = StateCode::CHECK_UPDATE_FAILED;
			$this->_message = "暂无更新信息";
		}

		return $this->response();
	}
}