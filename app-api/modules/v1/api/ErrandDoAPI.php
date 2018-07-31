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
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;

/**
 * 帮我办API版本控制
 * Class ErrandBuyAPI
 * @package api\modules\v1\api
 */
class ErrandDoAPI extends HelperBase
{
	/**
	 * 首页数据1.0
	 * @param $user_id
	 * @return array
	 */
	public static function IndexV10($user_id)
	{
		$userInfo      = UserHelper::getUserInfo($user_id);
		$checkOrder    = ErrandDoHelper::checkCanOrder($user_id);
		$user_location = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("user_location"));
		$result        = [
			'service_price' => ErrandDoHelper::getServicePrice($user_location, $user_id),        //TODO ?
			'use_mobile'    => isset($userInfo['mobile']) ? $userInfo['mobile'] : null,
			'order_no'      => $checkOrder['order_no'],
			"order_count"   => intval($checkOrder['order_count']),
		];

		return $result;
	}

	/**
	 * 指定地址返回计算结果给前端1.0
	 * @return array
	 */
	public static function getServicePriceV10($user_id)
	{
		$service_location      = AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("service_location"));
		$data['service_price'] = ErrandDoHelper::getServicePrice($service_location, $user_id);

		return $data;
	}

	/**
	 * 创建订单1.0
	 * @param $user_id
	 * @return array|bool
	 */
	public static function createOrderV10($user_id)
	{
		$result         = false;
		$service_qty    = SecurityHelper::getBodyParam('service_qty', 1);
		$orderAmount    = ErrandDoHelper::getServicePrice(AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("start_location")), $user_id) * $service_qty;
		$userInfo       = UserHelper::getUserInfo($user_id);
		$online_money   = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;  //在线宝余额
		$discount_money = OrderHelper::getOnlineDiscount($online_money);
		$user_city      = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
		$regionArr      = RegionHelper::getAddressIdByLocation(AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam('start_location')), $user_city);
		$params         = [
			"base"     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND_DO,
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
				'user_location'  => AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("user_location")),
				'user_address'   => SecurityHelper::getBodyParam("user_address"),
				'start_location' => AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("start_location")),
				'start_address'  => SecurityHelper::getBodyParam("start_address"),
				'end_location'   => AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("end_location")),
				'end_address'    => SecurityHelper::getBodyParam("end_address")
			],
			'errand'   => [
				'service_price'  => ErrandDoHelper::getServicePrice(AMapHelper::convert_baidu2Amap(SecurityHelper::getBodyParam("start_location")), $user_id),
				'service_time'   => strtotime(SecurityHelper::getBodyParam('service_time')),
				'errand_type'    => SecurityHelper::getBodyParam('errand_type', Ref::ERRAND_TYPE_DO),
				'errand_content' => SecurityHelper::getBodyParam('content'),
				'mobile'         => SecurityHelper::getBodyParam('user_mobile'),
				'first_fee'      => SecurityHelper::getBodyParam('first_fee'),
				'service_qty'    => SecurityHelper::getBodyParam('service_qty')
			]
		];

		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($params);
		if ($orderHelper->checkErrandParams()) {
			return $result;
		}
		$res = $orderHelper->save();
		if (is_array($res)) {

			$res['total_fee']      = sprintf("%.2f", SecurityHelper::getBodyParam('first_fee'));
			$res['online_money']   = $discount_money; //在线宝余额抵扣
			$res['card_available'] = CouponHelper::getOrderCardNum($res['order_no']); //订单可用优惠券数量
			$result                = $res;
		}

		return $result;
	}

	/**
	 * 订单计算价格1.0
	 * @param $user_id
	 * @return array|bool
	 */
	public static function orderCalcV10($user_id)
	{
		$userInfo               = UserHelper::getUserInfo($user_id);
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id', 0);
		$params['errand_type']  = SecurityHelper::getBodyParam('errand_type');
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['online_money'] = $online_money;

		return ErrandDoHelper::getCalc($params);
	}


	/**
	 * 用户详情和任务页
	 * @return array|bool
	 */
	public static function userDetailV10()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = SecurityHelper::getBodyParam('current_page');

		return ErrandDoHelper::userDetail($params);
	}

	/**
	 * 小帮任务页获取任务数据1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerTaskV10($provider_id)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;

		return ErrandDoHelper::workerTask($params);
	}

	/**
	 * 小帮详情页1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerDetailV10($provider_id)
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		$params['app_data']    = null;

		return ErrandDoHelper::workerDetail($params);
	}

	/**
	 * 用户确认1.0
	 * @param $user_id
	 * @return array|bool
	 */
	public static function userConfirmV10($user_id)
	{

		$result             = false;
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$data               = ErrandDoHelper::userConfirm($params);

		if ($data) {
			$params['current_page'] = "finish";
			ErrandDoHelper::pushToProviderNotice($params['order_no'], ErrandDoHelper::PUSH_PROVIDER_TYPE_CONFIRM, $params);
			$result = $data;
		}

		return $result;
	}


	/**
	 * 用户支付配送费用
	 * @param $user_id
	 * @return array
	 */
	public static function addFeeV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['fee']      = SecurityHelper::getBodyParam('fee');
		$params['user_id']  = $user_id;

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

			$total_fee = OrderHelper::getTotalFee($orderRes['order_id']);
			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("wxpay/errand-do-add-custom-fee");
				$wxRes                       = WxpayHelper::appOrder($payParams);
				$wxRes ? $payRes = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
				$total_fee += $orderRes['fee'];//这里总小费给前端查看，实际回调根据实际情况
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/errand-do-add-custom-fee");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $payRes = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
				$total_fee += $orderRes['fee'];//这里总小费给前端查看，实际回调根据实际情况
			}

			if ($result['code'] == 0) {
				$result['data'] = [
					'total_fee'    => sprintf("%.2f", $total_fee),
					'order_amount' => sprintf("%.2f", $orderRes['order_amount']),
					'data'         => $payRes,
					'payment_id'   => $payment_id
				];
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}
}