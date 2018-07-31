<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/31
 */

namespace api_worker\modules\v1\api;

use api_worker\modules\v1\helpers\StateCode;
use api_worker\modules\v1\helpers\WorkerOrderHelper;
use common\components\Ref;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\UrlHelper;
use yii;

class ShopAPI extends HelperBase
{
	/**
	 * 小帮出行入驻
	 * @param $userInfo
	 * @return bool|int
	 */
	public static function enterMotorShopV10($userInfo)
	{
		$result = false;
		//获取参数
		$params = self::enterMotorShopParam($userInfo);

		if (!empty($params)) {
			$result = ShopHelper::enterMotoShop($params['shop'], $params['driver_license_pic'], $params['travel_license_pic'], $params['identity_card_pic']);
		}

		return $result;
	}

	/**
	 * 小帮出行入驻 数据验证
	 */
	private static function enterMotorShopParam($userInfo)
	{
		$result = false;
		$params = [
			'type_second'   => Ref::CATE_ID_FOR_MOTOR,                                        //店铺类型
			'type_first'    => Ref::CATE_ID_FOR_TRAFFIC_TRAVEL,
			'cityname'      => SecurityHelper::getBodyParam("cityname"),                            //所在城市
			'area_id'       => SecurityHelper::getBodyParam("area_id"),                                //地域ID
			'driver_num'    => SecurityHelper::getBodyParam("driver_num"),                            //驾驶证号码
			'travel_num'    => SecurityHelper::getBodyParam("travel_num"),                            //行驶证号码
			'photo'         => SecurityHelper::getBodyParam("photo"),                                //照片{"driver_license_pic_pos":"","driver_license_pic_opp":"","travel_license_pic_pos":"","travel_license_pic_opp":"","id_card_pic_pos":"","id_card_pic_opp":"","hand_id_card_pic":""}
			'shop_address'  => SecurityHelper::getBodyParam("shop_address"),                        //商铺地址
			'shop_location' => SecurityHelper::getBodyParam("shop_location")                           //商家姓名
		];

		if (empty($params['cityname']) || empty($params['area_id']) || empty($params['photo'])) {
			return false;
		}

		if (is_array($params['photo'])) {
			return false;
		}

		//获取城市ID
		$city_id = RegionHelper::getRegionId($params['cityname']);
		if (empty($city_id)) return $result;

		$province_id = RegionHelper::getProvinceId($city_id);
		//END

		//重新设定添加数据参数
		$shop_location = json_decode($params['shop_location']);
		$shop          = [
			'uid'                => $userInfo['uid'],
			'utel'               => $userInfo['mobile'],
			'type_first'         => $params['type_first'],
			'type_second'        => $params['type_second'],
			'first_cateid'       => $params['type_second'],
			'czlx'               => 1,
			'remove'             => 1,
			'province_id'        => $province_id,
			'city_id'            => $city_id,
			'area_id'            => $params['area_id'],
			'range'              => 3,
			'shops_province'     => $params['cityname'],
			'shops_address'      => $params['shop_address'],
			'shops_location'     => str_replace('"', "", $params['shop_location']),
			'shops_location_lat' => $shop_location[0],
			'shops_location_lng' => $shop_location[1],
			'create_time'        => time(),
		];

		$photo = json_decode($params['photo'], true);

		if (empty($photo['driver_license_pic_pos']) || empty($photo['driver_license_pic_opp'])
			|| empty($photo['travel_license_pic_pos']) || empty($photo['travel_license_pic_opp'])
			|| empty($photo['id_card_pic_pos']) || empty($photo['id_card_pic_opp'])
			|| empty($photo['hand_id_card_pic'])
		) {
			return false;
		}

		$driver_license_pic = ['driver_license_pic_pos' => $photo['driver_license_pic_pos'], 'driver_license_pic_opp' => $photo['driver_license_pic_opp'], 'driver_num' => $params['driver_num']];
		$travel_license_pic = ['travel_license_pic_pos' => $photo['travel_license_pic_pos'], 'travel_license_pic_opp' => $photo['travel_license_pic_opp'], 'travel_num' => $params['travel_num']];
		$identity_card_pic  = ['id_card_pic_pos' => $photo['id_card_pic_pos'], 'id_card_pic_opp' => $photo['id_card_pic_opp'], 'hand_id_card_pic' => $photo['hand_id_card_pic']];
		$result             = ['shop' => $shop, 'driver_license_pic' => $driver_license_pic, 'travel_license_pic' => $travel_license_pic, 'identity_card_pic' => $identity_card_pic];

		return $result;
	}

