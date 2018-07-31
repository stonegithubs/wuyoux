<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_user\modules\v1\api;

use api_user\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UrlHelper;

/**
 * 钱包模块版本控制
 * Class ErrandBuyAPI
 * @package api\modules\v1\api
 */
class WalletAPI extends HelperBase
{
	/**
	 * 余额充值
	 * @param $user_id
	 * @return array
	 */
	public static function balanceRechargeV10($user_id)
	{
		$money      = SecurityHelper::getBodyParam("money");
		$payment_id = SecurityHelper::getBodyParam('payment_id');
		$money      = doubleval($money);
		$params     = [
			'user_id'        => $user_id,
			'recharge_from'  => SecurityHelper::getBodyParam("recharge_from"),
			'order_amount'   => $money,
			'amount_payable' => $money,
			'get_amount'     => $money,
			'payment_id'     => $payment_id,
			'type'           => Ref::BELONG_TYPE_USER
		];

		$result   = [
			'code' => 0,
			'data' => null,
		];
		$orderRes = WalletHelper::createRechargeOrder($params);
		if ($orderRes) {
			$payRes = null;

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['body']           = "无忧帮帮-余额充值";
				$payParams['detail']         = "无忧帮帮-余额充值";
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/recharge-pay");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $payRes = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败

			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['body']           = "无忧帮帮-余额充值";
				$payParams['subject']        = "无忧帮帮-余额充值";
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/recharge-pay");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $payRes = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

			if ($result['code'] == 0) {

				$result['data'] = [
					'order_amount'    => sprintf("%.2f", $orderRes['fee']),
					'data'            => $payRes,
					'payment_id'      => $payment_id,
					'success_content' => "充值" . $money . '元到账',
				];
			}
		} else {
			$result['code'] = StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}

	/**
	 * 充值送优惠券或者送金额
	 *
	 * @param $user_id
	 * @return array
	 */
	public static function rechargePaymentV10($user_id)
	{
		$payment_id = SecurityHelper::getBodyParam('payment_id');
		$params     = [
			'user_id'          => $user_id,
			'recharge_from'    => SecurityHelper::getBodyParam("recharge_from"),
			'recharge_info_id' => SecurityHelper::getBodyParam('recharge_id'),
			'payment_id'       => $payment_id,
		];

		$result   = [
			'code' => 0,
			'data' => null,
		];
		$orderRes = WalletHelper::getRechargeParamsForPayment($params);
		if ($orderRes) {
			$payRes = null;

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/recharge-pay");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $payRes = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败

			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/recharge-pay");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $payRes = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

			if ($result['code'] == 0) {

				$result['data'] = [
					'order_amount'    => sprintf("%.2f", $orderRes['fee']),
					'data'            => $payRes,
					'payment_id'      => $payment_id,
					'success_content' => $orderRes['success_content'],
				];
			}
		} else {
			$result['code'] = StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}


}