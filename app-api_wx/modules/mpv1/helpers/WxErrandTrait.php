<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/9/11
 */

namespace api_wx\modules\mpv1\helpers;

use common\components\Ref;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UrlHelper;

trait  WxErrandTrait
{

	//共用小帮快送的方法
	/**
	 * @通用 预支支付
	 * 帮我送，帮我买，我帮办
	 */
	public function actionPrePayment()
	{

		//TODO 支付功能

		$userInfo               = UserHelper::getUserInfo($this->user_id);
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id');
		$payment_id             = SecurityHelper::getBodyParam('payment_id');
		$openId                 = SecurityHelper::getBodyParam("openid");
		$params['online_money'] = OrderHelper::getOnlineDiscount($online_money);
		$params['payment_id']   = $payment_id;
		$url = SecurityHelper::getBodyParam('url');
		$orderHelper = new OrderHelper();
		$orderRes    = $orderHelper->updatePrepayment($params);
		$result      = [
			'code' => 0,
			'data' => null
		];

		if ($orderRes) {

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = TransactionHelper::successOrderTrade($orderRes['transaction_no'], $trade_no, $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? QueueHelper::errandSendOrder($orderRes['order_id'])
					: $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/notify");
				$payParams['openid']         = $openId;

				$wxRes                       = WxpayHelper::jsOrder($payParams);
				$result['data']['wxInfo'] = WxpayHelper::getJsConfig(['chooseWXPay'],$url);
				$wxRes ? $result['data']['payInfo'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];

		return $this->response();
	}


	/**
	 * @通用 用户逻辑删除
	 * @return array
	 */
	public function actionUserDelete()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;

		$data = ErrandHelper::userDelete($params);
		if ($data) {
			$this->_message = "删除成功";
		} else {

			$this->setCodeMessage(StateCode::ERRAND_DELETE);
		}

		return $this->response();
	}


	/**
	 * 用户取消订单
	 */
	public function actionUserCancel()
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$data               = ErrandHelper::userCancel($params);
		if ($data) {
			if ($data['robbed'] == Ref::ORDER_ROBBED) { //透传数据并进入退单流程

				$data['message'] = "申请成功待小帮处理";
				$this->_userCancelProviderNotice($data);
			} else {
				$data['message'] = "取消成功，资金会原路返回，请耐心等待";
				QueueHelper::preRefund($data);//进入退款流程
			}

			$this->_message = $data['message'];
			$this->_data    = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
		}

		return $this->response();
	}

	/**
	 * 用户取消订单工作流
	 */
	public function actionUserCancelFlow()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$params['agreed']   = SecurityHelper::getBodyParam('agreed');
		$data               = ErrandBuyHelper::userCancelFlow($params);
		if ($data) {
			$this->_userCancelProviderNotice($data);
			$this->_message = $params['agreed'] == 'yes' ? "取消成功，资金会原路返回，请耐心等待" : '提交成功';
		} else {
			$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
		}

		return $this->response();
	}

	/**
	 * 用户取消通知发给小帮
	 * @param $data
	 */
	private function _userCancelProviderNotice($data)
	{
		if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY)
			ErrandBuyHelper::pushToProviderNotice($data['order_no'], ErrandBuyHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_DO)
			ErrandDoHelper::pushToProviderNotice($data['order_no'], ErrandDoHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_SEND)
			ErrandSendHelper::pushToProviderNotice($data['order_no'], ErrandSendHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);
	}

}