	/**
	 * 小帮快送入驻
	 * @param $userInfo
	 * @return bool|int
	 */
	public static function enterErrandShop($userInfo)
	{
		$result = false;
		//获取参数
		$params = self::enterErrandShopParam($userInfo);

		if (!empty($params)) {
			//添加数据
			$result = ShopHelper::enterErrandShop($params['shop'], $params['identity_card_pic']);
		}

		return $result;
	}

	/**
	 * 小帮快送入驻 数据验证
	 */
	private static function enterErrandShopParam($userInfo)
	{
		$result = false;
		$params = [
			'type_second'   => Ref::CATE_ID_FOR_ERRAND,                                        //店铺类型
			'type_first'    => Ref::CATE_ID_FOR_TRAFFIC_TRAVEL,
			'cityname'      => SecurityHelper::getBodyParam("cityname"),                            //所在城市
			'area_id'       => SecurityHelper::getBodyParam("area_id"),                                //地域ID
			'driver_num'    => SecurityHelper::getBodyParam("driver_num"),                            //驾驶证号码
			'travel_num'    => SecurityHelper::getBodyParam("travel_num"),                            //行驶证号码
			'card_num'      => SecurityHelper::getBodyParam("card_num"),                              //身份证号码
			'photo'         => SecurityHelper::getBodyParam("photo"),                                //照片{"driver_license_pic_pos":"","driver_license_pic_opp":"","travel_license_pic_pos":"","travel_license_pic_opp":"","id_card_pic_pos":"","id_card_pic_opp":"","hand_id_card_pic":""}
			'shop_address'  => SecurityHelper::getBodyParam("shop_address"),                        //商铺地址
			'shop_location' => SecurityHelper::getBodyParam("shop_location")                           //商家姓名
		];

		if (empty($params['cityname']) || empty($params['area_id']) || empty($params['photo'])) {
			return false;
		}

		if (is_array($params['photo'])) {
			return false;
		}

		//获取城市ID
		$city_id = RegionHelper::getRegionId($params['cityname']);
		if (empty($city_id)) return $result;

		$province_id = RegionHelper::getProvinceId($city_id);
		//END

		//重新设定添加数据参数
		$shop_location = json_decode($params['shop_location']);
		$shop          = [
			'uid'                => $userInfo['uid'],
			'utel'               => $userInfo['mobile'],
			'type_first'         => $params['type_first'],
			'type_second'        => $params['type_second'],
			'first_cateid'       => $params['type_second'],
			'czlx'               => 1,
			'remove'             => 1,
			'province_id'        => $province_id,
			'city_id'            => $city_id,
			'area_id'            => $params['area_id'],
			'range'              => 3,
			'shops_province'     => $params['cityname'],
			'shops_address'      => $params['shop_address'],
			'shops_location'     => str_replace('"', "", $params['shop_location']),
			'shops_location_lat' => $shop_location[0],
			'shops_location_lng' => $shop_location[1],
			'create_time'        => time(),
		];


		$photo = json_decode($params['photo'], true);

		if (empty($photo['id_card_pic_pos']) || empty($photo['id_card_pic_opp'])
			|| empty($photo['hand_id_card_pic'])
		) {
			return false;
		}
		$identity_card_pic = ['id_card_pic_pos' => $photo['id_card_pic_pos'], 'id_card_pic_opp' => $photo['id_card_pic_opp'], 'hand_id_card_pic' => $photo['hand_id_card_pic'], 'card_num' => $params['card_num']];
		$result            = ['shop' => $shop, 'identity_card_pic' => $identity_card_pic];

		return $result;
	}

	/**
	 * 电动车入驻
	 * @param $userInfo
	 * @return bool|int
	 */
	public static function enterElectroShopV10($userInfo)
	{
		$result = false;
		//获取参数
		$params = self::enterElectroShopParam($userInfo);

		if (!empty($params)) {
			$result = ShopHelper::enterElectroShop($params['shop'], $params['other'], $params['car'], $params['id_card']);
		}

		return $result;
	}

