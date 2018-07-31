<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/1
 */

namespace common\helpers\payment;

use common\helpers\HelperBase;
use common\helpers\utils\UtilsHelper;
use Yii;


class WxpayHelper extends HelperBase
{
	/**
	 * 旧版 APP支付
	 * @param $params
	 * @return array|bool
	 */
	public static function appOrder($params)
	{
		$result  = false;
		$payment = Yii::$app->app_wechat->getPayment();

		$body       = isset($params['body']) ? $params['body'] : "无忧帮帮-小帮快送";
		$attributes = [
			'trade_type'   => "APP",
			'body'         => $body,
			'detail'       => isset($params['detail']) ? $params['detail'] : $body,
			'out_trade_no' => $params['transaction_no'],
			'total_fee'    => YII_ENV_PROD ? $params['fee'] * 100 : 1,
		];
		isset($params['notify_url']) ? $attributes['notify_url'] = $params['notify_url'] : null;

		$res = $payment->order->unify($attributes);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS') {
			$prepay_id = $res['prepay_id'];
			$result    = $payment->jssdk->appConfig($prepay_id);
		} else {
			Yii::$app->debug->pay_info('wxpay_appOrder_res', $res);
		}

		return $result;
	}

	/**
	 * 用户版 APP支付
	 * @param $params
	 * @return array|bool
	 */
	public static function userAppOrder($params)
	{
		$result  = false;
		$payment = Yii::$app->user_app_wechat->getPayment();

		$body       = isset($params['body']) ? $params['body'] : "无忧帮帮-小帮快送";
		$attributes = [
			'trade_type'   => "APP",
			'body'         => $body,
			'detail'       => isset($params['detail']) ? $params['detail'] : $body,
			'out_trade_no' => $params['transaction_no'],
			'total_fee'    => YII_ENV_PROD ? $params['fee'] * 100 : 1,
		];
		isset($params['notify_url']) ? $attributes['notify_url'] = $params['notify_url'] : null;

		$res = $payment->order->unify($attributes);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS') {
			$prepay_id = $res['prepay_id'];
			$result    = $payment->jssdk->appConfig($prepay_id);
		} else {
			Yii::$app->debug->pay_info('wxpay_userAppOrder_res', $res);
		}

		return $result;
	}

	/**
	 * 小帮版 APP支付
	 * @param $params
	 * @return array|bool
	 */
	public static function workerAppOrder($params)
	{
		$result  = false;
		$payment = Yii::$app->worker_app_wechat->getPayment();

		$body       = isset($params['body']) ? $params['body'] : "无忧帮帮-保证金";
		$attributes = [
			'trade_type'   => "APP",
			'body'         => $body,
			'detail'       => isset($params['detail']) ? $params['detail'] : $body,
			'out_trade_no' => $params['transaction_no'],
			'total_fee'    => YII_ENV_PROD ? $params['fee'] * 100 : 1,
		];
		isset($params['notify_url']) ? $attributes['notify_url'] = $params['notify_url'] : null;

		$res = $payment->order->unify($attributes);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS') {
			$prepay_id = $res['prepay_id'];
			$result    = $payment->jssdk->appConfig($prepay_id);
		} else {
			Yii::$app->debug->pay_info('wxpay_workerAppOrder_res', $res);
		}

		return $result;
	}


	/**
	 * 公众号JsSDK支付
	 * @param $params
	 * @return array|bool
	 */
	public static function jsOrder($params)
	{
		$result  = false;
		$payment = Yii::$app->mp_wechat->getPayment();

		$body       = isset($params['body']) ? $params['body'] : "无忧帮帮-小帮快送";
		$attributes = [
			'trade_type'   => "JSAPI",
			'body'         => $body,
			'detail'       => isset($params['detail']) ? $params['detail'] : $body,
			'out_trade_no' => $params['transaction_no'],
			'total_fee'    => YII_ENV_PROD ? $params['fee'] * 100 : 1,
			'openid'       => $params['openid'],
		];
		isset($params['notify_url']) ? $attributes['notify_url'] = $params['notify_url'] : null;

		$res = $payment->order->unify($attributes);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS') {
			$prepay_id = $res['prepay_id'];
			$result    = json_encode($payment->jssdk->sdkConfig($prepay_id));
		} else {
			Yii::$app->debug->pay_info('wxpay_JsOrder_res', $res);
		}

		return $result;
	}


	/**
	 * 企业送小程序
	 * @param $params
	 * @return array|bool
	 */
	public static function minAppBizOrder($params)
	{
		$result  = false;
		$payment = Yii::$app->mini_biz->getPayment();

		$body       = isset($params['body']) ? $params['body'] : "无忧帮帮-小帮快送";
		$attributes = [
			'trade_type'   => "JSAPI",
			'body'         => $body,
			'detail'       => isset($params['detail']) ? $params['detail'] : $body,
			'out_trade_no' => $params['transaction_no'],
			'total_fee'    => YII_ENV_PROD ? $params['fee'] * 100 : 1,
			'openid'       => $params['openid'],
		];
		isset($params['notify_url']) ? $attributes['notify_url'] = $params['notify_url'] : null;

		$res = $payment->order->unify($attributes);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS') {
			$prepay_id = $res['prepay_id'];
			$result    = $payment->jssdk->bridgeConfig($prepay_id);
		} else {
			Yii::$app->debug->pay_info('wxpay_minAppBizOrder_res', $res);
		}

		return $result;
	}


	///////////////////////////////////////////以下开始退款/////////////////////////////////////////////////////////////////////////

	/**
	 * 旧版APP 由微信交易单号申请退款
	 *
	 * @param $trade_no //微信交易单号
	 * @param $refund_no //自定义的退款单号
	 * @param $total_fee //总金额
	 * @param $refund_fee //退款金额
	 * @return array|bool
	 */
	public static function APPRefundByTradeNo($trade_no, $refund_no, $total_fee, $refund_fee)
	{
		$result = false;

		$res = Yii::$app->app_wechat->getPayment()->refund->byTransactionId($trade_no, $refund_no, $total_fee, $refund_fee, $optional = []);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS'
			&& isset($res['result_code']) && $res['result_code'] == 'SUCCESS') {

			$result = $res;
		} else {
			Yii::$app->debug->pay_info('APPRefundByTradeNo', $res);
		}

		return $result;
	}

	/**
	 * 旧版APP 查询退款情况
	 *
	 * @param $refundNo
	 * @return array
	 */
	public static function APPQueryRefundByRefundNo($refundNo)
	{
		$payment = Yii::$app->app_wechat->getPayment()->refund;

		return $payment->queryByRefundId($refundNo);
	}

	/**
	 * 用户版 由微信交易单号申请退款
	 *
	 * @param $trade_no //微信交易单号
	 * @param $refund_no //自定义的退款单号
	 * @param $total_fee //总金额
	 * @param $refund_fee //退款金额
	 * @return array|bool
	 */
	public static function UserAPPRefundByTradeNo($trade_no, $refund_no, $total_fee, $refund_fee)
	{
		$result = false;

		$res = Yii::$app->user_app_wechat->getPayment()->refund->byTransactionId($trade_no, $refund_no, $total_fee, $refund_fee, $optional = []);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS'
			&& isset($res['result_code']) && $res['result_code'] == 'SUCCESS') {

			$result = $res;
		} else {
			Yii::$app->debug->pay_info('UserAPPRefundByTradeNo', $res);
		}

		return $result;
	}

	/**
	 * 用户版 查询退款情况
	 *
	 * @param $refundNo
	 * @return array
	 */
	public static function UserAPPQueryRefundByRefundNo($refundNo)
	{
		$payment = Yii::$app->user_app_wechat->getPayment()->refund;

		return $payment->queryByRefundId($refundNo);
	}


	/**
	 * 公众号退款处理 有微信交易单号申请退款
	 * @param $trade_no //微信交易单号
	 * @param $refund_no //自定义的退款单号
	 * @param $total_fee //总金额
	 * @param $refund_fee //退款金额
	 *
	 * @return bool
	 */
	public static function MpRefundByTradeNo($trade_no, $refund_no, $total_fee, $refund_fee, $optional = [])
	{

		$result = false;

		$res = Yii::$app->mp_wechat->getPayment()->refund->byTransactionId($trade_no, $refund_no, $total_fee, $refund_fee, $optional);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS'
			&& isset($res['result_code']) && $res['result_code'] == 'SUCCESS') {

			$result = $res;
		} else {
			Yii::$app->debug->pay_info('MpRefundByTradeNo', $res);
		}

		return $result;
	}

	/**
	 * 企业送小程序退款处理 有微信交易单号申请退款
	 * @param $trade_no //微信交易单号
	 * @param $refund_no //自定义的退款单号
	 * @param $total_fee //总金额
	 * @param $refund_fee //退款金额
	 *
	 * @return bool
	 */
	public static function MiniBizRefundByTradeNo($trade_no, $refund_no, $total_fee, $refund_fee, $optional = [])
	{

		$result = false;

		$res = Yii::$app->mini_biz->getPayment()->refund->byTransactionId($trade_no, $refund_no, $total_fee, $refund_fee, $optional);
		if (isset($res['return_code']) && $res['return_code'] == 'SUCCESS'
			&& isset($res['result_code']) && $res['result_code'] == 'SUCCESS') {

			$result = $res;
		} else {
			Yii::$app->debug->pay_info('MiniBizRefundByTradeNo', $res);
		}

		return $result;
	}

	/**
	 * 公众号查询退款
	 * @param $refundNo
	 * @return array
	 */
	public static function MpQueryRefundByRefundNo($refundNo)
	{
		$payment = Yii::$app->app_wechat->getPayment()->refund;

		return $payment->queryByOutRefundNumber($refundNo);
	}

	///////////////////////////////////////////////一下获取jssdk信息/////////////////////////////////////////////////////

	/**
	 * 公众号JsSDK 获取js配置
	 * @param array $apis
	 * @param null  $url
	 * @return array|string
	 */
	public static function getJsConfig($apis = ['chooseWXPay'], $url = null, $json = true)
	{
		$js    = Yii::$app->mp_wechat->getApp()->jssdk;
		$debug = true;
		if (YII_ENV_PROD) {
			$debug = false;
		}

		if ($url) {
			$js->setUrl($url);
		}

		return $js->buildConfig($apis, $debug, false, $json);
	}

	/**
	 * 根据APP ID来判断使用那种退款方式
	 * @param $app_id
	 * @param $out_trade_no
	 * @param $transaction_no
	 * @param $fee
	 * @return array|bool
	 */
	public static function refundForAllProvider($app_id, $out_trade_no, $transaction_no, $fee)
	{
		$result = false;

		$fee = YII_ENV_PROD ? intval(round(floatval($fee) * 100)) : 1;

		if ($app_id == "wx4ac5a53b6105e479") {    //测试环境公众号
			$result = WxpayHelper::MpRefundByTradeNo($out_trade_no, $transaction_no, $fee, $fee);
		}

		if ($app_id == "wx30a7c4dd30933ac8") {    //线上环境公众号
			$result = WxpayHelper::MpRefundByTradeNo($out_trade_no, $transaction_no, $fee, $fee);
		}

		if ($app_id == "wx0803e6035308d429") {    //企业送小程序
			$result = WxpayHelper::MiniBizRefundByTradeNo($out_trade_no, $transaction_no, $fee, $fee);
		}

		if ($app_id == "wx5ad660aa421150e6") {    //用户版APP
			$result = WxpayHelper::UserAPPRefundByTradeNo($out_trade_no, $transaction_no, $fee, $fee);
		}

		if ($app_id == "wxe1f869ba9f5165c8") {    //旧APP

			$result = WxpayHelper::APPRefundByTradeNo($out_trade_no, $transaction_no, $fee, $fee);
		}

		Yii::$app->debug->pay_info("wx_refund_res", $result);

		if (!$result) {
			Yii::$app->debug->pay_info("wx_refund_fail", $out_trade_no);
		}

		return $result;
	}
}