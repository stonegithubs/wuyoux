<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/16
 */

namespace api_user\modules\v1\api;

use api_user\modules\v1\helpers\StateCode;
use api_user\modules\v1\helpers\UserBizSendHelper;
use common\helpers\HelperBase;
use common\helpers\orders\BizSendHelper;
use common\helpers\security\SecurityHelper;

class BizSendAPI extends HelperBase
{
	/**
	 * 发单首页1.0
	 * @param $user_id
	 * @return array
	 */
	public static function HomeV10($user_id)
	{
		$result = [
			'data'    => ['status' => 0, 'guide' => 1],
			'message' => ''
		];

		if (!UserBizSendHelper::checkGuide($user_id)) {
			$result['data']['guide'] = 0;

			return $result;
		}

		if (BizSendHelper::checkBizOrderStatus($user_id)) {

			$result['data']['status'] = 1;
			$result['message']        = '请支付配送到达的订单才能继续下单';

			return $result;
		}

		if (BizSendHelper::getBizSendHome($user_id)) {

			$result['data']['status'] = 2;

			return $result;
		}

		return $result;
	}

	/**
	 * 发单首页1.1
	 * @param $user_id
	 * @return array
	 */
	public static function HomeV11($user_id)
	{
		$result = [
			'data'    => ['status' => 0, 'guide' => 1],
			'message' => ''
		];

		$bizDistrict                = BizSendHelper::getDistrict($user_id);
		$result['data']['district'] = $bizDistrict;

		if (!UserBizSendHelper::checkGuide($user_id)) {
			$result['data']['guide'] = 0;

			return $result;
		}

		if (BizSendHelper::checkBizOrderStatus($user_id)) {

			$result['data']['status'] = 1;
			$result['message']        = '请支付配送到达的订单才能继续下单';

			return $result;
		}

		if (BizSendHelper::getBizSendHome($user_id)) {

			$result['data']['status'] = 2;

			return $result;
		}

		return $result;
	}

	/**
	 * 取消临时发单
	 * @param $user_id
	 * @return mixed
	 */
	public static function cancelOrderV10($user_id)
	{
		$batch_no = SecurityHelper::getBodyParam('batch_no');
		$result   = UserBizSendHelper::cancelOrder($user_id, $batch_no);

		return $result;
	}

	/**
	 * 立即发单
	 * @param $user_id
	 * @return array|bool
	 */
	public static function orderNowV10($user_id)
	{
		$result = UserBizSendHelper::orderNow($user_id);

		return $result;
	}

	/**
	 * 实时检测接单情况
	 * @param $user_id
	 * @return array|bool
	 */
	public static function tmpCheckOrderV10($user_id)
	{
		$batch_no = SecurityHelper::getBodyParam("batch_no");
		$result   = UserBizSendHelper::getLastTmpOrder($user_id, $batch_no);

		return $result;
	}

	/**
	 * 订单任务页
	 * @return array|bool
	 */
	public static function userTaskV10()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'task';
		$result                 = BizSendHelper::userTaskAndDetail($params);