	/**
	 * 电动车入驻 数据验证
	 */
	private static function enterElectroShopParam($userInfo)
	{
		$result = false;
		$params = [
			'type_second'   => Ref::CATE_ID_FOR_ERRAND,                                        //店铺类型
			'type_first'    => SecurityHelper::getBodyParam("type_first"),
			'cityname'      => SecurityHelper::getBodyParam("cityname"),                            //所在城市
			'area_id'       => SecurityHelper::getBodyParam("area_id"),                                //地域ID
			'photo'         => SecurityHelper::getBodyParam("photo"),                                //照片{"driver_license_pic_pos":"","driver_license_pic_opp":"","travel_license_pic_pos":"","travel_license_pic_opp":"","id_card_pic_pos":"","id_card_pic_opp":"","hand_id_card_pic":""}
			'shop_address'  => SecurityHelper::getBodyParam("shop_address"),                        //商铺地址
			'shop_location' => SecurityHelper::getBodyParam("shop_location")                           //商家姓名
		];

		if (empty($params['cityname']) || empty($params['area_id']) || empty($params['photo'])) {
			return false;
		}

		if (is_array($params['photo'])) {
			return false;
		}

		//获取城市ID
		$city_id = RegionHelper::getRegionId($params['cityname']);
		if (empty($city_id)) return $result;

		$province_id = RegionHelper::getProvinceId($city_id);
		//END

		//重新设定添加数据参数
		$shop_location = json_decode($params['shop_location']);
		$shop          = [
			'uid'                => $userInfo['uid'],
			'utel'               => $userInfo['mobile'],
			'type_first'         => $params['type_first'],
			'type_second'        => $params['type_second'],
			'czlx'               => 1,
			'remove'             => 1,
			'province_id'        => $province_id,
			'city_id'            => $city_id,
			'area_id'            => $params['area_id'],
			'range'              => 3,
			'shops_province'     => $params['cityname'],
			'shops_address'      => $params['shop_address'],
			'shops_location'     => str_replace('"', "", $params['shop_location']),
			'shops_location_lat' => $shop_location[0],
			'shops_location_lng' => $shop_location[1],
			'create_time'        => time(),
		];

		$photo = json_decode($params['photo'], true);

		if (empty($photo['id_card_pos']) || empty($photo['id_card_opp'])
			|| empty($photo['hand_id_card']) || empty($photo['other_pos'])
			|| empty($photo['other_opp']) || empty($photo['car'])
			|| empty($photo['car_man'])
		) {
			return false;
		}

		$id_card = ['id_card_pos' => $photo['id_card_pos'], 'id_card_opp' => $photo['id_card_opp'], 'hand_id_card' => $photo['hand_id_card']];
		$other   = ['other_pos' => $photo['other_pos'], 'other_opp' => $photo['other_opp']];
		$car     = ['car' => $photo['car'], 'car_man' => $photo['car_man']];
		$result  = ['shop' => $shop, 'id_card' => $id_card, 'other' => $other, 'id_card' => $id_card, 'car' => $car];

		return $result;
	}


