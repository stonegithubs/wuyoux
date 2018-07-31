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
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\orders\TripBikeHelper;
use common\helpers\orders\TripHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use common\models\util\HistoryAddress;
use yii\helpers\ArrayHelper;
use Yii;

class TripBikeAPI extends HelperBase
{

	//小帮出行首页
	public static function indexV10($user_id)
	{
		return TripHelper::checkOrder($user_id, Ref::CATE_ID_FOR_MOTOR, [Ref::ORDER_STATUS_DEFAULT, Ref::ORDER_STATUS_AWAITING_PAY]);   //检查是否有未完成的出行订单
	}

	// 价格预估
	public static function estimatePriceV10($user_id)
	{
		$startLocation = SecurityHelper::getBodyParam('start_location');
		$endLocation   = SecurityHelper::getBodyParam('end_location');
		$userInfo      = UserHelper::getUserInfo($user_id, 'uid, city_id');

		return TripBikeHelper::estimatePrice($startLocation, $endLocation, $userInfo, Ref::CATE_ID_FOR_MOTOR);
	}

	//用户历史输入地址
	public static function inputHistoryV10($user_id)
	{
		$params['location'] = AMapHelper::coordToStr(SecurityHelper::getBodyParam('user_location'));  //用户坐标
		$params['page']     = SecurityHelper::getBodyParam('page');
		$params['pageSize'] = SecurityHelper::getBodyParam('page_size');
		$res                = TripHelper::historyAddress($user_id, $params['page'], $params['pageSize'], Ref::ORDER_TYPE_TRIP);
		//没有历史地址则取周边商圈地址
		if ($res['pagination']['totalCount'] <= 0) {
			$result['type'] = 2;
			$result['data'] = AMapHelper::searchAround($params);
		} else {
			$result['type']       = 1;
			$result['pagination'] = $res['pagination']; //历史地址会有分页信息
			$result['data']       = $res['list'];
		}

		return $result;
	}

