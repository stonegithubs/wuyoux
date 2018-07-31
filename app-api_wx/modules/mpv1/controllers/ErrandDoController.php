<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\helpers\StateCode;
use api_wx\modules\mpv1\helpers\WxErrandDoHelper;
use api_wx\modules\mpv1\helpers\WxErrandTrait;
use common\components\Ref;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;

class ErrandDoController extends ControllerAccess
{
	use WxErrandTrait;

	/**
	 * 首页
	 */
	public function actionIndex()
	{
		$userInfo      = UserHelper::getUserInfo($this->user_id);
		$checkOrder    = ErrandDoHelper::checkCanOrder($this->user_id);
		$user_location = SecurityHelper::getBodyParam("user_location", SecurityHelper::getBodyParam("end_location"));
		$this->_data   = [
			'service_price' => ErrandDoHelper::getServicePrice($user_location, $this->user_id),
			'user_mobile'   => isset($userInfo['mobile']) ? $userInfo['mobile'] : null,
			'order_no'      => $checkOrder['order_no'],
			"order_count"   => intval($checkOrder['order_count']),
		];

		return $this->response();
	}

	/**
	 * 获取单价
	 */
	public function actionGetServicePrice()
	{
		$service_location = SecurityHelper::getBodyParam("service_location");
		$this->_data      = ErrandDoHelper::getServicePrice($service_location, $this->user_id);

		return $this->response();
	}

	/**
	 * 新建快送订单
	 */
	public function actionCreate()
	{
		$service_qty    = SecurityHelper::getBodyParam('service_qty', 1);
		$orderAmount    = ErrandDoHelper::getServicePrice(SecurityHelper::getBodyParam("start_location"), $this->user_id) * $service_qty;
		$userInfo       = UserHelper::getUserInfo($this->user_id);
		$online_money   = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$discount_money = OrderHelper::getOnlineDiscount($online_money);
		$user_city_id   = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
		$regionArr      = RegionHelper::getAddressIdByLocation(SecurityHelper::getBodyParam('start_location'), $user_city_id);
		$params         = [
			"base"     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND_DO,
				'city_id'     => $regionArr['city_id'],
				'region_id'   => $regionArr['region_id'],
				'area_id'     => $regionArr['area_id'],
				'order_from'  => SecurityHelper::getBodyParam("order_from"),
				'user_mobile' => SecurityHelper::getBodyParam("user_mobile"),
				'order_type'  => Ref::ORDER_TYPE_ERRAND,
				'user_id'     => $this->user_id
			],
			'amount'   => [
				'order_amount' => $orderAmount,
			],
			'location' => [
				'user_location'  => SecurityHelper::getBodyParam("user_location"),
				'user_address'   => SecurityHelper::getBodyParam("user_address"),
				'start_location' => SecurityHelper::getBodyParam("start_location"),
				'start_address'  => SecurityHelper::getBodyParam("start_address"),
				'end_location'   => SecurityHelper::getBodyParam("end_location"),
				'end_address'    => SecurityHelper::getBodyParam("end_address")
			],
			'errand'   => [
				'service_price'  => ErrandDoHelper::getServicePrice(SecurityHelper::getBodyParam("start_location"), $this->user_id),
				'service_time'   => strtotime(SecurityHelper::getBodyParam('service_time')),
				'errand_type'    => SecurityHelper::getBodyParam('errand_type', Ref::ERRAND_TYPE_DO),
				'errand_content' => SecurityHelper::getBodyParam('content'),
				'mobile'         => SecurityHelper::getBodyParam('user_mobile'),
				'first_fee'      => SecurityHelper::getBodyParam('first_fee'),
				'service_qty'    => SecurityHelper::getBodyParam('service_qty')
			]
		];
		//检查是否允许下单
		$user_location  = SecurityHelper::getBodyParam('user_location');
		$start_location = SecurityHelper::getBodyParam('start_location');
		$check          = RegionHelper::checkCurrentRegionAndOpening($user_location, $start_location, Ref::CATE_ID_FOR_ERRAND_DO, $user_city_id);
		if (!$check['pass']) {
			$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);
			$this->_message = $check['message'];

			return $this->response();
		}

		//创建订单
		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($params);
		if ($orderHelper->checkErrandParams()) {
			$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);

			return $this->response();
		}
		$res = $orderHelper->save();
		if (is_array($res)) {

			$res['total_fee']      = sprintf("%.2f", SecurityHelper::getBodyParam('first_fee'));
			$res['online_money']   = $discount_money;
			$res['card_available'] = CouponHelper::getOrderCardNum($res['order_no']);

			$this->_data = $res;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_CREATE);
		}


		return $this->response();
	}

	/**
	 * 获取订单计算明细
	 */
	public function actionCalculation()
	{


		$userInfo               = UserHelper::getUserInfo($this->user_id);
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id', 0);
		$params['errand_type']  = SecurityHelper::getBodyParam('errand_type');
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['online_money'] = OrderHelper::getOnlineDiscount($online_money);

		$data = ErrandDoHelper::getCalc($params);

		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_SEND_CREATE_FAILED);
		}

		return $this->response();
	}

	/**
	 * 订单明细
	 */
	public function actionUserDetail()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = "finish";
		$res                    = ErrandDoHelper::userDetail($params);

		if ($res) {
			$this->_data = $res;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_DETAIL);
		}

		return $this->response();
	}

	/**
	 * 订单任务
	 */
	public function actionUserTask()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = "task";
		$res                    = ErrandDoHelper::userDetail($params);

		if ($res) {
			$this->_data = $res;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_DETAIL);
		}

		return $this->response();
	}

	/**
	 * 订单确认
	 */
	public function actionUserConfirm()
	{


		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$data               = ErrandDoHelper::userConfirm($params);
		if ($data) {
			ErrandDoHelper::pushToProviderNotice($params['order_no'], ErrandDoHelper::PUSH_PROVIDER_TYPE_CONFIRM, $params);
			$data['current_page'] = "finish";
			$this->_message       = "订单已确认";
		} else {

			$this->setCodeMessage(StateCode::ERRAND_USER_CONFIRM);
		}

		return $this->response();
	}


	/**
	 * 增加小费
	 * @return array
	 */
	public function actionAddFee()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['fee']      = SecurityHelper::getBodyParam('fee');
		$params['user_id']  = $this->user_id;
		$openId             = SecurityHelper::getBodyParam('openid');
		$url                = SecurityHelper::getBodyParam('url');

		$result = [
			'code' => 0,
			'data' => null,
		];

		$orderRes   = ErrandDoHelper::addCustomFee($params);
		$payment_id = $orderRes['payment_id'];
		if ($orderRes) {

			$payRes = null;
			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$isSuccess = ErrandDoHelper::addCustomFeeSuccess($orderRes['transaction_no'], date("YmdHis"), $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/errand-do-add-custom-fee");
				$payParams['openid']         = $openId;
				$wxRes                       = WxpayHelper::jsOrder($payParams);
				$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
				$wxRes ? $result['data']['payInfo'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

		} else {
			$result['code'] = StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];

		return $this->response();
	}


	public function actionCheckOrder()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$data               = WxErrandDoHelper::checkOrder($params);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}
}