	/**
	 * 商家入驻 验证字段 version 1.0
	 */
	public static function enterShopParamsV10($userInfo)
	{
		$result = false;
		$params = [
			'type_second'   => SecurityHelper::getBodyParam("type_second"),                                        //店铺类型
			'type_first'    => SecurityHelper::getBodyParam("type_first"),
			'cityname'      => SecurityHelper::getBodyParam("cityname"),                            //所在城市
			'area_id'       => SecurityHelper::getBodyParam("area_id"),                                //地域ID
			'driver_num'    => SecurityHelper::getBodyParam("driver_num"),                            //驾驶证号码
			'travel_num'    => SecurityHelper::getBodyParam("travel_num"),                            //行驶证号码
			'photo'         => SecurityHelper::getBodyParam("photo"),                                //照片{"driver_license_pic_pos":"","driver_license_pic_opp":"","travel_license_pic_pos":"","travel_license_pic_opp":"","id_card_pic_pos":"","id_card_pic_opp":"","hand_id_card_pic":""}
			'shop_address'  => SecurityHelper::getBodyParam("shop_address"),                        //商铺地址
			'shop_location' => SecurityHelper::getBodyParam("shop_location")                           //商家姓名
		];

		if (empty($params['cityname']) || empty($params['area_id']) || empty($params['photo'])) {
			return false;
		}

		if (is_array($params['photo'])) {
			return false;
		}

		//获取城市ID
		$city_id = RegionHelper::getRegionId($params['cityname']);
		if (empty($city_id)) return $result;

		$province_id = RegionHelper::getProvinceId($city_id);
		//END

		//重新设定添加数据参数
		$shop_location = json_decode($params['shop_location']);
		$shop          = [
			'uid'                => $userInfo['uid'],
			'utel'               => $userInfo['mobile'],
			'type_first'         => $params['type_first'],
			'type_second'        => $params['type_second'],
			'first_cateid'       => $params['type_second'],
			'czlx'               => 1,
			'remove'             => 1,
			'province_id'        => $province_id,
			'city_id'            => $city_id,
			'area_id'            => $params['area_id'],
			'range'              => 3,
			'shops_province'     => $params['cityname'],
			'shops_address'      => $params['shop_address'],
			'shops_location'     => $params['shop_location'],
			'shops_location_lat' => $shop_location[0],
			'shops_location_lng' => $shop_location[1],
			'create_time'        => time(),
		];


		$photo = json_decode($params['photo'], true);

		if ($params['type_second'] == Ref::CATE_ID_FOR_MOTOR) {
			if (empty($photo['driver_license_pic_pos']) || empty($photo['driver_license_pic_opp'])
				|| empty($photo['travel_license_pic_pos']) || empty($photo['travel_license_pic_opp'])
				|| empty($photo['id_card_pic_pos']) || empty($photo['id_card_pic_opp'])
				|| empty($photo['hand_id_card_pic'])
			) {
				return false;
			}

			$driver_license_pic = ['driver_license_pic_pos' => $photo['driver_license_pic_pos'], 'driver_license_pic_opp' => $photo['driver_license_pic_opp'], 'driver_num' => $params['driver_num']];
			$travel_license_pic = ['travel_license_pic_pos' => $photo['travel_license_pic_pos'], 'travel_license_pic_opp' => $photo['travel_license_pic_opp'], 'travel_num' => $params['travel_num']];
			$identity_card_pic  = ['id_card_pic_pos' => $photo['id_card_pic_pos'], 'id_card_pic_opp' => $photo['id_card_pic_opp'], 'hand_id_card_pic' => $photo['hand_id_card_pic']];
			$result             = ['shop' => $shop, 'driver_license_pic' => $driver_license_pic, 'travel_license_pic' => $travel_license_pic, 'identity_card_pic' => $identity_card_pic];

		} elseif ($params['type_second'] == Ref::CATE_ID_FOR_ERRAND) {
			if (empty($photo['id_card_pic_pos']) || empty($photo['id_card_pic_opp'])
				|| empty($photo['hand_id_card_pic'])
			) {
				return false;
			}
			$identity_card_pic = ['id_card_pic_pos' => $photo['id_card_pic_pos'], 'id_card_pic_opp' => $photo['id_card_pic_opp'], 'hand_id_card_pic' => $photo['hand_id_card_pic']];
			$result            = ['shop' => $shop, 'identity_card_pic' => $identity_card_pic];
		} else {
			//其他分类,后期处理
		}


		//end

		return $result;
	}

	/**
	 * 获取城市ID
	 *
	 * @param $cityname        城市名
	 *
	 * @return mixed
	 */
	public function getCityId($cityname)
	{
		$cityname = str_replace('市', '', $cityname);

		return RegionHelper::getCityId($cityname);
	}

