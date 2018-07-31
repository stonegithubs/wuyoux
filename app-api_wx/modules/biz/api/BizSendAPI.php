<?php
/**
 * Created by PhpStorm.
 * User: JasonLeung
 * Date: 2018/1/29
 * Time: 11:24
 */

namespace api_wx\modules\biz\api;

use api_wx\modules\biz\helpers\WxBizHelper;
use api_wx\modules\biz\helpers\WxBizSendHelper;
use api_wx\modules\mpv1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\BizSendHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\BizHelper;
use common\helpers\utils\QueueHelper;

class BizSendAPI extends HelperBase
{
	//临时发单下单
	public static function orderNowV10($userId)
	{
		$result  = false;
		$bizInfo = WxBizHelper::getBizData($userId);

		if (!$bizInfo) {
			return $result;
		}


		$biz_address     = isset($bizInfo['biz_address']) ? $bizInfo['biz_address'] : null;
		$biz_address_ext = isset($bizInfo['biz_address_ext']) ? $bizInfo['biz_address_ext'] : null;
		$tag_id          = isset($bizInfo['tag_id']) ? $bizInfo['tag_id'] : null;
		$params          = [
			'user_id'        => $userId,
			'city_id'        => isset($bizInfo['city_id']) ? $bizInfo['city_id'] : 0,
			'area_id'        => isset($bizInfo['area_id']) ? $bizInfo['area_id'] : 0,
			'region_id'      => isset($bizInfo['region_id']) ? $bizInfo['region_id'] : 0,
			'start_location' => isset($bizInfo['biz_location']) ? $bizInfo['biz_location'] : null,
			'start_address'  => $biz_address . "," . $biz_address_ext,
			'user_mobile'    => isset($bizInfo['biz_mobile']) ? $bizInfo['biz_mobile'] : 0,
			'user_location'  => SecurityHelper::getBodyParam("user_location"),
			'user_address'   => SecurityHelper::getBodyParam("user_address"),
			'tmp_from'       => Ref::ORDER_FROM_MINI_APP,
			'cate_id'        => Ref::CATE_ID_FOR_BIZ_SEND,
			'content'        => BizHelper::getTagNameById($tag_id),
			'qty'            => SecurityHelper::getBodyParam("qty"),
		];

		$data = WxBizSendHelper::saveOrderNow($params);

		if ($data) {
			$result = $data;
			QueueHelper::bizSendOrder($data['batch_no']);
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

		return WxBizSendHelper::saveOrderNowForArea($params);
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