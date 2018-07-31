<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api\modules\v1\api;

use api\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UrlHelper;
use common\models\orders\Order;


class ErrandAPI extends HelperBase
{
	/**
	 * 通用预支付
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function prePaymentV10($user_id)
	{
		$userInfo               = UserHelper::getUserInfo($user_id);
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id');
		$payment_id             = SecurityHelper::getBodyParam('payment_id');
		$verify_code            = SecurityHelper::getBodyParam('verify_code');        //TODO 余额支付时录入密码会返回验证码，验证码和预支付一起提交，后台再次验证，暂不使用
		$params['online_money'] = OrderHelper::getOnlineDiscount($online_money);
		$params['payment_id']   = $payment_id;

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
				$payParams['notify_url']     = UrlHelper::payNotify("wxpay/notify");
				$wxRes                       = WxpayHelper::appOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/notify");
				$alipayRes                   = AlipayHelper::appOrder($payParams);

				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}

	/**
	 * 通用抢单接口
	 *
	 * @param $user_id
	 * @param $provider_id
	 *
	 * @return bool
	 */
	public static function robbingV10($user_id, $provider_id)
	{
		$shopInfo                    = ShopHelper::getShopInfoByUserId($user_id);
		$params['order_no']          = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']       = $provider_id;
		$params['provider_location'] = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam('provider_location'));
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = isset($shopInfo['utel']) ? $shopInfo['utel'] : null;
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');

		return ErrandHelper::saveRobbing($params);
	}

	/**
	 * 通用小帮删除订单
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerDeleteV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		return ErrandHelper::workerDelete($params);
	}

	/**
	 * 通用用户删除订单
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function userDeleteV10($user_id)
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;

		return ErrandHelper::userDelete($params);
	}

	/**
	 * 通用小帮申请取消订单
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerCancelV10($provider_id)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		$data = ErrandBuyHelper::workerCancel($params);
		if ($data) {
			self::_workerCancelUserNotice($data);
		}

		return $data;
	}

	/**
	 * 通用小帮取消订单流程
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerCancelFlowV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		$params['agreed']      = SecurityHelper::getBodyParam('agreed');
		$data                  = ErrandBuyHelper::workerCancelFlow($params);
		if ($data) {
			self::_workerCancelUserNotice($data);
			if ($params['agreed'] == 'yes') {    //进入退款流程
				QueueHelper::preRefund($data);
			}
		}

		return $data;
	}

	/**
	 * 通用用户申请取消订单
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function userCancelV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$data               = ErrandHelper::userCancel($params);
		if ($data) {
			if ($data['robbed'] == Ref::ORDER_ROBBED) { //透传数据并进入退单流程

				$data['message'] = "申请成功待小帮处理";
				self::_userCancelProviderNotice($data);
			} else {
				$data['message'] = "取消成功，资金会原路返回，请耐心等待";
				QueueHelper::preRefund($data);//进入退款流程
			}
		}

		return $data;
	}

	/**
	 * 通用用户取消订单流程
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function userCancelFlowV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$params['agreed']   = SecurityHelper::getBodyParam('agreed');
		$data               = ErrandBuyHelper::userCancelFlow($params);
		if ($data) {
			self::_userCancelProviderNotice($data);
			$data['message'] = $params['agreed'] == 'yes' ? "取消成功，资金会原路返回，请耐心等待" : '提交成功';
			if ($params['agreed'] == 'yes') {    //进入退款流程
				QueueHelper::preRefund($data);
			}
		}

		return $data;
	}

	/**
	 * 小帮工作流1.0
	 *
	 * @param $provider_id
	 *
	 * @return array|bool
	 */
	public static function workerProgressV10($provider_id)
	{
		$result                     = false;
		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['errand_status']    = SecurityHelper::getBodyParam('errand_status');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam('current_location'));

		$params['provider_id'] = $provider_id;
		$data                  = ErrandSendHelper::workerProgress($params);
		if ($data) {

			if ($params['errand_status'] == Ref::ERRAND_STATUS_FINISH) {
				$ttl = YII_ENV_DEV ? 60 * 5 : Ref::TTL_AUTO_CONFIRM_ERRAND_ORDER;    //测试环境5分钟自动确认收货，正式环境24小时
				QueueHelper::autoConfirmErrandOrder($data, $ttl);
			}

			self::_workerProgressUserNotice($data);
			$result = $data;
		}

		return $result;
	}


	/**
	 * 小帮取消通知发给用户
	 *
	 * @param $data
	 */
	private static function _workerProgressUserNotice($data)
	{
		if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY)
			ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_DO)
			ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_SEND)
			ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandSendHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);
	}

	/**
	 * 用户取消通知发给小帮
	 *
	 * @param $data
	 */
	private static function _userCancelProviderNotice($data)
	{
		if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY)
			ErrandBuyHelper::pushToProviderNotice($data['order_no'], ErrandBuyHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_DO)
			ErrandDoHelper::pushToProviderNotice($data['order_no'], ErrandDoHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_SEND)
			ErrandSendHelper::pushToProviderNotice($data['order_no'], ErrandSendHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);
	}

	/**
	 * 小帮取消通知发给用户
	 *
	 * @param $data
	 */
	private static function _workerCancelUserNotice($data)
	{
		if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY)
			ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_DO)
			ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);

		if ($data['errand_type'] == Ref::ERRAND_TYPE_SEND)
			ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandSendHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);
	}
}