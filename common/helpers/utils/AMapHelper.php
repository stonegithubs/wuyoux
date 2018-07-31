<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace common\helpers\utils;

use common\helpers\HelperBase;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use Yii;
use yii\db\Query;

class AMapHelper extends HelperBase
{

	public static function around($center, $filter = [], $radius = 5000)
	{

		$data       = Yii::$app->amap->around($center, $filter, $radius);
		$aroundData = false;
		if ($data) {

			foreach ($data as $row) {
				$update      = true;
				$modify_time = strtotime($row['_updatetime']) + 7200;    //过期时间
				if (time() > $modify_time) {
					$params['shop_login_status'] = false;
					ShopHelper::update($row['provider_id'], $params);    //下班状态
					AMapHelper::poiOfflineLBS($row['provider_id']);
					$update = false;
				}

				if ($update) {
					$aroundData[] = $row;
				}
			}

		}

		return $aroundData;
	}


	/**
	 * @param      $origns 1234,23424的组合
	 * @param      $destinations 1234,23424的组合
	 * @param bool $calShort
	 *
	 * @return array|mixed|string
	 */
	public static function routeMatrix($origns, $destinations, $calShort = true)
	{

		$origns       = self::coordToFormat($origns);
		$destinations = self::coordToFormat($destinations);
		$result       = '-1';
		$res          = Yii::$app->lbs->routeMatrix($origns, $destinations);

		if (is_array($res)) {

			if ($calShort) {    //返回最短距离
				$result = current($res);
			} else {
				$result = $res;
			}
		}

		return $result;
	}

	/**
	 * @param $coord [123.24234242,134.2423424]
	 *
	 *
	 * @return string 逗号隔开的坐标值 123.24234242,134.2423424
	 */
	public static function coordToStr($coord)
	{
		if (substr(trim($coord), 0, 1) != '[') {
			return $coord;  //不用转换
		}
		$result = '';
		if ($coord) {
			$coordArr = json_decode($coord, true);

			if (is_array($coordArr))
				$result = current($coordArr) . "," . end($coordArr);
		}

		return $result;
	}

	/**
	 * 经度，纬度  转  纬度，经度
	 *
	 * @param $coordStr
	 *
	 * @return string
	 */
	public static function coordToFormat($coordStr)
	{
		$arr = explode(",", $coordStr);
		if (count($arr) > 1)
			return $arr[1] . "," . $arr[0];

		return $coordStr;
	}

	/**
	 * 把位置更新到高德云存储
	 */
	public static function poiOnlineLBS($shop_id, $coordtype = 2)
	{
		$result   = false;
		$shopInfo = UserHelper::getShopInfo($shop_id);
		if ($shopInfo) {

			//验证空坐标数据
			if (!($shopInfo['shops_location_lng'] && $shopInfo['shops_location_lat'])) {
				Yii::$app->debug->log_info("坐标同步为空数据" . $shop_id, $shop_id);

				return $result;
			}

			$location = trim($shopInfo['shops_location_lng']) . "," . trim($shopInfo['shops_location_lat']);
			$data     = [
				'provider_id'        => $shopInfo['id'],                //商家ID
				'shops_name'         => isset($shopInfo['shops_name']) ? $shopInfo['shops_name'] : "小帮名称",            //商家名称
				'shops_address'      => $shopInfo['shops_address'],        //地址
				'location'           => $location,
				'shops_location_lng' => trim($shopInfo['shops_location_lng']),    //经度
				'shops_location_lat' => trim($shopInfo['shops_location_lat']),    //纬度
				'city_id'            => $shopInfo['city_id'],                //当前城市
				'cate_id'            => $shopInfo['type_second'],//$cate_id,                //分类ID
				'mobile'             => $shopInfo['utel'],                    //手机
				'user_id'            => $shopInfo['uid'],                //用户ID
				'range'              => $shopInfo['range'],         //商家接单范围（单位：米）
				'vip_status'         => $shopInfo['vip_status'],
				'job_time'           => $shopInfo['job_time'],
				'original_coord'     => json_encode('[' . $location . ']')            //原坐标
			];

			$key           = 'amap_lbs_key' . $shop_id . $coordtype;
			$lbs_parent_id = Yii::$app->cache->get($key);
			if ($lbs_parent_id) {                        //更新，更新失败执行删除
				$data['poi_id'] = $lbs_parent_id;
				$res            = Yii::$app->amap->poiUpdate($data, $coordtype);
				if (isset($res['status']) && $res['status'] == 1 && $res['info'] == 'OK')
					return true;
			}

			//存在的数据删除处理
			$params = [
				'filter' => 'provider_id:' . $shop_id
			];

			$deleted = true;
			$listRes = Yii::$app->amap->poiLists($params);
			if ($listRes['status'] == 1 && $listRes['info'] == 'OK' && $listRes['count'] > 0) {
				foreach ($listRes['datas'] as $item) {
					$delRes = Yii::$app->amap->poiDelete($item['_id']);

					if ($delRes['status'] == 1 && $delRes['info'] == 'OK' && $delRes['fail'] > 0) {
						$deleted = false;
					}
				}
			}

			//不存在就创建数据
			if ($deleted) {
				$poi_id = Yii::$app->amap->poiCreate($data, $coordtype);
				Yii::$app->cache->set($key, $poi_id, 3600);
				$result = $poi_id;
			}
		}

		return $result;
	}

