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
use api_wx\modules\mpv1\helpers\WxBizSendHelper;
use api_wx\modules\mpv1\helpers\WxUserHelper;
use api_wx\modules\mpv1\helpers\WxWalletHelper;
use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\UrlHelper;


/**
 * 钱包控制器
 * Class ErrandSendController
 * @package api_wx\modules\mpv1\controllers
 */
class WalletController extends ControllerAccess
{

	/**
	 * 钱包首页
	 * @return array
	 */
	public function actionIndex()
	{
		$wallet_info = WxUserHelper::getWalletInfo($this->user_id);

		$this->_data = [
			'balance' => $wallet_info['balance'],
			'count'   => $wallet_info['count'],

		];

		return $this->response();
	}

	/**
	 * 收支明细-列表
	 * @return array
	 */
	public function actionTransactionList()
	{
		$params                  = [];
		$params['user_id']       = $this->user_id;
		$params['last_separate'] = SecurityHelper::getBodyParam('last_separate');
		$params['page']          = SecurityHelper::getBodyParam('page');
		$params['pageSize']      = SecurityHelper::getBodyParam('pageSize');
		$params['type']          = SecurityHelper::getBodyParam('type');
		$data                    = WxWalletHelper::transactionList($params);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();

	}

	/**
	 * 收支明细-详情
	 * @return array
	 */
	public function actionTransactionDetail()
	{
		//$data = WxWalletHelper::transactionDetail();
		$data = WxWalletHelper::getIncome();

		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}


	//一般充值首页 适用出行,快送
	public function actionUserRechargeIndex()
	{
		$data        = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_USER);
		$this->_data = $data;

		return $this->response();
	}

	//按自定义金额支付 适用出行,快送
	public function actionUserInputPayment()
	{
		$money  = SecurityHelper::getBodyParam("money");
		$openId = SecurityHelper::getBodyParam("openid");
		$url    = SecurityHelper::getBodyParam("url");
		$money  = doubleval($money);
		$params = [
			'user_id'        => $this->user_id,
			'recharge_no'    => OrderHelper::getOrderNo(),
			'recharge_from'  => Ref::ORDER_FROM_WECHAT,
			'order_amount'   => $money,
			'amount_payable' => $money,
			'get_amount'     => $money,
			'payment_id'     => Ref::PAYMENT_TYPE_WECHAT,
			'type'           => Ref::BELONG_TYPE_USER
		];

		$orderRes = WalletHelper::createRechargeOrder($params);
		if ($orderRes) {

			$payParams['fee']            = $orderRes['fee'];
			$payParams['transaction_no'] = $orderRes['transaction_no'];
			$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/recharge-pay");
			$payParams['openid']         = $openId;
			$payParams['body']           = "无忧帮帮-余额充值";
			$wxRes                       = WxpayHelper::jsOrder($payParams);
			$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
			$wxRes ? $result['data']['payInfo'] = $wxRes : $this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_WECHAT);
			$this->_data = $result['data'];

		} else {
			$this->setCodeMessage(StateCode::TRANSACTION_FAILED);
		}

		return $this->response();
	}

	//按充值方案支付 适用出行,快送
	public function actionUserPlanPayment()
	{
		$openId   = SecurityHelper::getBodyParam('openid');
		$url      = SecurityHelper::getBodyParam("url");
		$params   = [
			'user_id'          => $this->user_id,
			'recharge_no'      => OrderHelper::getOrderNo(),
			'recharge_from'    => Ref::ORDER_FROM_WECHAT,
			'recharge_info_id' => SecurityHelper::getBodyParam('recharge_id'),
			'payment_id'       => Ref::PAYMENT_TYPE_WECHAT,
		];
		$orderRes = WalletHelper::getRechargeParamsForPayment($params);
		if ($orderRes) {

			$payParams['fee']            = $orderRes['fee'];
			$payParams['transaction_no'] = $orderRes['transaction_no'];
			$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/recharge-pay");
			$payParams['openid']         = $openId;
			$payParams['body']           = "无忧帮帮-余额充值";
			$wxRes                       = WxpayHelper::jsOrder($payParams);
			$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
			$wxRes ? $result['data']['payInfo'] = $wxRes : $this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_WECHAT);
			$this->_data = $result['data'];

		} else {
			$this->setCodeMessage(StateCode::TRANSACTION_FAILED);
		}

		return $this->response();
	}


	//企业送充值首页 适用企业送
	public function actionBizRechargeIndex()
	{
		$data        = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_BIZ);
		$this->_data = $data;

		return $this->response();
	}

	//按自定义金额支付 企业送
	public function actionBizInputPayment()
	{
		$money  = SecurityHelper::getBodyParam("money");
		$openId = SecurityHelper::getBodyParam("openid");
		$url    = SecurityHelper::getBodyParam("url");
		$money  = doubleval($money);
		$params = [
			'user_id'        => $this->user_id,
			'recharge_no'    => OrderHelper::getOrderNo(),
			'recharge_from'  => Ref::ORDER_FROM_WECHAT,
			'order_amount'   => $money,
			'amount_payable' => $money,
			'get_amount'     => $money,
			'payment_id'     => Ref::PAYMENT_TYPE_WECHAT,
			'type'           => Ref::BELONG_TYPE_BIZ
		];

		$orderRes = WalletHelper::createRechargeOrder($params);
		if ($orderRes) {

			$payParams['fee']            = $orderRes['fee'];
			$payParams['transaction_no'] = $orderRes['transaction_no'];
			$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/recharge-pay");
			$payParams['openid']         = $openId;
			$payParams['body']           = "无忧帮帮-余额充值";
			$wxRes                       = WxpayHelper::jsOrder($payParams);
			$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
			$wxRes ? $result['data']['payInfo'] = $wxRes : $this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_WECHAT);
			$this->_data = $result['data'];

		} else {
			$this->setCodeMessage(StateCode::TRANSACTION_FAILED);
		}

		return $this->response();
	}

	//按充值方案支付 适用企业送
	public function actionBizPlanPayment()
	{
		$openId   = SecurityHelper::getBodyParam('openid');
		$url      = SecurityHelper::getBodyParam("url");
		$params   = [
			'user_id'          => $this->user_id,
			'recharge_no'      => OrderHelper::getOrderNo(),
			'recharge_from'    => Ref::ORDER_FROM_WECHAT,
			'recharge_info_id' => SecurityHelper::getBodyParam('recharge_id'),
			'payment_id'       => Ref::PAYMENT_TYPE_WECHAT,
		];
		$orderRes = WalletHelper::getRechargeParamsForPayment($params);
		if ($orderRes) {

			$payParams['fee']            = $orderRes['fee'];
			$payParams['transaction_no'] = $orderRes['transaction_no'];
			$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/recharge-pay");
			$payParams['openid']         = $openId;
			$payParams['body']           = "无忧帮帮-余额充值";
			$wxRes                       = WxpayHelper::jsOrder($payParams);
			$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
			$wxRes ? $result['data']['payInfo'] = $wxRes : $this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_WECHAT);
			$this->_data = $result['data'];

		} else {
			$this->setCodeMessage(StateCode::TRANSACTION_FAILED);
		}

		return $this->response();
	}
}