	/**
	 * 商家-接单类型
	 */
	public static function getShopType($shops_id)
	{
		$result   = false;
		$data     = [];
		$shopData = UserHelper::getShopInfo($shops_id, ['type_second', 'first_cateid', 'range']);

		if ($shopData) {
			$chooseTypeArr = explode(',', $shopData['type_second']);

			//默认有小帮快送
			$cate[] = [
				'name'    => CateListHelper::getCategoryName(Ref::CATE_ID_FOR_ERRAND),
				'value'   => Ref::CATE_ID_FOR_ERRAND,
				'checked' => in_array(Ref::CATE_ID_FOR_ERRAND, $chooseTypeArr) ? 1 : 0,
			];

			if ($shopData['first_cateid'] == Ref::CATE_ID_FOR_MOTOR) {
				$cate[] = [
					'name'    => CateListHelper::getCategoryName(Ref::CATE_ID_FOR_MOTOR),
					'value'   => Ref::CATE_ID_FOR_MOTOR,
					'checked' => in_array(Ref::CATE_ID_FOR_MOTOR, $chooseTypeArr) ? 1 : 0,
				];    //小帮出行
			}

			$data['cate'] = $cate;
			for ($i = 1; $i <= Ref::SHOP_MAX_RANGE; $i++) {

				$data['sel_range'][] = [
					'value'   => $i,
					'checked' => ($i == $shopData['range']) ? 1 : 0
				];
			}
		}

		if (!empty($data))
			$result = $data;

		return $result;
	}

	/**
	 * 更新商家接单类型,接单距离
	 * @param $shops_id
	 * @param $shop_cate
	 * @param $range
	 */
	public static function updateShopType($shops_id)
	{
		$result    = false;
		$shop_cate = SecurityHelper::getBodyParam("shop_cate");
		$range     = SecurityHelper::getBodyParam("range");
		if (empty($shops_id) || empty($shop_cate) || empty($range))
			return $result;
		$params = ['range' => $range, 'type_second' => trim(rtrim($shop_cate, ","), ",")];

		return ShopHelper::update($shops_id, $params);

	}

	/**
	 * 小帮收入首页
	 * @param $provider_id
	 * @return bool
	 */
	public static function incomeDataV10($provider_id)
	{
		$result     = false;
		$field      = ['shops_money', 'shops_historymoney', 'shops_outmoney', 'brankid', 'zfbname', 'zfbnum'];
		$shops_info = UserHelper::getShopInfo($provider_id, $field);

		if ($shops_info) {
			$account_info = ShopHelper::getThawAccount($provider_id);

			$result['balance']          = $shops_info['shops_money'];//余额
			$result['income_amount']    = $shops_info['shops_historymoney'];//收入总额
			$result['withdraw_amount']  = $shops_info['shops_outmoney'];//提现总额
			$result['available_amount'] = $shops_info['shops_money'];//可提现额度
			$result['binding_text']     = isset($account_info['binding_text']) ? $account_info['binding_text'] : 0;//提现绑定的账户文本
			$result['binding_id']       = isset($account_info['binding_id']) ? $account_info['binding_id'] : 0;//提现账户ID
			$result['is_binding']       = isset($account_info['is_binding']) ? 1 : 0;//供前端判断是否绑定
		}

		return $result;
	}

	/**
	 * 增加提现账户
	 * @param $provider_id
	 * @return int
	 */
	public static function drawingWayV10($provider_id)
	{
		$params['account_type'] = SecurityHelper::getBodyParam('account_type');
		$params['account']      = SecurityHelper::getBodyParam('account');
		$params['real_name']    = SecurityHelper::getBodyParam('real_name');
		$params['account_name'] = SecurityHelper::getBodyParam('account_name');
		$params['provider_id']  = $provider_id;

		return $result = ShopHelper::addDrawAway($params);
	}

	/**
	 * 删除提现账户
	 * @param $provider_id
	 * @return int
	 */
	public static function drawingDeleteV10($provider_id)
	{
		$params = [
			'provider_id' => $provider_id,
			'account_id'  => SecurityHelper::getBodyParam('account_id'),
		];

		return $result = ShopHelper::deleteDrawAway($params);
	}

	/**
	 * 检查提现账户是否存在
	 * @param $provider_id
	 * @return bool
	 */
	public static function checkAccountExist($provider_id)
	{
		$params = [
			'provider_id' => $provider_id,
			'account'     => SecurityHelper::getBodyParam('account'),
		];
		$data   = ShopHelper::checkAccountExist($params);

		return $result = $data ? true : false;

	}

