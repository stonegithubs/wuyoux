<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_user\modules\v1\api;

use api\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;

/**
 * 帮我买API版本控制
 * Class ErrandBuyAPI
 * @package api\modules\v1\api
 */
class ErrandBuyAPI extends HelperBase
{
	/**
	 * 首页数据1.0
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function IndexV10($user_id)
	{
		$user_location = SecurityHelper::getBodyParam("user_location");    //参数
		$checkOrder    = ErrandBuyHelper::checkCanOrder($user_id);
		$userInfo      = UserHelper::getUserInfo($user_id);
		$user_city_id  = isset($userInfo['city_id']) ? $userInfo['city_id'] : null;
		$result        = [
			'cateList'    => ErrandBuyHelper::getOrderCateList(),
			'use_mobile'  => isset($userInfo['mobile']) ? $userInfo['mobile'] : null,
			'order_no'    => $checkOrder['order_no'],
			"order_count" => intval($checkOrder['order_count']),
			'low_price'   => ErrandBuyHelper::getLowPrice($user_location, $user_city_id),    //最低金额
		];

		return $result;
	}

	/**
	 * 指定地址返回计算结果给前端1.0
	 * @return array
	 */
	public static function getRangeV10($user_id)
	{
		$start_location = SecurityHelper::getBodyParam("start_location");
		$end_location   = SecurityHelper::getBodyParam("end_location");
		$userInfo       = UserHelper::getUserInfo($user_id);

		return ErrandHelper::getRangePriceDataForAMap($start_location, $end_location, $userInfo, Ref::CATE_ID_FOR_ERRAND_BUY);
	}

	/**
	 * 创建订单1.0
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function createOrderV10($user_id,$user_city_id)
	{
		$result       = false;
		$orderAmount  = SecurityHelper::getBodyParam("service_price");
		$regionArr    = RegionHelper::getAddressIdByLocation(SecurityHelper::getBodyParam('start_location'), $user_city_id);
		$params = [
			"base"     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND_BUY,
				'city_id'     => $regionArr['city_id'],
				'region_id'   => $regionArr['region_id'],
				'area_id'     => $regionArr['area_id'],
				'order_from'  => SecurityHelper::getBodyParam("order_from"),
				'user_mobile' => SecurityHelper::getBodyParam("user_mobile"),
				'order_type'  => Ref::ORDER_TYPE_ERRAND,
				'user_id'     => $user_id,
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

		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($params);
		if ($orderHelper->checkErrandParams()) {
			return $result;
		}

		$res = $orderHelper->save();
		if (is_array($res)) {

			$amount                = $orderHelper->calcOrder(Ref::ORDER_TYPE_ERRAND);
			$res['card_available'] = CouponHelper::getOrderCardNum($res['order_no']);    //可用优惠券数
			$res['discount']       = sprintf("%.2f", $amount['discount']);                //优惠金额
			$res['order_amount']   = sprintf("%.2f", $res['order_amount']);            //订单金额
			$res['amount_payable'] = sprintf("%.2f", $amount['amount_payable']);                        //订单金额
			$result                = $res;
		}

		return $result;
	}

	/**
	 * 订单计算价格1.0
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function orderCalcV10($user_id)
	{
		$userInfo               = UserHelper::getUserInfo($user_id);
		$params['user_city']    = $userInfo['city_id'];
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id', 0);
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['online_money'] = $online_money;

		return ErrandBuyHelper::getCalc($params);

	}

	/**
	 * 用户任务页1.0
	 * @return array|bool
	 */
	public static function userTaskV10()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'task';

		return ErrandBuyHelper::userTaskAndDetail($params);
	}

	/**
	 * 用户详情页1.0x
	 * @return array|bool
	 */
	public static function userDetailV10()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'detail';

		return ErrandBuyHelper::userTaskAndDetail($params);
	}

	/**
	 * 用户确认1.0
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function userConfirmV10($user_id)
	{

		$result             = false;
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$data               = ErrandBuyHelper::userConfirm($params);
		if ($data) {
			$params['current_page'] = "finish";
			ErrandBuyHelper::pushToProviderNotice($params['order_no'], ErrandBuyHelper::PUSH_PROVIDER_TYPE_CONFIRM, $params);
			$result = $data;
		}

		return $result;
	}

	/**
	 * 用户支付配送费用
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function payExpenseV10($user_id)
	{

		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['fee_id']     = SecurityHelper::getBodyParam('fee_id');
		$payment_id           = SecurityHelper::getBodyParam('payment_id');
		$verify_code          = SecurityHelper::getBodyParam('verify_code');        //TODO 余额支付时录入密码会返回验证码，验证码和预支付一起提交，后台再次验证，暂不使用
		$params['user_id']    = $user_id;
		$params['payment_id'] = $payment_id;


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
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/errand-buy-add-expense");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $errandRes['fee'];
				$payParams['transaction_no'] = $errandRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/errand-buy-add-expense");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}

	/**
	 * 用户过渡版本
	 * 配送到达并线下付款记录付款信息
	 */
	public static function arriveAndPayV10($provider_id)
	{

		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['fee']              = SecurityHelper::getBodyParam('fee');
		$params['provider_id']      = $provider_id;
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = SecurityHelper::getBodyParam('current_location');

		return ErrandBuyHelper::arriveAndPayFinish($params);
	}
}