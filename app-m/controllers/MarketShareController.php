<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/7
 */

namespace m\controllers;

use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\UrlHelper;
use m\helpers\MarketHelper;
use m\helpers\MarketShareHelper;
use m\helpers\StateCode;
use Yii;

class MarketShareController extends ControllerWeb
{
	/**
	 * 注册
	 * @return string
	 */
	public function actionRegister()
	{
		$entrance = Yii::$app->request->get('entrance');
		//营销号不能为空
		$market_code = Yii::$app->request->get('market_code');
		if (!$market_code) {
			return $this->actionError();
		}

		$introduce = Yii::$app->request->get('introduce');
		if ($introduce == 1) {
			return $this->actionintroduce();
		}

		//营销数据不能为空
		$userMaket = MarketShareHelper::getUserMarket($market_code, 'user_id');
		if (!$userMaket) {
			return $this->actionError();
		}
		//获取用户数据
		$userInfo       = UserHelper::getUserInfo($userMaket['user_id']);
		$result['data'] = [
			'invite_name'   => $userInfo['nickname'],
			'invite_mobile' => $userInfo['mobile'],
			'entrance'      => $entrance
		];

		Yii::$app->session->set("entrance", $entrance);

		return $this->renderPartial('register', $result);
	}

	/**
	 * 营销介绍页
	 * @return string
	 */
	public function actionIntroduce()
	{
		$market_code    = Yii::$app->request->get('market_code');
		$entrance       = Yii::$app->request->get('entrance', 1);
		$result['data'] = [
			'market_code' => $market_code,
			'entrance'    => $entrance,
		];

		return $this->renderPartial('introduce', $result);
	}

	/**
	 * 分享
	 * @return string
	 */
	public function actionShare()
	{
		$market_code = Yii::$app->request->get('market_code');
		$entrance    = Yii::$app->request->get('entrance');
		if (!$market_code) {
			return $this->actionError();
		}

		//是否为微信打开
		$result = Yii::$app->mp_wechat->getIsWechat();
		if ($result) {
			$wechat_share_url = UrlHelper::wxLink('market-share/wechat-share') . '?market_code=' . $market_code . '&entrance=' . $entrance;

			return $this->redirect($wechat_share_url);
		}

		$share_content  = "Hi~朋友！无忧帮帮“全民合伙人”计划隆重上线啦！零投入、零风险、让你实现月入过万！加入并成为无忧帮帮的“合伙人”，只要您成功推荐新用户注册，即可获得无上限的订单丰厚抽佣分成，就能躺着赚钱。还等什么？赶快加入吧！！详情";
		$result['data'] = [
			'entrance'      => $entrance,
			'web_share_url' => $share_content . UrlHelper::webLink('market-share/register') . '?market_code=' . $market_code . '&introduce=1'
		];

		return $this->renderPartial('web-share', $result);
	}

	/**
	 * 开放分享
	 * @return string|\yii\web\Response
	 */
	public function actionOpenShare()
	{
		$entrance = Yii::$app->request->get('entrance');

		//是否为微信打开
		$result = Yii::$app->mp_wechat->getIsWechat();
		if ($result) {
			$wechat_share_url = UrlHelper::wxLink('market-share/wechat-open-share') . '?entrance=' . $entrance;

			return $this->redirect($wechat_share_url);
		}

		$share_content  = "Hi~朋友！无忧帮帮“全民合伙人”计划隆重上线啦！零投入、零风险、让你实现月入过万！加入并成为无忧帮帮的“合伙人”，只要您成功推荐新用户注册，即可获得无上限的订单丰厚抽佣分成，就能躺着赚钱。还等什么？赶快加入吧！！详情";
		$result['data'] = [
			'entrance'      => $entrance,
			'web_share_url' => $share_content . UrlHelper::webLink('market-share/open-introduce')
		];

		return $this->renderPartial('open/web-share', $result);
	}


	/**
	 * 404页面
	 * @return string
	 */
	public function actionError()
	{
		return $this->renderPartial('error');
	}


	/**
	 * 开放的活动介绍
	 */
	public function actionOpenIntroduce()
	{
		return $this->renderPartial("open/introduce");
	}

	/**
	 * 开放的活动注册
	 */
	public function actionOpenRegister()
	{
		return $this->renderPartial("open/register");
	}

	/* -----------------------------分享注册AJAX------------------------------ */

