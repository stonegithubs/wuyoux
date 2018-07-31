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
use api_wx\modules\mpv1\helpers\WxErrandBuyHelper;
use api_wx\modules\mpv1\helpers\WxErrandTrait;
use common\components\Ref;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use Yii;


class ErrandBuyController extends ControllerAccess
{

	use WxErrandTrait;


	////用户端
	//首页				/mpv1/errand-buy/index
	//创建订单			/mpv1/errand-buy/create
	//预支付(通用)		/mpv1/errand-buy/pre-payment  (ErrandController/PrePayment)
	//指定地点-计算距离	/mpv1/errand-buy/get-range
	//计价接口			/mpv1/errand-buy/calculation
	//订单任务页			/mpv1/errand-buy/user-task
	//订单详情			/mpv1/errand-buy/user-detail
	//订单取消 + 推送		/mpv1/errand-buy/user-cancel
	//订单取消流程 + 推送	/mpv1/errand-buy/user-cancel
	//订单确认 + 推送		/mpv1/errand-buy/user-confirm
	//订单删除			/mpv1/errand-buy/user-delete
	//订单评价(通用)		/mpv1/evaluate/save
	//商品费用付款 + 推送	/mpv1/errand-buy/pay-expense


	//帮我买 解决思路
	//1、首页（根据不同距离路程算出最低价格）
	//2、创建订单，支付完毕后进行推送订单
	//3、小帮抢单，同时推送通知给用户告知情况
	//4、小帮流程，1待接单，2已接单，3拨打电话，4开始配送，5配送到达，6商品费用

	//首页
	public function actionIndex()
	{
		$user_location = SecurityHelper::getBodyParam("user_location");    //参数
		$checkOrder    = ErrandBuyHelper::checkCanOrder($this->user_id);
		$userInfo      = UserHelper::getUserInfo($this->user_id);
		$user_city_id  = isset($userInfo['city_id']) ? $userInfo['city_id'] : null;
		$this->_data   = [
			'user_mobile' => isset($userInfo['mobile']) ? $userInfo['mobile'] : null,
			'order_no'    => $checkOrder['order_no'],
			"order_count" => intval($checkOrder['order_count']),
			'low_price'   => ErrandBuyHelper::getLowPrice($user_location, $user_city_id),    //最低金额
		];

		return $this->response();
	}

	//指定地点-计算距离
	public function actionGetRange()
	{
		$start_location = SecurityHelper::getBodyParam("start_location");
		$end_location   = SecurityHelper::getBodyParam("end_location");
		$userInfo       = UserHelper::getUserInfo($this->user_id);
		$this->_data    = ErrandHelper::getRangePriceDataForAMap($start_location, $end_location, $userInfo, Ref::CATE_ID_FOR_ERRAND_BUY);

		return $this->response();
	}

	/**
	 * 新建快送订单
	 */
	public function actionCreate()
	{

		//TODO 价格需要验证
		$orderAmount  = SecurityHelper::getBodyParam("service_price");
		$userInfo     = UserHelper::getUserInfo($this->user_id);
		$user_city_id = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
		$regionArr    = RegionHelper::getAddressIdByLocation(SecurityHelper::getBodyParam('start_location'), $user_city_id);
		$params       = [
			"base"     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND_BUY,
				'city_id'     => $regionArr['city_id'],
				'region_id'   => $regionArr['region_id'],
				'area_id'     => $regionArr['area_id'],
				'order_from'  => SecurityHelper::getBodyParam("order_from"),
				'user_mobile' => SecurityHelper::getBodyParam("user_mobile"),
				'order_type'  => Ref::ORDER_TYPE_ERRAND,
				'user_id'     => $this->user_id,
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
				'service_price'  => SecurityHelper::getBodyParam('service_price'),                    //小帮赏金
				'service_time'   => strtotime(SecurityHelper::getBodyParam('service_time')),        //预约收货时间
				'maybe_time'     => strtotime(SecurityHelper::getBodyParam('service_time')),        //预约收货时间
				'errand_type'    => Ref::ERRAND_TYPE_BUY,
				'errand_content' => SecurityHelper::getBodyParam('content'),
				'mobile'         => SecurityHelper::getBodyParam('user_mobile'),
				'service_qty'    => 1
			]
		];
		//检查是否允许下单
		$user_location  = SecurityHelper::getBodyParam('user_location');
		$start_location = SecurityHelper::getBodyParam('start_location');
		$check          = RegionHelper::checkCurrentRegionAndOpening($user_location, $start_location, Ref::CATE_ID_FOR_ERRAND_BUY, $user_city_id);
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

			$amount                = $orderHelper->calcOrder(Ref::ORDER_TYPE_ERRAND);
			$res['card_available'] = CouponHelper::getOrderCardNum($res['order_no']);  //可用优惠券数
			$res['discount']       = sprintf("%.2f", $amount['discount']);             //优惠金额
			$res['order_amount']   = sprintf("%.2f", $res['order_amount']);            //订单金额
			$res['amount_payable'] = sprintf("%.2f", $amount['amount_payable']);       //订单实付
			$this->_data           = $res;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);
		}

		return $this->response();
	}

	/**
	 * 获取订单计算明细
	 */
	public function actionCalculation()
	{
		//TODO
		$userInfo               = UserHelper::getUserInfo($this->user_id);
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id', 0);
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['online_money'] = OrderHelper::getOnlineDiscount($online_money);

		$data = WxErrandBuyHelper::getCalc($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);
		}

		return $this->response();
	}

	/**
	 * 用户流程
	 */
	public function actionUserTask()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'task';

		$data = ErrandBuyHelper::userTaskAndDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
		}

		return $this->response();
	}

	/**
	 * 用户明细
	 */
	public function actionUserDetail()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'detail';
		$data                   = ErrandBuyHelper::userTaskAndDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
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
		$data               = ErrandBuyHelper::userConfirm($params);
		if ($data) {
			$params['current_page'] = "finish";
			ErrandBuyHelper::pushToProviderNotice($params['order_no'], ErrandBuyHelper::PUSH_PROVIDER_TYPE_CONFIRM, $params);
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_CONFIRM);
		}

		return $this->response();
	}


	/**
	 * 用户支付商品费用
	 * @return array
	 */
	public function actionPayExpense()
	{
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['fee_id']     = SecurityHelper::getBodyParam('fee_id');
		$payment_id           = SecurityHelper::getBodyParam('payment_id');
		$params['user_id']    = $this->user_id;
		$params['payment_id'] = $payment_id;
		$openId               = SecurityHelper::getBodyParam('openid');
		$url                  = SecurityHelper::getBodyParam('url');

		$result    = [
			'code' => 0,
			'data' => null,
		];
		$errandRes = ErrandBuyHelper::payExpense($params);

		if ($errandRes) {

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$isSuccess = ErrandBuyHelper::payExpenseSuccess($errandRes['transaction_no'], date("YmdHis"), $payment_id, $errandRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;
			}

			if ($payment_id == Ref::PAYMENT_TYPE_CASH) {    //现金支付
				$isSuccess = ErrandBuyHelper::payExpenseSuccess($errandRes['transaction_no'], date("YmdHis"), $payment_id, $errandRes['fee'], "现金支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PAYMENT_CASH;
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $errandRes['fee'];
				$payParams['transaction_no'] = $errandRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/errand-buy-add-expense");
				$payParams['openid']         = $openId;
				$wxRes                       = WxpayHelper::jsOrder($payParams);
				$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
				$wxRes ? $result['data']['payInfo'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];

		return $this->response();
	}

	public function actionCheckOrder()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$data               = WxErrandBuyHelper::checkOrder($params);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}
}