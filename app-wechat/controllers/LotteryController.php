<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/20
 */

namespace wechat\controllers;

use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UrlHelper;
use common\models\users\UserWechatRel;
use wechat\helpers\LotteryHelper;
use wechat\helpers\StateCode;
use Yii;

class LotteryController extends ControllerAuthorize
{
	public $_status;  //状态码
	public $_msg;  //提示信息
	public $_data; //数据主体

	/**
	 * 设置状态码提示信息
	 * @param $status
	 */
	public function setCodeMessage($status)
	{
		$this->_status = $status;
		$this->_msg    = StateCode::get($status);
	}

	/**
	 * 返回json数据
	 */
	public function response()
	{
		$result           = [];
		$result['msg']    = $this->_msg ? $this->_msg : '';
		$result['data']   = $this->_data ? $this->_data : '';
		$result['status'] = $this->_status ? $this->_status : 0;

		echo json_encode($result);
	}

	/**
	 * 404页面
	 */
	public function actionError()
	{
		return $this->renderPartial('error');
	}

	/**
	 * 首页
	 */
	public function actionIndex()
	{
		//查不到订单,跳转404页面
		$order_no  = Yii::$app->request->get('order_no');
		$findOrder = LotteryHelper::findOrder($order_no);
		if (!$findOrder) {
			return $this->redirect('error');
		}

		//用户已登录领取红包，跳转页面
		$userWechatRel = UserWechatRel::findOne(['openid' => $this->_wxOpenId]);
		if ($userWechatRel) {
			$user_id  = $userWechatRel->user_id;
			$userInfo = UserHelper::getUserInfo($user_id, 'mobile');
			$mobile   = $userInfo['mobile'];

			$lottery_status = LotteryHelper::receiveGift($user_id, $order_no);//1:查不到订单 2:用户已领取 3:红包已领完 4:领取成功 5:今天领取次数超限
			if ($lottery_status == 1) {
				return $this->redirect('error');
			} elseif ($lottery_status == 2) {
				return $this->redirect("is-received?order_no=$order_no&mobile=$mobile");
			} elseif ($lottery_status == 3) {
				return $this->redirect("is-null?order_no=$order_no");
			} elseif ($lottery_status == 4) {
				return $this->redirect("receive-success?order_no=$order_no&mobile=$mobile");
			} elseif ($lottery_status == 5) {
				return $this->redirect("over-num?order_no=$order_no");
			}
		}

		//传递数据(微信jssdk配置、分享图片地址、订单号、url地址)
		$jssdk      = Yii::$app->mp_wechat->getApp()->jssdk->buildConfig(['getLocation', 'onMenuShareTimeline', 'onMenuShareAppMessage'], false);
		$share_logo = Yii::$app->params['wx_domain'] . '/static/lottery/img/img_link.png';
		$data       = [
			'jssdk'                 => $jssdk,
			'share_logo'            => $share_logo,
			'order_no'              => $order_no,
			'ajax_get_code'         => UrlHelper::wxLink('lottery/ajax-get-code'),
			'ajax_receive'          => UrlHelper::wxLink('lottery/ajax-receive'),
			'ajax_register_receive' => UrlHelper::wxLink('lottery/ajax-register-receive'),
			'receive_success'       => UrlHelper::wxLink("lottery/receive-success?order_no=$order_no"),
			'is_received'           => UrlHelper::wxLink("lottery/is-received?order_no=$order_no"),
			'is_null'               => UrlHelper::wxLink("lottery/is-null?order_no=$order_no"),
			'over_num'              => UrlHelper::wxLink("lottery/over-num?order_no=$order_no"),
		];

		return $this->renderPartial('index', $data);
	}

