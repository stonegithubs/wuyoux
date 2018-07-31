<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/26
 */

namespace common\helpers\utils;

use common\helpers\HelperBase;
use common\helpers\utils\AMapHelper;
use common\components\Ref;
use common\models\payment\CityPrice;
use common\models\util\CityOpening;
use common\models\util\Region;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class RegionHelper extends HelperBase
{
	const wyRegionTbl      = 'wy_region';
	const CACHE_CITY_PRICE = 'city_price_all_data_';
	const CITY_LIST        = 'city_list';

	const DEFAULT_CITY     = 0;    //默认全国城市
	const CITY_OPENING_KEY = 'city_opening_key';

	/**
	 * 根据城市查询对应的ID并返回
	 *
	 * @param $name
	 *
	 * @return int
	 */
	public static function getRegionId($name)
	{
		//TODO 优化
		$region_id = 0;
		$name      = preg_replace("/广东省/", '', $name);
		$name      = preg_replace("/市/", '', $name);
		$name      = preg_replace("/自治州/", '', $name);
		$name      = preg_replace("/朝鲜族/", '', $name);
		$name      = preg_replace("/回族/", '', $name);
		$name      = preg_replace("/蒙古族/", '', $name);
		$name      = preg_replace("/哈萨克/", '', $name);
		$name      = preg_replace("/柯尔克孜族/", '', $name);
		$name      = preg_replace("/彝族/", '', $name);
		$name      = preg_replace("/白族/", '', $name);
		$name      = preg_replace("/哈尼族/", '', $name);
		$name      = preg_replace("/壮族/", '', $name);
		$name      = preg_replace("/苗族/", '', $name);
		$name      = preg_replace("/傣族/", '', $name);
		$name      = preg_replace("/景颇族/", '', $name);
		$name      = preg_replace("/傈僳族/", '', $name);
		$name      = preg_replace("/藏族/", '', $name);
		$name      = preg_replace("/土家族/", '', $name);
		$name      = preg_replace("/布依族/", '', $name);
		$name      = preg_replace("/侗族/", '', $name);
		$name      = preg_replace("/羌族/", '', $name);

		$model = (new Query())->select("region_id")->from(self::wyRegionTbl)->where(['region_name' => $name])->one();
		if ($model) {
			$region_id = $model['region_id'];

		}

		return $region_id;
	}

	/**
	 * 获取城市ID
	 *
	 * @param $cityname
	 * @return bool
	 */
	public static function getCityId($cityname)
	{
		$result   = false;
		$cityname = str_replace('市', '', $cityname);
		$query    = (new Query())->from(self::wyRegionTbl)->select("region_id")->where("region_name like '%$cityname%'")->one();
		if ($query) {
			$result = $query['region_id'];
		}

		return $result;
	}

	/**
	 * 获取区域ID
	 *
	 * @param $city_id
	 * @param $name
	 *
	 * @return bool|int
	 */
	public static function getAreaId($city_id, $name)
	{
		$model = Region::findOne(['region_name' => $name, 'parent_id' => $city_id]);
		if ($model) {
			return $model->region_id;
		}

		return 0;
	}

	/**
	 * 获取省份ID
	 *
	 * @param $cityId
	 * @return mixed
	 */
	public static function getProvinceId($cityId)
	{
		//TODO 需要优化
		$cityInfo     = (new Query())->from(self::wyRegionTbl)->select("parent_id")->where(['region_id' => $cityId])->one();
		$provinceIndo = (new Query())->from(self::wyRegionTbl)->select("region_id")->where(['region_id' => $cityInfo['parent_id']])->one();

		return $provinceIndo['region_id'];
	}

	/**
	 * 从缓存中获取城市费率数据
	 *
	 * @param $city_id
	 * @param $cate_id
	 *
	 * @return array|bool|mixed
	 */
	private static function _getCityPriceAllDataByCache($city_id, $cate_id)
	{

		$key  = self::CACHE_CITY_PRICE . $city_id . '_' . $cate_id;    //TODO 需要清空和缓存数据
		$data = Yii::$app->cache->get($key);
		if (!$data) {

			$model = CityPrice::findOne(['city_id' => $city_id, 'cate_id' => $cate_id, 'status' => 1, 'area_id' => null]);
			if ($model) {
				$data = ArrayHelper::toArray($model);
				Yii::$app->cache->set($key, $data, 864000); //每十天清除一次
			}
		}

		return $data;
	}

	/**
	 * 从缓存中获取区域费率数据
	 *
	 * @param $city_id
	 * @param $cate_id
	 *
	 * @return array|bool|mixed
	 */
	private static function _getAreaPriceAllDataByCache($area_id, $cate_id)
	{

		$key  = self::CACHE_CITY_PRICE . $area_id . '_' . $cate_id;    //TODO 需要清空和缓存数据
		$data = Yii::$app->cache->get($key);
		if (!$data) {

			$model = CityPrice::findOne(['area_id' => $area_id, 'cate_id' => $cate_id, 'status' => 1]);
			if ($model) {
				$data = ArrayHelper::toArray($model);
				Yii::$app->cache->set($key, $data, 864000); //每十天清除一次
			}
		}

		return $data;
	}


	/**
	 * 根据坐标获取当前价格
	 * @param $location
	 * @param $user_city_id
	 * @param $cate_id
	 * @return bool|mixed 返回当前坐标指定时间的价格体系
	 */
	public static function getCityPrice($location, $user_city_id, $cate_id)
	{

		$key       = "location_info_" . md5($location) . "_" . $user_city_id . $cate_id;
		$regionArr = Yii::$app->cache->get($key);
		if (!$regionArr) {
			$regionArr = RegionHelper::getAddressIdByLocation($location, $user_city_id);
			Yii::$app->cache->set($key, $regionArr, 600);
		}

		$area_id = isset($regionArr['area_id']) ? $regionArr['area_id'] : 0;
		$city_id = isset($regionArr['city_id']) ? $regionArr['city_id'] : $user_city_id;

		$time = time();

		return self::getPriceByTime($time, $city_id, $area_id, $cate_id);
	}

	/**
	 * 指定时间，城市，区域，分类的城市价格
	 * @param $time
	 * @param $city_id
	 * @param $area_id
	 * @param $cate_id
	 * @return bool|mixed    返回指定时间的价格体系
	 */
	public static function getPriceByTime($time, $city_id, $area_id, $cate_id)
	{

		$data = self::getPriceByDay($city_id, $area_id, $cate_id);

		return self::_setPriceItem($data, $time);

	}

	/**
	 * 获取全天的数据
	 *
	 * @param $city_id
	 * @param $area_id
	 * @param $cate_id
	 * @return array|bool|mixed
	 */
	public static function getPriceByDay($city_id, $area_id, $cate_id)
	{
		$result = false;
		if ($area_id > 0) {

			$result = self::_getAreaPriceAllDataByCache($area_id, $cate_id);
		}

		if (!$result) {
			$cityData = self::_getCityPriceAllDataByCache($city_id, $cate_id);

			$result = $cityData ? $cityData : self::_getCityPriceAllDataByCache(self::DEFAULT_CITY, $cate_id); //获取中山城市
		}

		return $result;
	}

	/**
	 * 获取指定时间区域费率
	 * @param $time string 当前时间
	 * @param $area_id int 区域ID
	 * @param $cate_id    int 分类ID
	 * @return bool|mixed
	 */
	public static function priceForArea($time, $area_id, $cate_id)
	{
		$result    = false;
		$area_data = self::_getAreaPriceAllDataByCache($area_id, $cate_id);
		if ($area_data) {

			$result = self::_setPriceItem($area_data, $time);
		}

		return $result;
	}

	/**
	 * 设置价格字段
	 *
	 * @param $price_data
	 * @param $current_time
	 * @return mixed
	 */
	private static function _setPriceItem($price_data, $current_time)
	{
		$flag      = strtotime(date("Y-m-d 00:00:00", $current_time));
		$startTime = strtotime($price_data['day_time'], $flag);
		$endTime   = strtotime($price_data['night_time'], $flag);

		$duration = 'night';
		if ($current_time > $startTime && $current_time < $endTime) {
			$duration = 'day';
		}

		if ($duration == 'day') {
			$data['time_init']        = $price_data['day_time_init'];
			$data['time_init_price']  = $price_data['day_time_init_price'];
			$data['time_unit_price']  = $price_data['day_time_unit_price'];
			$data['range_init']       = $price_data['day_range_init'];
			$data['range_init_price'] = $price_data['day_range_init_price'];
			$data['range_unit_price'] = $price_data['day_range_unit_price'];
			$data['service_fee']      = $price_data['day_service_fee'];
			$data['busy_fee']         = $price_data['day_busy_fee'];
		} elseif ($duration == 'night') {
			$data['time_init']        = $price_data['night_time_init'];
			$data['time_init_price']  = $price_data['night_time_init_price'];
			$data['time_unit_price']  = $price_data['night_time_unit_price'];
			$data['range_init']       = $price_data['night_range_init'];
			$data['range_init_price'] = $price_data['night_range_init_price'];
			$data['range_unit_price'] = $price_data['night_range_unit_price'];
			$data['service_fee']      = $price_data['night_service_fee'];
			$data['busy_fee']         = $price_data['night_busy_fee'];
		}

		$data['day_time']                = $price_data['day_time'];
		$data['night_time']              = $price_data['night_time'];
		$data['online_money_discount']   = $price_data['online_money_discount'];
		$data['online_payment_discount'] = $price_data['online_payment_discount'];
		$data['full_time_take']          = $price_data['full_time_take'];
		$data['part_time_take']          = $price_data['part_time_take'];
		$data['type']                    = $duration;

		return $data;
	}

	/**
	 * 根据坐标获取城市ID 默认用户所在城市ID
	 *
	 * @param $location 坐标
	 * @param $user_city_id //城市ID
	 *
	 * @return bool
	 */
	public static function getCityIdByLocation($location, $user_city_id)
	{
		$data     = $user_city_id;
		$map_data = AMapHelper::getRegeo($location);
		if ($map_data) {
			$ad_code = $map_data[0]['addressComponent']['citycode'];
			$region  = Region::findOne(['code' => $ad_code, 'level' => 2]);
			if ($region) {
				$data = $region->region_id;
			} else {
				Yii::$app->debug->log_info("no region code:", $map_data);
			}
		}

		return $data;
	}

	/**
	 * 清除城市费率配置缓存
	 *
	 * @return int
	 */
	public static function clearCityPriceCache()
	{
		$cityPrice = CityPrice::find()->all();
		$result    = 0;
		foreach ($cityPrice as $value) {
			$key = self::CACHE_CITY_PRICE . $value->city_id . '_' . $value->cate_id;
			$rs  = Yii::$app->cache->delete($key);
			if ($rs) {
				$result += 1;
			}

			$areaKey = self::CACHE_CITY_PRICE . $value->area_id . '_' . $value->cate_id;
			$rs      = Yii::$app->cache->delete($areaKey);
			if ($rs) {
				$result += 1;
			}
		}

		return $result;
	}

	/**
	 * 根据坐标获取区域
	 *
	 * @param     $location
	 * @param     $user_city_id
	 * @param int $user_area_id
	 * @return array
	 */
	public static function getAddressIdByLocation($location, $user_city_id)
	{
		$result = [
			'region_id' => $user_city_id,
			'city_id'   => $user_city_id,
			'area_id'   => 0,
			'type'      => 1,    //1直辖区/地级市 2县级市
		];

		$key = 'getAddressIdByLocation' . md5($location) . $user_city_id;

		$data = Yii::$app->cache->get($key);
		if (!$data) {
			$map_data = AMapHelper::getRegeo($location);

			if ($map_data) {
				//如果district为空，则是四级的，例如中山东莞
				if ($map_data[0]['addressComponent']['district']) {
					$area_code      = $map_data[0]['addressComponent']['adcode'];
					$result['type'] = 2;
				} else {
					$area_code = $map_data[0]['addressComponent']['towncode'];
				}
				$area = Region::findOne(['code' => $area_code, 'level' => 3]);
				if ($area) {
					$result['area_id']   = $area->region_id;
					$result['city_id']   = $area->parent_id;
					$result['region_id'] = $area->region_id;
				} else {
					Yii::$app->debug->log_info("no region code:", $map_data);
					$city_code = $map_data[0]['addressComponent']['citycode'];
					$region    = Region::findOne(['code' => $city_code, 'level' => 2]);
					if ($region) {
						$result['city_id']   = $region->region_id;
						$result['area_id']   = 0;
						$result['region_id'] = $region->region_id;
					}

				}
			}

			$data = $result;
			Yii::$app->cache->set($key, $data, 60);    //这里数据确保不被频繁访问，但又要即时获取
		}

		return $data;
	}

	/**
	 * 获取名称
	 *
	 * @param $region_id
	 * @return int|string
	 */
	public static function getAddressNameById($region_id)
	{
		$result = 0;
		$region = Region::findOne(['region_id' => $region_id]);
		if ($region) {
			$result = $region->region_name;
		}

		return $result;
	}

	/**
	 * 获取城市列表
	 *
	 * @return array
	 */
	public static function getCityList()
	{
		$key  = self::CITY_LIST;
		$data = Yii::$app->cache->get($key);
		if (!$data) {
			$data = (new Query())->from(self::wyRegionTbl)->select(['region_id AS city_id', 'region_name AS city_name'])->where(['level' => 2])->all();
			Yii::$app->cache->set($key, $data, 864000); //每十天清除一次
		}

		return $data;
	}

	/**
	 * 获取抽佣费率(从订单所在城市)
	 *
	 * @param $city_id
	 * @param $area_id
	 * @param $cate_id
	 * @return bool
	 */
	public static function getTakeMoneyRate($city_id, $area_id, $cate_id)
	{
		$result    = false;
		$area_data = self::_getAreaPriceAllDataByCache($area_id, $cate_id);
		if ($area_data) {
			$result['full_time_take'] = $area_data['full_time_take'];
			$result['part_time_take'] = $area_data['part_time_take'];

			return $result;
		}

		$city_data = self::_getCityPriceAllDataByCache($city_id, $cate_id);
		if ($city_data) {
			$result['full_time_take'] = $city_data['full_time_take'];
			$result['part_time_take'] = $city_data['part_time_take'];
		}

		//从2018-3-15日开始 全国性的抽佣
		//小帮出行10% 小帮快送20%

		if (!$result) {
			$city_data = self::_getCityPriceAllDataByCache(self::DEFAULT_CITY, $cate_id);
			if ($city_data) {
				$result['full_time_take'] = $city_data['full_time_take'];
				$result['part_time_take'] = $city_data['part_time_take'];
			}
		}

		return $result;
	}

	//获取热门城市
	public static function getHotCity()
	{
		$hotCity = [
			['city_id' => 95, 'city_name' => '中山'],
			['city_id' => 85, 'city_name' => '茂名'],
			['city_id' => 337, 'city_name' => '遂宁'],
			['city_id' => 83, 'city_name' => '江门'],
			['city_id' => 192, 'city_name' => '咸宁'],
			['city_id' => 202, 'city_name' => '怀化'],
		];

		return $hotCity;
	}

	/**
	 * 获取区域列表，父类和等级来查找数据
	 * @param $level
	 * @param $parent_id
	 * @return array|mixed
	 */
	public static function getRegionList($parent_id, $level)
	{
		$key  = self::CITY_LIST . "_" . $level . "_" . $parent_id;
		$data = Yii::$app->cache->get($key);
		if (!$data) {
			$condition = ['level' => $level];
			if ($parent_id != '-1') {
				$condition['parent_id'] = $parent_id;
			}
			$data = (new Query())->from(self::wyRegionTbl)->select(['region_id', 'region_name'])->where($condition)->all();
			Yii::$app->cache->set($key, $data, 86400 * 3); //每3天清除一次
		}

		return $data;
	}

	public static function isCityOpening($city_id, $area_id, $cate_id = null)
	{
		//开发思路
		//1、查找city_id
		//2、查找city_id 和area_id

		$result     = false;
		$key        = self::CITY_OPENING_KEY;
		$openStatus = 1;

		$cityKey = $key . "_" . $city_id;
		$data    = Yii::$app->cache->get($cityKey);
		if (!$data) {
			$data = CityOpening::findOne(['city_id' => $city_id, 'area_id' => 0, 'status' => $openStatus]);
			if ($data) {
				$data = ArrayHelper::toArray($data);
				Yii::$app->cache->set($cityKey, $data, 1); //每十天清除一次
			}
		}

		if (!$data) {    //无数据时，查找区域数据
			$areaKey = $key . "_" . $city_id . "_" . $area_id;
			$data    = Yii::$app->cache->get($areaKey);
			if (!$data) {
				$data = CityOpening::findOne(['city_id' => $city_id, 'area_id' => $area_id, 'status' => $openStatus]);
				if ($data) {
					$data = ArrayHelper::toArray($data);
					Yii::$app->cache->set($areaKey, $data, 1); //每十天清除一次
				}
			}
		}

		if ($data) {

			$result = true;

			if ($cate_id) {

				$cateJson = json_decode($data['cate_data']);
				in_array($cate_id, $cateJson) ? null : $result = false;
			}
		}

		return $result;
	}

	public static function checkCurrentRegionAndOpening($user_location, $start_location, $cateId, $user_city_id = 95)
	{
		$result = [
			'pass'    => true,
			'message' => "您的城市未开通该服务",
		];

		//发单位置和起点位置比较
		$orderLocation = self::getAddressIdByLocation($user_location, $user_city_id);
		$startLocation = self::getAddressIdByLocation($start_location, $user_city_id);
		$order_city_id = $orderLocation['city_id'];
		$order_area_id = $orderLocation['area_id'];
		$order_type    = $orderLocation['type'];

		$start_city_id = $startLocation['city_id'];
		$start_area_id = $startLocation['area_id'];

		if ($order_type == 1) {
			if ($order_city_id != $start_city_id) {    //下单城市和发单城市不在同一个城市
				$result['pass']    = false;
				$result['message'] = "请在您所在城市发单，平台暂不支持跨城发单";

				return $result;
			}
		}

//		if ($order_type == 2) {	//不判断区域
//			if ($order_area_id != $start_area_id) {    //下单城市和发单城市不在同一个城市
//				$result['pass']    = false;
//				$result['message'] = "请在您所在城市发单，平台暂不支持跨城发单";
//
//				return $result;
//			}
//		}

		//判断城市是否开通

//		$check = self::isCityOpening($start_city_id, $start_area_id, $cateId);
//		if (!$check) {
//			$result['pass'] = false;
//		}

		return $result;
	}

	/**
	 * 清空缓存数据
	 * @return int
	 */
	public static function clearCityOpeningCache()
	{
		$cityPrice = CityOpening::find()->all();
		$result    = 0;
		foreach ($cityPrice as $value) {
			$key = self::CITY_OPENING_KEY . $value->city_id;
			$rs  = Yii::$app->cache->delete($key);
			if ($rs) {
				$result += 1;
			}

			$areaKey = self::CITY_OPENING_KEY . "_" . $value->city_id . "_" . $value->area_id;
			$rs      = Yii::$app->cache->delete($areaKey);
			if ($rs) {
				$result += 1;
			}
		}

		return $result;
	}
}