	/**
	 * 提现账户列表
	 * @param $provider_id
	 * @return bool
	 */
	public static function drawingListV10($provider_id)
	{
		$result = false;
		$params = [
			'provider_id' => $provider_id,
			'type'        => SecurityHelper::getBodyParam('type'),
		];
		$data   = ShopHelper::drawingList($params);
		if ($data) {
			$result = $data;
		}

		return $result;
	}

	/**
	 * 小帮上班,下班,刷新坐标 使用百度坐标系，前端也是传百度坐标过来
	 * @param $provider_id
	 * @return bool
	 */
	public static function shopOnlineMapV10($provider_id, $type = 'online')
	{
		$result = false;

		$user_location               = SecurityHelper::getBodyParam("user_location");
		$params['shops_address']     = SecurityHelper::getBodyParam("user_address");
		$params['shop_login_status'] = $type == 'online' ? true : false;
		$params['shops_location']    = $user_location;

		if (!is_null(json_decode($user_location))) {
			$arr                          = json_decode($user_location);
			$params['shops_location_lng'] = current($arr);
			$params['shops_location_lat'] = end($arr);
			$result                       = ShopHelper::update($provider_id, $params);
		}

		if ($type == 'online') {

			AMapHelper::poiOnlineLBS($provider_id, 3); //更新坐标
		} else {
			AMapHelper::poiOfflineLBS($provider_id); //更新坐标
		}

		return $result;
	}

	/**
	 * 小帮上班,下班,刷新坐标 使用高德坐标系
	 * @param $provider_id
	 * @return bool
	 */
	public static function shopOnlineMapV11($provider_id, $type = 'online')
	{
		$result = false;

		$user_location               = SecurityHelper::getBodyParam("user_location");
		$params['shops_address']     = SecurityHelper::getBodyParam("user_address");
		$params['shop_login_status'] = $type == 'online' ? true : false;
		$params['shops_location']    = $user_location;

		if (!is_null(json_decode($user_location))) {
			$arr                          = json_decode($user_location);
			$params['shops_location_lng'] = current($arr);
			$params['shops_location_lat'] = end($arr);
			$result                       = ShopHelper::update($provider_id, $params);
		}

		if ($type == 'online') {

			AMapHelper::poiOnlineLBS($provider_id); //更新坐标
		} else {
			AMapHelper::poiOfflineLBS($provider_id); //更新坐标
		}

		return $result;
	}

	/**
	 * 绑定提现账户
	 * @param $provider_id
	 * @return bool|int
	 */
	public static function drawingBindV10($provider_id)
	{
		$params = [
			'account_id'  => SecurityHelper::getBodyParam('account_id'),
			'provider_id' => $provider_id,
		];

		return $result = ShopHelper::drawingBind($params);

	}

	/**
	 * 申请提现
	 * @param $provider_id
	 * @return bool|int
	 */
	public static function drawingSaveV10($provider_id)
	{
		$params = [
			'binding_id'  => SecurityHelper::getBodyParam('binding_id'),
			'provider_id' => $provider_id,
			'money'       => SecurityHelper::getBodyParam('money'),
		];
		$data   = ShopHelper::drawingSave($params);

		return $data;
	}


	/**
	 * 获取入驻参数
	 */
	public static function applyIndex()
	{
		//1.获取对应的参数
		//2.获取可入驻分类
		$city_id = RegionHelper::getCityId(SecurityHelper::getBodyParam('cityname'));

		return ShopHelper::applyCategories($city_id);
	}