	/**
	 * 创建订单1.0
	 *
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public static function createOrderV10($user_id, $userInfo)
	{
		$result            = false;
		$estimate_distance = SecurityHelper::getBodyParam("estimate_distance");
		$user_city         = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
		$regionArr         = RegionHelper::getAddressIdByLocation(SecurityHelper::getBodyParam('start_location'), $user_city);
		$user_mobile       = isset($userInfo['mobile']) ? $userInfo['mobile'] : 0;

		$startLocation = SecurityHelper::getBodyParam('start_location');
		$endLocation   = SecurityHelper::getBodyParam('end_location');
		$cityPrice     = TripBikeHelper::getRangePriceDataForAMap($startLocation, $endLocation, $userInfo, Ref::CATE_ID_FOR_MOTOR);
		$order_amount  = $cityPrice['price'];

		$params = [
			"base"     => [
				'cate_id'      => Ref::CATE_ID_FOR_MOTOR,
				'city_id'      => $regionArr['city_id'],
				'region_id'    => $regionArr['region_id'],
				'area_id'      => $regionArr['area_id'],
				'order_from'   => SecurityHelper::getBodyParam("order_from"),
				'user_mobile'  => $user_mobile,
				'order_type'   => Ref::ORDER_TYPE_TRIP,
				'user_id'      => $user_id,
				'order_status' => Ref::ORDER_STATUS_DEFAULT,
			],
			'amount'   => [
				'order_amount' => $order_amount,
			],
			'location' => [
				'user_location'      => SecurityHelper::getBodyParam("user_location"),
				'user_address'       => SecurityHelper::getBodyParam("user_address"),
				'start_location'     => SecurityHelper::getBodyParam("start_location"),
				'start_address'      => SecurityHelper::getBodyParam("start_address"),
				'end_location'       => SecurityHelper::getBodyParam("end_location"),
				'end_address'        => SecurityHelper::getBodyParam("end_address"),
				'end_address_detail' => SecurityHelper::getBodyParam('end_address_detail'),
			],
			'trip'     => [
				'trip_status'       => Ref::TRIP_STATUS_WAIT,
				'trip_type'         => Ref::TRIP_TYPE_BIKE,
				'estimate_location' => SecurityHelper::getBodyParam("end_location"),
				'estimate_address'  => SecurityHelper::getBodyParam("end_address"),
				'estimate_amount'   => $order_amount,
				'estimate_distance' => $estimate_distance,
			]
		];
		//检查地址信息是否缺失
		foreach ($params['location'] as $value) {
			if (!isset($value)) {
				return $result;
			}
		}

		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($params);
		$res = $orderHelper->save();
		if (is_array($res)) {
			TripBikeHelper::saveHistoryAddress($params['location'], $user_id);
			QueueHelper::tripOrder($res['order_id']);
			QueueHelper::newOrderNotice($res['order_no'], 'trip');
			$result = $res;
		}

		return $result;
	}


	public static function userTaskV10($user_id)
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;

		return TripBikeHelper::userTask($params);
	}

	public static function userFinishV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;

		return TripBikeHelper::userFinish($params);
	}

	public static function userCancelV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;

		return TripBikeHelper::userCancel($params);
	}

	/**
	 * 通用预支付
	 * @param $user_id
	 * @return array
	 */
	public static function prePaymentV10($user_id)
	{
		$userInfo             = UserHelper::getUserInfo($user_id);
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['card_id']    = SecurityHelper::getBodyParam('card_id');
		$paymentId            = SecurityHelper::getBodyParam('payment_id');
		$verifyCode           = SecurityHelper::getBodyParam('verify_code');
		$params['payment_id'] = $paymentId;

		$result = [
			'code' => 0,
			'data' => null
		];

		if ($paymentId == Ref::PAYMENT_TYPE_BALANCE) {//余额支付优先验证支付密码

			if (!SecurityHelper::confirmPayPassword($user_id, $verifyCode)) {    //验证失败
				$result['code'] = StateCode::PAY_PWD_FAIL;

				return $result;
			}
		}

		$orderHelper = new OrderHelper();
		$orderRes    = $orderHelper->updatePrepayment($params);
		if ($orderRes) {

			if ($paymentId == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = TripBikeHelper::tripPaymentSuccess($orderRes['transaction_no'], $trade_no, $paymentId, $orderRes['fee'], "余额支付");

				$isSuccess ? "" : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败
			}

			if ($paymentId == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['body']           = "无忧帮帮-小帮出行";
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/trip-payment");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($paymentId == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['body']           = "无忧帮帮-小帮出行";
				$payParams['subject']        = "无忧帮帮-小帮出行";
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/trip-payment");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}


		} else {
			$result['code'] = $paymentId == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}

	/**
	 * 通用抢单接口
	 * @param $user_id
	 * @param $provider_id
	 * @return bool
	 */
	public static function robbingV10($user_id, $provider_id)
	{
		$userInfo                    = UserHelper::getUserInfo($user_id);
		$params['order_no']          = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']       = $provider_id;
		$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = isset($userInfo['mobile']) ? $userInfo['mobile'] : null;
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');

		return ErrandHelper::saveRobbing($params);
	}

	/**
	 * 通用用户删除订单
	 * @param $user_id
	 * @return array|bool
	 */
	public static function userDeleteV10($user_id)
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;

		return TripHelper::userDelete($params);
	}

	//取消订单信息
	public static function userCancelIndexV10()
	{
		$result['title']  = '取消订单';
		$result['option'] = [
			['id' => 1, 'value' => '行程有变，暂时不需要用车', 'type' => 0],
			['id' => 2, 'value' => '等待小帮时间过长', 'type' => 0],
			['id' => 3, 'value' => '平台派单太远', 'type' => 0],
			['id' => 4, 'value' => '小帮以各种理由不来接我', 'type' => 0],
			['id' => 5, 'value' => '联系不上小帮', 'type' => 0],
			['id' => 6, 'value' => '小帮找不到上车点', 'type' => 0],
			['id' => 7, 'value' => '小帮服务态度恶劣', 'type' => 0],
			['id' => 8, 'value' => '小帮迟到', 'type' => 0],
			['id' => 9, 'value' => '其他', 'type' => 1],
		];

		return $result;
	}

	//取消订单保存
	public static function userCancelSaveV10($userId)
	{
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['content_id'] = SecurityHelper::getBodyParam('content_id');
		$params['content']    = SecurityHelper::getBodyParam('content');

		$result = TripBikeHelper::userCancelSave($params, $userId);
		if ($result) {
			$result['robbed'] == Ref::ORDER_ROBBED ?
				TripBikeHelper::pushToProviderNotice($result['order_id'], TripBikeHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $result) : null;
		}

		return $result;

	}

	//投诉小帮信息
	public static function complaintIndexV10()
	{
		$result['title']  = '投诉小帮';
		$result['option'] = [
			['id' => 1, 'value' => '小帮速度太慢', 'type' => 0],
			['id' => 2, 'value' => '需要等待小帮时间太久', 'type' => 0],
			['id' => 3, 'value' => '小帮强收小费', 'type' => 0],
			['id' => 4, 'value' => '小帮服务态度很差', 'type' => 0],
			['id' => 5, 'value' => '小帮损坏物品或商品', 'type' => 0],
			['id' => 6, 'value' => '小帮故意多收商品费用', 'type' => 0],
			['id' => 7, 'value' => '小帮没有按要求送达具体位置', 'type' => 0],
			['id' => 8, 'value' => '小帮联系不上', 'type' => 0],
			['id' => 9, 'value' => '其他', 'type' => 1],
		];

		return $result;
	}

	//投诉小帮保存
	public static function complaintSaveV10()
	{
		$order_no            = SecurityHelper::getBodyParam('order_no');
		$param['content_id'] = SecurityHelper::getBodyParam('content_id');
		$param['content']    = SecurityHelper::getBodyParam('content');

		return TripBikeHelper::complaintSave($order_no, $param);
	}

	//计价详情
	public static function priceDetailV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['card_id']  = SecurityHelper::getBodyParam('card_id', '-1');

		return TripBikeHelper::priceDetail($user_id, $params);
	}