	/**
	 * 从高德云存储删除坐标信息
	 */
	public static function poiOfflineLBS($shop_id)
	{
		$params = [
			'filter' => 'provider_id:' . $shop_id
		];
		$data   = Yii::$app->amap->poiLists($params);
		if ($data['status'] == 1 && $data['info'] == 'OK' && $data['count'] > 0) {

			foreach ($data['datas'] as $item) {
				Yii::$app->amap->poiDelete($item['_id']);
			}
		}

		return true;
	}

	/**
	 * 其他坐标转为高德坐标
	 *
	 * @param string $locations 坐标点 经度和纬度用","分割，经度在前，纬度在后，经纬度小数点后不得超过6位。多个坐标对之间用”|”进行分隔最多支持40对坐标。
	 * @param        $coordsys 原坐标系 可选值 gps; mapbar;baidu;autonavi(不进行转换)
	 * @param bool   $is_json
	 * @return bool|\common\components\amap\坐标点|string
	 */
	public static function convert($locations, $coordsys = 'baidu', $is_json = true)
	{
		$data = Yii::$app->amap->convert($locations, $coordsys);

		if ($data) {

			if ($is_json) {
				return "[" . $data . "]";
			} else {
				return $data;
			}
		}

		return $data;
	}

	public static function getRegeo($location)
	{
		$params['location'] = self::coordToStr($location);
		$data               = Yii::$app->amap->getRegeo($params);

		return $data;
	}

	/**
	 * 高德地图关键词搜索地址
	 *
	 * @param $params
	 *
	 * @return bool
	 */
	public static function keywordSearch($params)
	{
		$result = false;

		if (empty($params)) return $result;

		$gdData = Yii::$app->amap->search($params);
		if ($gdData) $result = $gdData;

		return $result;
	}

	/**
	 * App端 百度转高德
	 * @param        $locations
	 * @param string $coordsys
	 * @param bool   $is_json
	 * @return bool|\common\components\amap\坐标点|string
	 */
	public static function convert_baidu2Amap($locations, $coordsys = 'baidu', $is_json = true)
	{
		$locations = self::coordToStr($locations);
		$data      = Yii::$app->amap->convert($locations, $coordsys);
		if ($data) {

			if ($is_json) {
				return "[" . $data . "]";
			} else {
				return $data;
			}
		}

		return $data;
	}

	/**
	 * 坐标转换为城市信息
	 * @param $location
	 * @return bool
	 */
	public static function getCityAddressLocation($location)
	{
		$result               = false;
		$locations            = self::coordToStr($location);
		$params['location']   = self::convert($locations, 'gps', false);
		$params['extensions'] = 'all';
		$regeo_data           = Yii::$app->amap->getRegeo($params);
		if ($regeo_data) {
			$result['city']      = $regeo_data[0]['addressComponent']['city'];
			$result['location']  = '[' . $regeo_data[0]['pois'][0]['location'] . ']';
			$location_arr        = explode(',', $regeo_data[0]['pois'][0]['location']);
			$result['latitude']  = $location_arr[1];
			$result['longitude'] = $location_arr[0];
			$result['address']   = $regeo_data[0]['pois'][0]['name'];
		}

		return $result;
	}

	/**
	 * 高德骑行距离
	 * @param $origin
	 * @param $destination
	 * @return array|bool
	 */
	public static function bicycling($origin, $destination)
	{

		$result = false;
		$data   = Yii::$app->amap->bicycling($origin, $destination);

		if ($data) {
			$result = [
				'distance' => $data['distance'],
				'duration' => $data['duration']
			];
		}

		return $result;
	}

	/**
	 * 根据中心点坐标搜索周边地址
	 * @param $params
	 * @return array|bool
	 */
	public static function searchAround($params)
	{
		$result = false;
		//$params['pageSize'] = (isset($params['pageSize']) && ($params['pageSize'] <= 25)) ? $params['pageSize'] : 20;  ////每页大小不能超25，否则可能会出错
		$data = Yii::$app->amap->searchAround($params);

		if ($data) {
			foreach ($data['pois'] as $k => $v) {
				$result[$k]['name']     = $v['name'];     //地方名,比如 中山东方名都
				$result[$k]['location'] = '[' . $v['location'] . ']'; //坐标，比如 [113.427724,22.519523]
				$result[$k]['address']  = $v['address'];  //所在地址，比如 炬开发区中山六路南侧
			}
		}

		return $result;
	}

	/**
	 * 火星，高德坐标转百度坐标
	 * @param $location [113.23923823,22.2394289]
	 * @return mixed
	 */
	public static function amap2Baidu($location)
	{
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;

		$coordArr = json_decode($location, true);

		if (is_array($coordArr)) {
			$x = current($coordArr);
			$y = end($coordArr);

			$z      = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
			$theta  = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
			$bd_lon = $z * cos($theta) + 0.0065;
			$bd_lat = $z * sin($theta) + 0.006;

			$location = "[" . $bd_lon . "," . $bd_lat . "]";
		}

		return $location;
	}

	/**
	 * BD-09(百度) 坐标转换成  GCJ-02(火星，高德) 坐标
	 *
	 * 百度转高德坐标
	 * @param $location
	 * @return mixed
	 */
	public static function baidu2Amap($location)
	{

		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;

		$coordArr = json_decode($location, true);

		if (is_array($coordArr)) {
			$bd_lon = current($coordArr);
			$bd_lat = end($coordArr);

			$x      = $bd_lon - 0.0065;
			$y      = $bd_lat - 0.006;
			$z      = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
			$theta  = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
			$gd_lon = $z * cos($theta);
			$gd_lat = $z * sin($theta);

			$location = "[" . $gd_lon . "," . $gd_lat . "]";
		}

		return $location;
	}
}