	/**
	 * 获取用户注册验证码API
	 */
	public function actionAjaxGetUserCode()
	{
		$mobile = Yii::$app->request->get('mobile');

		//账号不能为空
		if (!$mobile) {
			$this->setCodeMessage(StateCode::GET_CODE_FAIL);

			return $this->response();
		}

		//账号是否存在
		if (UserHelper::checkMobileExist($mobile)) {
			$this->setCodeMessage(StateCode::USER_EXISTENCE);

			return $this->response();
		}

		//4次的整数倍时需要图形验证码，校验之后才能继续获取短信验证码
		$num = SecurityHelper::getCodeFreq($mobile);
		if ($num > 0 && (fmod($num, 4) == 0)) {
			$graphCode  = Yii::$app->request->get('graph_code');
			$confirmRes = SecurityHelper::confirmGraph($mobile, Ref::SMS_CODE_REGISTER, $graphCode);
			if ($confirmRes > 0) {
				$this->_code = $confirmRes == 1 ? StateCode::GET_CODE_FREQUENTLY : StateCode::GRAPH_CODE_FAIL;
				$imageUrl    = SecurityHelper::getGraphCode($mobile, Ref::SMS_CODE_REGISTER);
				$this->_data = $imageUrl;
				$this->setCodeMessage($this->_code);

				return $this->response();
			}
		}

		//验证码是否获取成功
		$res = SmsHelper::sendCode($mobile, Ref::SMS_CODE_REGISTER);
		if ($res) {
			$this->_message = '获取验证码成功';
		} else {
			$this->setCodeMessage(StateCode::GET_CODE_FAIL);
		}

		return $this->response();
	}

	/**
	 * 获取小帮注册验证码API
	 */
	public function actionAjaxGetProviderCode()
	{
		$mobile = Yii::$app->request->get('mobile');

		//账号不能为空
		if (!$mobile) {
			$this->setCodeMessage(StateCode::GET_CODE_FAIL);

			return $this->response();
		}

		//是否注册小帮
		if (ShopHelper::shopDetailByMobile($mobile)) {
			$this->setCodeMessage(StateCode::PROVIDER_EXISTENCE);

			return $this->response();
		}

		//4次的整数倍时需要图形验证码，校验之后才能继续获取短信验证码
		$num = SecurityHelper::getCodeFreq($mobile);
		if ($num > 0 && (fmod($num, 4) == 0)) {
			$graphCode  = Yii::$app->request->get('graph_code');
			$confirmRes = SecurityHelper::confirmGraph($mobile, Ref::SMS_CODE_REGISTER, $graphCode);
			if ($confirmRes > 0) {
				$this->_code = $confirmRes == 1 ? StateCode::GET_CODE_FREQUENTLY : StateCode::GRAPH_CODE_FAIL;
				$imageUrl    = SecurityHelper::getGraphCode($mobile, Ref::SMS_CODE_REGISTER);
				$this->_data = $imageUrl;
				$this->setCodeMessage($this->_code);

				return $this->response();
			}
		}

		//验证码是否获取成功
		$res = SmsHelper::sendCode($mobile, Ref::SMS_CODE_REGISTER);
		if ($res) {
			//用户身份
			if (UserHelper::checkMobileExist($mobile)) {
				$data['is_user'] = 1;
			} else {
				$data['is_user'] = 0;
			}
			$this->_data    = $data;
			$this->_message = '获取验证码成功';
		} else {
			$this->setCodeMessage(StateCode::GET_CODE_FAIL);
		}

		return $this->response();
	}

	/**
	 * 获取图形验证码API
	 */
	public function actionAjaxGetAuthCode()
	{
		$mobile   = Yii::$app->request->get('mobile');
		$imageUrl = SecurityHelper::getGraphCode($mobile, Ref::SMS_CODE_REGISTER);
		if ($imageUrl) {
			$this->_data    = $imageUrl;
			$this->_message = '获取成功';
		} else {
			$this->setCodeMessage(StateCode::GET_CODE_FREQUENTLY);
		}

		return $this->response();
	}

	/**
	 * 用户注册API
	 */
	public function actionAjaxUserRegister()
	{
		$params = [
			'mobile'        => Yii::$app->request->get('mobile'),
			'password'      => Yii::$app->request->get('password'),
			'code'          => Yii::$app->request->get('code'),
			'longitude'     => Yii::$app->request->get('longitude'),
			'latitude'      => Yii::$app->request->get('latitude'),
			'invite_mobile' => Yii::$app->request->get('invite_mobile'),
			'register_src'  => Ref::ORDER_FROM_WECHAT,
		];

		//用户注册账号、密码不能为空
		if (!($params['mobile'] && $params['password'])) {
			$this->setCodeMessage(StateCode::REGISTER_FAIL);

			return $this->response();
		}

		//用户位置坐标
		if ($params['longitude'] && $params['latitude']) {
			$params['user_location'] = '[' . $params['longitude'] . ',' . $params['latitude'] . ']';
		} else {
			$params['user_location'] = null;
		}

		//验证码是否正确
		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->setCodeMessage(StateCode::SMS_INCORRECT_CODE);

			return $this->response();
		}