		return $result;
	}

	/**
	 * 订单详情页
	 * @return array|bool
	 */
	public static function userDetailV10()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'detail';
		$result                 = BizSendHelper::userTaskAndDetail($params);

		return $result;
	}

	/**
	 * 取消订单
	 * @param $user_id
	 * @return array|bool
	 */
	public static function userCancelV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$result             = UserBizSendHelper::userCancel($params);
		if ($result) {
			UserBizSendHelper::pushToProviderNotice($params['order_no'], UserBizSendHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $result);
		}

		return $result;
	}

	/**
	 * 删除订单
	 * @param $user_id
	 * @return array|bool
	 */
	public static function userDeleteV10($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$result             = UserBizSendHelper::userDelete($params);

		return $result;
	}

	/**
	 * 用户取消订单工作流
	 * @param $user_id
	 * @return array|bool
	 */
	public static function userCancelFlow($user_id)
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $user_id;
		$params['agreed']   = SecurityHelper::getBodyParam('agreed');
		$result             = UserBizSendHelper::userCancelFlow($params);
		if ($result) {
			UserBizSendHelper::pushToProviderNotice($params['order_no'], UserBizSendHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $result);
		}

		return $result;
	}

	/**
	 * 单张订单预支付
	 * @return array
	 */

	public static function prePaymentV10()
	{
		$result = UserBizSendHelper::prePayment();

		return $result;
	}

	/**
	 * 确认取消临时发单
	 * @param $user_id
	 * @return array|bool
	 */
	public static function tmpCancelConfirmV10($user_id)
	{
		$params['tmp_no']  = SecurityHelper::getBodyParam('tmp_no');
		$params['user_id'] = $user_id;
		$result            = UserBizSendHelper::userTmpOrderCancelConfirm($params);

		return $result;
	}

	/**
	 * 去支付的时候自动获取卡券信息
	 * @param $user_id
	 * @return array|bool
	 */
	public static function GetAutoCouponPaymentV10($user_id)
	{
		$params['user_id']  = $user_id;
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['card_id']  = SecurityHelper::getBodyParam('card_id', -1);
		$result             = UserBizSendHelper::getAutoCouponPayment($params);

		return $result;
	}

	/**
	 * 计算支付金额和订单数
	 * @param $user_id
	 * @return array|bool
	 */
	public static function getBatchPaymentDetailV10($user_id)
	{
		$params['user_id'] = $user_id;
		$result            = UserBizSendHelper::getBatchPaymentDetail($params);

		return $result;
	}

	/**
	 * 批量支付
	 * @param $user_id
	 * @return mixed
	 */
	public static function prePaymentBatchV10($user_id)
	{
		$params['user_id']    = $user_id;
		$params['payment_id'] = SecurityHelper::getBodyParam('payment_id');
		$orderData            = BizSendHelper::getPaymentCache($params);
		if (!$orderData) {
			$result['data'] = '';
			$result['code'] = StateCode::BIZ_SEND_PAY_BATCH_EXPIRE;

			return $result;
		}

		$data           = UserBizSendHelper::prePaymentBatch($orderData, $params);
		$result['data'] = $data['data'];
		$result['code'] = $data['code'];

		return $result;
	}

	/**
	 *    获取优惠券匹配列表
	 * @param $user_id
	 * @return mixed
	 */
	public static function getCouponMatchListV10($user_id)
	{
		$params['user_id'] = $user_id;
		if (!$orderData = BizSendHelper::getPaymentCache($params)) {
			$result['code'] = StateCode::BIZ_SEND_PAY_BATCH_EXPIRE;
			$result['data'] = '';

			return $result;
		}
		$result['data'] = BizSendHelper::getCouponMatchData($orderData, $user_id);
		$result['code'] = 0;

		return $result;
	}

	/**
	 * 智能获取优惠券匹配列表
	 * @param $user_id
	 * @return mixed
	 */
	public static function smartCouponCalV10($user_id)
	{
		$params['user_id']     = $user_id;
		$select_card           = SecurityHelper::getBodyParam("select_card");
		$params['select_card'] = json_decode($select_card, true);
		if (!$orderData = BizSendHelper::getPaymentCache($params)) {
			$result['code'] = StateCode::BIZ_SEND_PAY_BATCH_EXPIRE;
			$result['data'] = 0;

			return $result;
		}
		$result['data'] = BizSendHelper::smartCouponCal($orderData, $params);
		$result['code'] = 0;

		return $result;
	}

	public static function valuationDetailV10()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');

		return $result = UserBizSendHelper::getCalc($params);
	}

	/**
	 * 保存引导指南
	 * @param $user_id
	 * @return bool
	 */
	public static function saveGuideV10($user_id)
	{
		return $result = UserBizSendHelper::saveGuide($user_id);
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
		$data               = BizSendHelper::userConfirm($params);

		if ($data) {
			$params['current_page'] = "finish";
			$result                 = $data;
		}

		return $result;
	}

	public static function orderNowV11($userId)
	{
		$params = [
			'user_id'       => $userId,
			'user_location' => SecurityHelper::getBodyParam("user_location"),
			'user_address'  => SecurityHelper::getBodyParam("user_address"),
			'tmp_from'      => SecurityHelper::getBodyParam("order_from"),
			'delivery_area' => SecurityHelper::getBodyParam("delivery_area"),
			'qty'           => SecurityHelper::getBodyParam("area_qty"),
		];

		return UserBizSendHelper::saveOrderNowForArea($params);
	}

	public static function tmpViewV10($userId)
	{

		$params['tmp_no']  = SecurityHelper::getBodyParam('tmp_no');
		$params['user_id'] = $userId;

		return BizSendHelper::tmpOrderDetail($params);
	}

	/**
	 * 添加用户配送区域1.0
	 * @param $user_id
	 * @return mixed
	 */
	public static function addDistrictV10($user_id)
	{
		$district = SecurityHelper::getBodyParam('district');

		//配送区域不能为空
		if (!$district) {
			$result['code'] = StateCode::BIZ_SEND_ADD_DISTRICT_IS_NULL;

			return $result;
		}

		//配送区域是否存在
		if (BizSendHelper::checkDistrictExist($user_id, $district)) {
			$result['code'] = StateCode::BIZ_SEND_ADD_DISTRICT_EXIST;

			return $result;
		}

		//配送区域数量是否超限
		if (!BizSendHelper::checkDistrictNum($user_id)) {
			$result['code'] = StateCode::BIZ_SEND_ADD_DISTRICT_NUM;

			return $result;
		}

		//添加配送区域
		if (!BizSendHelper::addDistrict($user_id, $district)) {
			$result['code'] = StateCode::BIZ_SEND_ADD_DISTRICT;

			return $result;
		}
		$result['code'] = 0;

		return $result;
	}

	/**
	 * 删除用户配送区域1.0
	 * @param $user_id
	 * @return mixed
	 */
	public static function deleteDistrictV10($user_id)
	{
		$district = SecurityHelper::getBodyParam('district');

		if (!BizSendHelper::deleteDistrict($user_id, $district)) {
			$result['code'] = StateCode::BIZ_SEND_DELETE_DISTRICT;

			return $result;
		}
		$result['code'] = 0;

		return $result;
	}
}