	//加价
	public static function addPriceV10()
	{
		$addPrice = SecurityHelper::getBodyParam('price');  //加价金额
		$orderNo  = SecurityHelper::getBodyParam('order_no');

		return TripBikeHelper::addPrice($orderNo, $addPrice);
	}

	//催一催小帮
	public static function pressProviderV10($userId)
	{
		$orderNo = SecurityHelper::getBodyParam('order_no');
		$res     = TripBikeHelper::pressProvider($orderNo, $userId);
		if ($res) {
			TripBikeHelper::pushToProviderNotice($res['order_id'], TripBikeHelper::PUSH_PROVIDER_TYPE_PRESS, $res);
		}

		return $res;
	}

	//打赏小帮
	public static function addRewardV10($userId)
	{

		$param['order_no']   = SecurityHelper::getBodyParam('order_no');
		$paymentId           = SecurityHelper::getBodyParam('payment_id');
		$param['payment_id'] = $paymentId;
		$param['fee']        = SecurityHelper::getBodyParam('fee');
		$verifyCode          = SecurityHelper::getBodyParam('verify_code');

		$result = [
			'code' => 0,
			'data' => null
		];

		if ($paymentId == Ref::PAYMENT_TYPE_BALANCE) {//余额支付优先验证支付密码

			if (!SecurityHelper::confirmPayPassword($userId, $verifyCode)) {    //验证失败
				$result['code'] = StateCode::PAY_PWD_FAIL;

				return $result;
			}
		}

		$orderRes = TripBikeHelper::addRewardPrePayment($param);
		if ($orderRes) {

			if ($paymentId == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$isSuccess = TripBikeHelper::addRewardSuccess($orderRes['transaction_no'], date("YmdHis"), $paymentId, $orderRes['fee'], "余额支付");
				$isSuccess ? "" : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败
			}

			if ($paymentId == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/trip-add-reward");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($paymentId == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/trip-add-reward");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

		} else {
			$result['code'] = ($paymentId == Ref::PAYMENT_TYPE_BALANCE) ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}

	//获取支付信息
	public static function getPaymentDetailV10($userId)
	{
		$orderNo = SecurityHelper::getBodyParam('order_no');
		$cardId  = SecurityHelper::getBodyParam('card_id', '-1');

		$params = [
			'order_no' => $orderNo,
			'user_id'  => $userId,
			'card_id'  => $cardId
		];

		return TripBikeHelper::getPaymentDetail($params);
	}

	//清除历史地址记录
	public static function clearHistory($user_id)
	{
		return Yii::$app->db->createCommand()->delete(HistoryAddress::tableName(), 'user_id = ' . $user_id)->execute();
	}
}