	/**
	 * 领取成功页
	 */
	public function actionReceiveSuccess()
	{
		//账号不存在,跳转404页面
		$mobile   = Yii::$app->request->get('mobile');
		$order_no = Yii::$app->request->get('order_no');
		$user     = UserHelper::checkMobileExist($mobile);
		if (!$user) {
			return $this->redirect('error');
		}

		//查不到订单,跳转404页面
		$findOrder = LotteryHelper::findOrder($order_no);
		if (!$findOrder) {
			return $this->redirect('error');
		}

		//查不到领取数据,跳转404页面
		$user_id = $user['uid'];
		$result  = LotteryHelper::getReceiveRecord($user_id, $order_no);
		if (!$result) {
			return $this->redirect('error');
		}

		//传递数据(领取数据、微信jssdk配置、分享图片地址、url地址、账号)
		$result['jssdk']         = Yii::$app->mp_wechat->getApp()->jssdk->buildConfig(['getLocation', 'onMenuShareTimeline', 'onMenuShareAppMessage'], false);
		$result['share_logo']    = Yii::$app->params['wx_domain'] . '/static/lottery/img/img_link.png';
		$result['app_introduce'] = UrlHelper::wxLink("lottery/app-introduce");
		$result['mobile']        = $mobile;
		$result['order_no']      = $order_no;

		return $this->renderPartial('receive-success', $result);
	}

	/**
	 * 已领取页
	 */
	public function actionIsReceived()
	{
		//账号不存在,跳转404页面
		$mobile   = Yii::$app->request->get('mobile');
		$order_no = Yii::$app->request->get('order_no');
		$user     = UserHelper::checkMobileExist($mobile);
		if (!$user) {
			return $this->redirect('error');
		}

		//查不到订单,跳转404页面
		$findOrder = LotteryHelper::findOrder($order_no);
		if (!$findOrder) {
			return $this->redirect('error');
		}

		//查不到领取数据,跳转404页面
		$user_id = $user['uid'];
		$result  = LotteryHelper::getReceiveRecord($user_id, $order_no);
		if (!$result) {
			return $this->redirect('error');
		}

		//传递数据(领取数据、微信jssdk配置、分享图片地址、url地址、账号)
		$result['jssdk']         = Yii::$app->mp_wechat->getApp()->jssdk->buildConfig(['getLocation', 'onMenuShareTimeline', 'onMenuShareAppMessage'], false);
		$result['share_logo']    = Yii::$app->params['wx_domain'] . '/static/lottery/img/img_link.png';
		$result['app_introduce'] = UrlHelper::wxLink("lottery/app-introduce");
		$result['mobile']        = $mobile;
		$result['order_no']      = $order_no;

		return $this->renderPartial('is-received', $result);
	}

	/**
	 * 已领完页
	 */
	public function actionIsNull()
	{
		//查不到订单,跳转404页面
		$order_no  = Yii::$app->request->get('order_no');
		$findOrder = LotteryHelper::findOrder($order_no);
		if (!$findOrder) {
			return $this->redirect('error');
		}

		//传递数据(微信jssdk配置、分享图片地址、订单号、url地址)
		$jssdk      = Yii::$app->mp_wechat->getApp()->jssdk->buildConfig(['getLocation', 'onMenuShareTimeline', 'onMenuShareAppMessage'], false);
		$share_logo = Yii::$app->params['wx_domain'] . '/static/lottery/img/img_link.png';
		$data       = [
			'jssdk'         => $jssdk,
			'share_logo'    => $share_logo,
			'order_no'      => $order_no,
			'app_introduce' => UrlHelper::wxLink("lottery/app-introduce"),
		];

		return $this->renderPartial('is-null', $data);
	}

	/**
	 * 领取次数超限页
	 */
	public function actionOverNum()
	{
		//查不到订单,跳转404页面
		$order_no  = Yii::$app->request->get('order_no');
		$findOrder = LotteryHelper::findOrder($order_no);
		if (!$findOrder) {
			return $this->redirect('error');
		}

		//传递数据(微信jssdk配置、分享图片地址、订单号、url地址)
		$jssdk      = Yii::$app->mp_wechat->getApp()->jssdk->buildConfig(['getLocation', 'onMenuShareTimeline', 'onMenuShareAppMessage'], false);
		$share_logo = Yii::$app->params['wx_domain'] . '/static/lottery/img/img_link.png';
		$data       = [
			'jssdk'         => $jssdk,
			'share_logo'    => $share_logo,
			'order_no'      => $order_no,
			'app_introduce' => UrlHelper::wxLink("lottery/app-introduce"),
		];

		return $this->renderPartial('over-num', $data);
	}

