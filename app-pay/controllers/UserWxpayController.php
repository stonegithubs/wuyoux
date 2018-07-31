<?php

namespace pay\controllers;

use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\TripBikeHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\QueueHelper;
use Yii;
use yii\web\Controller;

/**
 * 用户版 App支付回调处理
 * Class WxpayController
 * @package pay\controllers
 */
class UserWxpayController extends Controller
{
	public function init()
	{
		$this->enableCsrfValidation = false;
	}

	public function actionNotify()
	{
		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {
			// 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
			Yii::$app->debug->pay_info("notify2018", $notify);

			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = TransactionHelper::successOrderTrade($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						QueueHelper::errandSendOrder($orderRes['ids_ref']);//派发订单

						return true;
					}
					// 用户支付失败
				} elseif ($notify['result_code'] === 'FAIL') {

				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}


	//快送->帮我买->添加配送费用
	public function actionErrandBuyAddExpense()
	{
		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("errand_buy_add_expense_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = ErrandBuyHelper::payExpenseSuccess($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	//快送->帮我送->增加小费
	public function actionErrandSendAddCustomFee()
	{

		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("errand_send_add_custom_fee_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = ErrandSendHelper::addCustomFeeSuccess($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	//快送->帮我办->添加小费
	public function actionErrandDoAddCustomFee()
	{

		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("errand_do_add_custom_fee_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = ErrandDoHelper::addCustomFeeSuccess($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	//缴纳保证金
	public function actionBailPay()
	{

		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("bail_pay_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = ShopHelper::bailPaySuccess($transaction_no, $trade_no, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	/**
	 * 充值回调
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function actionRechargePay()
	{
		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("balance_recharge_pay_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = WalletHelper::rechargePaySuccess($transaction_no, $trade_no, $fee, "微信异步回调", $data, Ref::PAYMENT_TYPE_WECHAT);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	//企业送支付
	public function actionBizOrderPayment()
	{

		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("biz_order_payment_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = BizSendHelper::orderPaymentSuccess($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	//小帮出行支付费用
	public function actionTripPayment()
	{

		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("trip_payment_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = TripBikeHelper::tripPaymentSuccess($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}

	//小帮出行支付费用
	public function actionTripAddReward()
	{

		$response = Yii::$app->user_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("trip_add_fee_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = TripBikeHelper::addRewardSuccess($transaction_no, $trade_no, Ref::PAYMENT_TYPE_WECHAT, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}
}
