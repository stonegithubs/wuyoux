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
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;

/**
 * 帮我送API版本控制
 * Class ErrandBuyAPI
 * @package api\modules\v1\api
 */
class ErrandSendAPI extends HelperBase
{
	/**
	 * 首页数据1.0
	 * @param $user_id
	 * @return array
	 */
	public static function IndexV10($user_id)
	{
		$user_location = SecurityHelper::getBodyParam("user_location");    //参数
		$checkOrder    = ErrandSendHelper::checkCanOrder($user_id);
		$userInfo      = UserHelper::getUserInfo($user_id);
		$user_city_id  = isset($userInfo['city_id']) ? $userInfo['city_id'] : null;

		$result = [
			'cateList'    => ErrandSendHelper::getOrderCateList(),
			'use_mobile'  => isset($userInfo['mobile']) ? $userInfo['mobile'] : null,    //发货人电话
			'order_no'    => $checkOrder['order_no'],
			'order_count' => intval($checkOrder['order_count']),
			'low_price'   => ErrandSendHelper::getLowPrice($user_location, $user_city_id),    //最低金额
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

		return ErrandHelper::getRangePriceDataForAMap($start_location, $end_location, $userInfo, Ref::CATE_ID_FOR_ERRAND_SEND);
	}

	/**
	 * 创建订单1.0
	 * @param $user_id
	 * @return array|bool
	 */
	public static function createOrderV10($user_id,$user_city_id)
	{
		$result       = false;
		$orderAmount  = SecurityHelper::getBodyParam("service_price");
		$regionArr    = RegionHelper::getAddressIdByLocation(SecurityHelper::getBodyParam('start_location'), $user_city_id);

		$params = [
			"base"     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND_SEND,
				'city_id'     => $regionArr['city_id'],
				'region_id'   => $regionArr['region_id'],
				'area_id'     => $regionArr['area_id'],
				'order_from'  => SecurityHelper::getBodyParam("order_from"),
				'user_mobile' => SecurityHelper::getBodyParam("user_mobile"),        //发货人
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
				'errand_type'    => Ref::ERRAND_TYPE_SEND,
				'errand_content' => SecurityHelper::getBodyParam('content'),
				'mobile'         => SecurityHelper::getBodyParam('receiver_mobile'),    //收货人
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
			$res['order_amount']   = sprintf("%.2f", $res['order_amount']);                //订单金额
			$res['amount_payable'] = sprintf("%.2f", $amount['amount_payable']);        //订单实付
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
		$params['user_city']    = $userInfo['city_id'];
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id', 0);
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['online_money'] = $online_money;

		return ErrandSendHelper::getCalc($params);

	}

	/**
	 * 用户任务页1.0
	 * @return array|bool
	 */
	public static function userTaskV10()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'task';

		return ErrandSendHelper::userTaskAndDetail($params);
	}

	/**
	 * 用户详情页1.0
	 * @return array|bool
	 */
	public static function userDetailV10()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'detail';

		return ErrandSendHelper::userTaskAndDetail($params);
	}

	/**
	 * 小帮任务页获取任务数据1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerTaskV10($provider_id)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'task';

		return ErrandSendHelper::workerTaskAndDetail($params);
	}

	/**
	 * 小帮详情页1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function workerDetailV10($provider_id)
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']  = $provider_id;
		$params['current_page'] = 'detail';

		return ErrandSendHelper::workerTaskAndDetail($params);
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
		$data               = ErrandSendHelper::userConfirm($params);    //TODO
		if ($data) {
			$params['current_page'] = "finish";
			ErrandSendHelper::pushToProviderNotice($params['order_no'], ErrandSendHelper::PUSH_PROVIDER_TYPE_CONFIRM, $params);
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

		$orderRes   = ErrandSendHelper::addCustomFee($params);
		$payment_id = $orderRes['payment_id'];
		if ($orderRes) {

			$payRes = null;

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$isSuccess = ErrandSendHelper::addCustomFeeSuccess($orderRes['transaction_no'], date("YmdHis"), $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;
			}

			$total_fee = OrderHelper::getTotalFee($orderRes['order_id']);
			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/errand-send-add-custom-fee");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $payRes = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
				$total_fee += $orderRes['fee'];//这里总小费给前端查看，实际回调根据实际情况
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/errand-send-add-custom-fee");
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

	/**
	 * 用户确认1.0
	 * @param $provider_id
	 * @return array|bool
	 */
	public static function takePhotoV10($provider_id)
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['image_id']    = SecurityHelper::getBodyParam('image_id');
		$params['provider_id'] = $provider_id;

		return ErrandSendHelper::takePhoto($params);
	}

}