	/**
	 * 引导下载页
	 */
	public function actionAppIntroduce()
	{
		return $this->renderPartial('app-introduce');
	}

	/**
	 * ajax领取
	 */
	public function actionAjaxReceive()
	{
		//账号是否存在
		$mobile   = Yii::$app->request->get('mobile');
		$order_no = Yii::$app->request->get('order_no');
		$user     = UserHelper::checkMobileExist($mobile);
		if (!$user) {
			$this->setCodeMessage(StateCode::OTHER_MOBILE_NO_EXIST);

			return $this->response();
		}

		//领取红包
		$user_id                = $user['uid'];
		$data['lottery_status'] = LotteryHelper::receiveGift($user_id, $order_no);//1:查不到订单 2:用户已领取 3:红包已领完 4:领取成功 5:今天领取次数超限
		$this->_data            = $data;

		return $this->response();
	}

	/**
	 * ajax发送注册验证码
	 */
	public function actionAjaxGetCode()
	{
		$params = [
			'mobile' => Yii::$app->request->get('mobile'),
			'type'   => 1,//验证码类型：注册验证码
		];

		//账号是否已存在
		if (UserHelper::checkMobileExist($params['mobile'])) {
			$this->_status = StateCode::OTHER_MOBILE_EXIST;
			$this->_msg    = '用户已存在';

			return $this->response();
		}

		//验证码是否获取成功
		$res = SmsHelper::sendCode($params['mobile'], $params['type']);
		if ($res) {
			$this->_status = 0;
			$this->_msg    = '验证码已发送';
		} else {
			$this->_status = StateCode::COMMON_OPERA_ERROR;
			$this->_msg    = '验证码发送失败';
		}

		return $this->response();
	}

	/**
	 * ajax注册领取
	 */
	public function actionAjaxRegisterReceive()
	{
		$params = [
			'mobile'        => Yii::$app->request->get('mobile'),
			'password'      => Yii::$app->request->get('password'),
			'code'          => Yii::$app->request->get('code'),
			'user_location' => Yii::$app->request->get('user_location'),
			'order_no'      => Yii::$app->request->get('order_no'),
			'register_src'  => Ref::ORDER_FROM_WECHAT,
		];

		//处理用户位置坐标
		if ($params['user_location']) {
			$user_location           = AMapHelper::getCityAddressLocation($params['user_location']);
			$params['user_location'] = '[' . $user_location['longitude'] . ',' . $user_location['latitude'] . ']';
		} else {
			$params['user_location'] = null;
		}

		//验证码是否正确
		if (!SecurityHelper::checkCode($params['mobile'], $params['code'], Ref::SMS_CODE_REGISTER)) {
			$this->_status = StateCode::OTHER_SMS_INCORRECT_CODE;
			$this->_msg    = '验证码错误或失效';

			return $this->response();
		}

		//账号是否存在
		$user = UserHelper::checkMobileExist($params['mobile']);
		if ($user) {
			$this->_status = StateCode::OTHER_MOBILE_EXIST;
			$this->_msg    = '用户已存在';

			return $this->response();
		}

		$signUp = UserHelper::signUp($params);

		//是否注册成功
		if (!$signUp) {
			$this->_status = StateCode::COMMON_OPERA_ERROR;
			$this->_msg    = '注册失败,请重新填写';

			return $this->response();
		}

		//领取红包
		$userInfo = UserHelper::checkMobileExist($params['mobile']);
		$user_id                = $userInfo['uid'];
		$order_no               = Yii::$app->request->get('order_no');
		$data['lottery_status'] = LotteryHelper::receiveGift($user_id, $order_no);//1:查不到订单 2:用户已领取 3:红包已领完 4:领取成功 5:今天领取次数超限
		$this->_data            = $data;

		return $this->response();
	}
}