	/**
	 * 缴纳保证金
	 * @param $provider_id
	 * @param $user_id
	 * @return array
	 */
	public static function bailPayV10($provider_id, $user_id)
	{
		$params  = [
			'bail_money'  => SecurityHelper::getBodyParam('bail_money'),
			'provider_id' => $provider_id,
			'payment_id'  => SecurityHelper::getBodyParam('payment_id'),
			'user_id'     => $user_id,
		];
		$result  = [
			'code' => 0,
			'data' => null,
		];
		$bailRes = ShopHelper::bailPay($params);
		if ($bailRes) {
			if ($params['payment_id'] == Ref::PAYMENT_TYPE_BALANCE) {
				$trade_no  = date("YmdHis");
				$isSuccess = ShopHelper::bailPaySuccess($bailRes['transaction_no'], $trade_no, $bailRes['fee'], "余额支付");
				$isSuccess ? '' : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败
			}


			if ($params['payment_id'] == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $bailRes['fee'];
				$payParams['transaction_no'] = $bailRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("worker-wxpay/bail-pay");
				$payParams['body']           = "无忧帮帮-保证金缴纳";
				$payParams['detail']         = "无忧帮帮-保证金缴纳";
				$wxRes                       = WxpayHelper::workerAppOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($params['payment_id'] == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $bailRes['fee'];
				$payParams['transaction_no'] = $bailRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/bail-pay");
				$payParams['body']           = "无忧帮帮-保证金缴纳";
				$payParams['subject']        = "无忧帮帮-保证金缴纳";
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}
		}

		return $result;
	}


	/**
	 * 商家首页
	 * @version v10
	 * @param $provider_id
	 */
	public static function homeDataV10($provider_id)
	{
		$result   = false;
		$shopInfo = UserHelper::getShopInfo($provider_id, ['shops_name', 'shops_photo', 'type_first', 'type_second', 'shop_login_status', 'bail_money']);
		if ($shopInfo) {
			$today_data                 = WorkerOrderHelper::getTodayData($provider_id);
			$result['shop_name']        = empty($shopInfo['shops_name']) ? "小帮" : $shopInfo['shops_name'];
			$result['cate_name']        = CateListHelper::getCateName($shopInfo['type_second']);
			$result['order_count']      = $today_data['order_count'];
			$result['income_amount']    = $today_data['income_amount'];
			$result['not_finish_count'] = $today_data['not_finish_count'];
			$result['online']           = $shopInfo['shop_login_status'];
			$result['biz_not_finish']   = $today_data['biz_not_finish'];
			$result['bail_money']       = $shopInfo['bail_money'];
			$result['bail_payed']       = intval(false);

			if ($shopInfo['bail_money'] >= Ref::BAIL_MONEY) {
				$result['bail_payed'] = intval(true);
			} else {
				//判断是否是免缴保证金
				$freeShop = ShopHelper::freeBailShop($provider_id);
				$freeShop ? $result['bail_payed'] = intval(true) : '';
			}
		}

		return $result;
	}

	/**
	 * 商家首页
	 * @version v11
	 * @param $provider_id
	 */
	public static function homeDataV11($provider_id)
	{
		$result   = false;
		$shopInfo = UserHelper::getShopInfo($provider_id, ['shops_name', 'shops_photo', 'type_first', 'type_second', 'shop_login_status', 'bail_money']);
		if ($shopInfo) {
			$today_data                 = WorkerOrderHelper::getTodayDataV11($provider_id);
			$result['shop_name']        = empty($shopInfo['shops_name']) ? "小帮" : $shopInfo['shops_name'];
			$result['cate_name']        = CateListHelper::getCateName($shopInfo['type_second']);
			$result['order_count']      = $today_data['order_count'];
			$result['income_amount']    = $today_data['income_amount'];
			$result['not_finish_count'] = $today_data['not_finish_count'];
			$result['online']           = $shopInfo['shop_login_status'];
			$result['bail_money']       = $shopInfo['bail_money'];
			$result['bail_payed']       = intval(false);

			if ($shopInfo['bail_money'] >= Ref::BAIL_MONEY) {
				$result['bail_payed'] = intval(true);
			} else {
				//判断是否是免缴保证金
				$freeShop = ShopHelper::freeBailShop($provider_id);
				$freeShop ? $result['bail_payed'] = intval(true) : '';
			}
		}

		return $result;
	}

	/**
	 * 获取商家信息
	 * @param $provider_id
	 */
	public static function getInfoV10($provider_id, $user_id)
	{
		$result    = false;
		$shop_info = UserHelper::getShopInfo($provider_id);
		$user_info = UserHelper::getUserInfo($user_id, 'mobile');
		if ($shop_info) {
			$result = [
				'user_mobile'  => $user_info['mobile'],
				'shop_mobile'  => $shop_info['utel'],
				'shop_name'    => empty($shop_info['shops_name']) ? '无忧小帮' : $shop_info['shops_name'],
				'user_name'    => empty($shop_info['uname']) ? '无忧用户' : $shop_info['uname'],
				'cate_name'    => CateListHelper::getCateName($shop_info['type_second']),
				'car_type'     => "摩托车",
				'car_num'      => $shop_info['plate_numbers'],
				'city_name'    => RegionHelper::getAddressNameById($shop_info['city_id']),
				'shop_address' => $shop_info['shops_address'],
				'photo_id'     => $shop_info['shops_photo'],
			];
		}

		return $result;
	}

	/**
	 * 保存商家基本信息
	 * @param $provider_id
	 * @return int
	 */
	public static function saveInfoV10($provider_id, $params)
	{
		//城市，地址,头像，小帮名称,接单号码
		$save_data = [
			'shops_photo' => $params['shop_photo'],
			'shops_name'  => $params['shop_name'],
			'utel'        => $params['shop_mobile'],
			'update_time' => time(),
		];

		return $result = Yii::$app->db->createCommand()->update("bb_51_shops", $save_data, ['id' => $provider_id])->execute();
	}

	public static function judgeOnlineV10($provider_id)
	{
		$result    = false;
		$shop_info = UserHelper::getShopInfo($provider_id, ['status', 'guangbi']);
		if ($shop_info) {
			$result = $shop_info['status'] == 1 ? true : false;
			$close  = $shop_info['status'] == 1 ? true : false;
			$result &= $close;
		}

		return $result;
	}

	//保证金缴纳首页
	public static function bailIndexV11($provider_id)
	{
		$result    = false;
		$shop_info = UserHelper::getShopInfo($provider_id, ['shops_money', 'bail_money', 'bail_time']);
		if ($shop_info) {
			$need_pay                 = Ref::BAIL_MONEY - $shop_info['bail_money'];
			$need_pay                 = sprintf("%.2f", $need_pay);
			$result['shops_money']    = $shop_info['shops_money'];       //账户余额
			$result['bail_money']     = $shop_info['bail_money'];        //已经缴纳金额
			$result['need_pay']       = $need_pay < 0 ? 0 : $need_pay;   //需要缴纳的金额
			$result['low_bail_money'] = Ref::BAIL_MONEY;                 //最低保证金
			$result['return_day']     = Ref::BAIL_RETURN_DAY;            //保证金退回时间
			$result['protocol_link']  = UrlHelper::webLink(['protocol/index', 'doc' => 'bail_management']);         //保证金协议
		}

		return $result;
	}

	//保证金解冻首页
	public static function bailRefundV10($provider_id)
	{
		$result       = false;
		$account_info = ShopHelper::getThawAccount($provider_id);
		$shop_info    = UserHelper::getShopInfo($provider_id, ['shops_money', 'bail_money', 'bail_time']);
		if ($shop_info) {

			$now_time      = time();
			$withdraw_time = $shop_info['bail_time'] + 3600 * 24 * Ref::BAIL_RETURN_DAY;
			$left_days     = 0;
			if ($shop_info['bail_time']) {
				if ($withdraw_time - $now_time > 0) {

					$diff      = $withdraw_time - $now_time;
					$left_days = ceil($diff / 86400);		//剩余解冻天数
				}
			}

			$result['bail_money']    = isset($shop_info['bail_money']) ? $shop_info['bail_money'] : 0.00;
			$result['binding_text']  = isset($account_info['binding_text']) ? $account_info['binding_text'] : 0;//提现绑定的账户文本
			$result['binding_id']    = isset($account_info['binding_id']) ? $account_info['binding_id'] : 0;//提现账户ID
			$result['is_binding']    = isset($account_info['is_binding']) ? 1 : 0;//供前端判断是否绑定
			$result['left_days']     = $left_days;
			$result['can_refund']    = 1;
			$orderCount              = OrderHelper::isDoingOrder($provider_id);
			$orderCount              = intval($orderCount);
			$result['order_count']   = $orderCount;
			$result['protocol_link'] = UrlHelper::webLink(['protocol/index', 'doc' => 'bail_management']);          //保证金协议
			if ($orderCount > 0 || $left_days > 0) {
				$result['can_refund'] = 0;
			}
		}

		return $result;
	}

}