		//账号是否存在
		$user = UserHelper::checkMobileExist($params['mobile']);
		if ($user) {
			$this->setCodeMessage(StateCode::USER_EXISTENCE);

			return $this->response();
		}

		//邀请用户是否存在
		$user = UserHelper::checkMobileExist($params['invite_mobile']);
		if (!$user) {
			$this->setCodeMessage(StateCode::INVITE_MOBILE_NO_EXIST);

			return $this->response();
		}
		$params['invite_id'] = $user['uid'];
		$entrance            = Yii::$app->session->get("entrance");
		$transaction         = Yii::$app->db->beginTransaction();
		try {
			//用户注册
			$result = UserHelper::signUp($params);
			//添加用户营销关系
			$userInfo = UserHelper::selectUserInfo(['mobile' => $params['mobile']]);
			$result   &= ActivityHelper::addUserMarketRelation($userInfo['uid'], $params['invite_id'], $entrance);
			if ($result) {
				$userMarket     = MarketHelper::getUserMarket($userInfo['uid']);
				$data['data']   = [
					'market_code' => $userMarket['market_code']
				];
				$this->_data    = $data;
				$this->_message = '注册成功';
				$transaction->commit();
			} else {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);

				return $this->response();
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $this->response();
	}

	/**
	 * 小帮注册API
	 */
	public function actionAjaxProviderRegister()
	{
		$params = [
			'mobile'        => Yii::$app->request->get('mobile'),
			'password'      => Yii::$app->request->get('password'),
			'code'          => Yii::$app->request->get('code'),
			'longitude'     => Yii::$app->request->get('longitude'),
			'latitude'      => Yii::$app->request->get('latitude'),
			'invite_mobile' => Yii::$app->request->get('invite_mobile'),
			'register_src'  => Ref::ORDER_FROM_WECHAT,
		];

		//是否为用户
		$is_user = UserHelper::checkMobileExist($params['mobile']) ? 1 : 0;

		if ($is_user == 0) {
			//用户注册账号、密码不能为空
			if (!($params['mobile'] && $params['password'])) {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);

				return $this->response();
			}
		} else {
			//小帮注册账号不能为空
			if (!$params['mobile']) {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);

				return $this->response();
			}
		}

		//用户位置坐标
		if ($params['longitude'] && $params['latitude']) {
			$params['user_location'] = '[' . $params['longitude'] . ',' . $params['latitude'] . ']';
		} else {
			$params['user_location'] = null;
		}

		//验证码是否正确
		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->setCodeMessage(StateCode::SMS_INCORRECT_CODE);

			return $this->response();
		}

		//是否为小帮
		if (ShopHelper::shopDetailByMobile($params['mobile'])) {
			$this->setCodeMessage(StateCode::PROVIDER_EXISTENCE);

			return $this->response();
		}

		//邀请用户是否存在
		$user = UserHelper::checkMobileExist($params['invite_mobile']);
		if (!$user) {
			$this->setCodeMessage(StateCode::INVITE_MOBILE_NO_EXIST);

			return $this->response();
		}
		$params['invite_id'] = $user['uid'];

		$entrance = Yii::$app->session->get("entrance");

		$transaction = Yii::$app->db->beginTransaction();
		try {
			$result = true;
			//用户注册
			if ($is_user == 0) {
				$result &= UserHelper::signUp($params);
				//添加用户营销关系
				$userInfo = UserHelper::selectUserInfo(['mobile' => $params['mobile']]);
				$result   &= ActivityHelper::addUserMarketRelation($userInfo['uid'], $params['invite_id'], $entrance);
			} else {
				$userInfo = UserHelper::selectUserInfo(['mobile' => $params['mobile']]);
			}
			//小帮注册
			$result &= MarketShareHelper::enterShop($params['mobile'], $params['invite_mobile']);
			//添加小帮营销关系
			$shopInfo = ShopHelper::shopDetailByMobile($params['mobile']);
			$result   &= ActivityHelper::addProviderMarketRelation($userInfo['uid'], $shopInfo['id'], $params['invite_id'], $entrance);
			if ($result) {
				$userMarket     = MarketHelper::getUserMarket($userInfo['uid']);
				$data['data']   = [
					'market_code' => $userMarket['market_code']
				];
				$this->_data    = $data;
				$this->_message = '注册成功';
				$transaction->commit();
			} else {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);

				return $this->response();
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $this->response();
	}


	/**
	 * 用户开放注册API
	 */
	public function actionAjaxUserOpenRegister()
	{
		$params = [
			'mobile'        => Yii::$app->request->get('mobile'),
			'password'      => Yii::$app->request->get('password'),
			'code'          => Yii::$app->request->get('code'),
			'longitude'     => Yii::$app->request->get('longitude'),
			'latitude'      => Yii::$app->request->get('latitude'),
			'invite_mobile' => Yii::$app->request->get('invite_mobile'),
			'register_src'  => Ref::ORDER_FROM_WECHAT,
		];

		//用户注册账号、密码不能为空
		if (!($params['mobile'] && $params['password'])) {
			$this->setCodeMessage(StateCode::REGISTER_FAIL);

			return $this->response();
		}

		//用户位置坐标
		if ($params['longitude'] && $params['latitude']) {
			$params['user_location'] = '[' . $params['longitude'] . ',' . $params['latitude'] . ']';
		} else {
			$params['user_location'] = null;
		}

		//验证码是否正确
		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->setCodeMessage(StateCode::SMS_INCORRECT_CODE);

			return $this->response();
		}

		//账号是否存在
		$user = UserHelper::checkMobileExist($params['mobile']);
		if ($user) {
			$this->setCodeMessage(StateCode::USER_EXISTENCE);

			return $this->response();
		}

		//邀请用户是否存在
		if ($params['invite_mobile']) {
			$user = UserHelper::checkMobileExist($params['invite_mobile']);
			if (!$user) {
				$this->setCodeMessage(StateCode::INVITE_MOBILE_NO_EXIST);

				return $this->response();
			}
			$params['invite_id'] = $user['uid'];
		}

		$transaction = Yii::$app->db->beginTransaction();
		try {
			//用户注册
			$result = UserHelper::signUp($params);
			if ($params['invite_mobile']) {
				//添加用户营销关系
				$userInfo = UserHelper::selectUserInfo(['mobile' => $params['mobile']]);
				$result   &= ActivityHelper::addUserMarketRelation($userInfo['uid'], $params['invite_id'], 1);
			}

			if ($result) {
				$this->_message = '注册成功';
				$transaction->commit();
			} else {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $this->response();
	}

	/**
	 * 小帮开放注册API
	 */
	public function actionAjaxProviderOpenRegister()
	{
		$params = [
			'mobile'        => Yii::$app->request->get('mobile'),
			'password'      => Yii::$app->request->get('password'),
			'code'          => Yii::$app->request->get('code'),
			'longitude'     => Yii::$app->request->get('longitude'),
			'latitude'      => Yii::$app->request->get('latitude'),
			'invite_mobile' => Yii::$app->request->get('invite_mobile'),
			'register_src'  => Ref::ORDER_FROM_WECHAT,
		];

		//是否为用户
		$is_user = UserHelper::checkMobileExist($params['mobile']) ? 1 : 0;

		if ($is_user == 0) {
			//用户注册账号、密码不能为空
			if (!($params['mobile'] && $params['password'])) {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);

				return $this->response();
			}
		} else {
			//小帮注册账号不能为空
			if (!$params['mobile']) {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);

				return $this->response();
			}
		}

		//用户位置坐标
		if ($params['longitude'] && $params['latitude']) {
			$params['user_location'] = '[' . $params['longitude'] . ',' . $params['latitude'] . ']';
		} else {
			$params['user_location'] = null;
		}

		//验证码是否正确
		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->setCodeMessage(StateCode::SMS_INCORRECT_CODE);

			return $this->response();
		}

		//是否为小帮
		if (ShopHelper::shopDetailByMobile($params['mobile'])) {
			$this->setCodeMessage(StateCode::PROVIDER_EXISTENCE);

			return $this->response();
		}

		//邀请用户是否存在
		if ($params['invite_mobile']) {
			$user = UserHelper::checkMobileExist($params['invite_mobile']);
			if (!$user) {
				$this->setCodeMessage(StateCode::INVITE_MOBILE_NO_EXIST);

				return $this->response();
			}
			$params['invite_id'] = $user['uid'];
		}

		$transaction = Yii::$app->db->beginTransaction();
		try {
			$result = true;
			//用户注册
			if ($is_user == 0) {
				$result &= UserHelper::signUp($params);
			}
			//小帮注册
			$result &= MarketShareHelper::enterShop($params['mobile'], $params['invite_mobile']);

			if ($params['invite_mobile']) {
				//添加用户营销关系
				$userInfo = UserHelper::selectUserInfo(['mobile' => $params['mobile']]);
				$result   &= ActivityHelper::addUserMarketRelation($userInfo['uid'], $params['invite_id'], 2);

				//添加小帮营销关系
				$shopInfo = ShopHelper::shopDetailByMobile($params['mobile']);
				$result   &= ActivityHelper::addProviderMarketRelation($userInfo['uid'], $shopInfo['id'], $params['invite_id'], 2);
			}

			if ($result) {
				$this->_message = '注册成功';
				$transaction->commit();
			} else {
				$this->setCodeMessage(StateCode::REGISTER_FAIL);
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $this->response();
	}
}