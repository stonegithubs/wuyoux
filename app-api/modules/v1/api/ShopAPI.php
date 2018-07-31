<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/31
 */

namespace api\modules\v1\api;

use api\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\orders\CateListHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\UrlHelper;
use yii\db\Query;

class ShopAPI extends HelperBase
{
	/**
	 * 商家入驻 验证字段 version 1.0
	 */
	public static function enterShopParamsV10($userInfo)
	{
		$result = false;
		$params = [
			'type_second'   => SecurityHelper::getBodyParam("type_second"),                                        //店铺类型
			'type_first'    => SecurityHelper::getBodyParam("type_first"),
			'invite_mobile' => SecurityHelper::getBodyParam("invite_mobile"),                        //邀请号码
			'cityname'      => SecurityHelper::getBodyParam("cityname"),                            //所在城市
			'area_id'       => SecurityHelper::getBodyParam("area_id"),                                //地域ID
			'driver_num'    => SecurityHelper::getBodyParam("driver_num"),                            //驾驶证号码
			'travel_num'    => SecurityHelper::getBodyParam("travel_num"),                            //行驶证号码
			'photo'         => SecurityHelper::getBodyParam("photo"),                                //照片{"driver_license_pic_pos":"","driver_license_pic_opp":"","travel_license_pic_pos":"","travel_license_pic_opp":"","id_card_pic_pos":"","id_card_pic_opp":"","hand_id_card_pic":""}
			'shop_address'  => SecurityHelper::getBodyParam("shop_address"),                        //商铺地址
			'shop_location' => SecurityHelper::getBodyParam("shop_location")                           //商家姓名
		];

		if (!isset($params['invite_mobile']) || empty($params['cityname']) || empty($params['area_id']) || empty($params['photo'])) {
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
			'shops_location'     => $params['shop_location'],
			'shops_location_lat' => $shop_location[0],
			'shops_location_lng' => $shop_location[1],
			're_mobile'          => $params['invite_mobile'],
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
	 * 商家入驻 添加判断 version 1.0
	 */
	public static function enterShopFunctionV10($userInfo)
	{
		$result = false;
		//获取参数
		$params = self::enterShopParamsV10($userInfo);

		if (!empty($params)) {
			//添加数据
			if ($params['shop']['type_second'] == Ref::CATE_ID_FOR_MOTOR) {
				$result = ShopHelper::enterMotoShop($params['shop'], $params['driver_license_pic'], $params['travel_license_pic'], $params['identity_card_pic']);
			} elseif ($params['shop']['type_second'] == Ref::CATE_ID_FOR_ERRAND) {
				$result = ShopHelper::enterErrandShop($params['shop'], $params['identity_card_pic']);
			} else {
				//其他分类,待处理
			}
		}

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
	 * 获取入驻参数
	 */
	public static function applyIndex()
	{
		//1.获取对应的参数
		//2.获取可入驻分类
		$city_id = RegionHelper::getCityId(SecurityHelper::getBodyParam('cityname'));

		return ShopHelper::applyCategories($city_id);
	}


}