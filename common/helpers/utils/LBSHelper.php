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
use common\helpers\users\UserHelper;
use Yii;
use yii\db\Query;

class LBSHelper extends HelperBase
{

	public static function nearby($center, $tags, $radius = 5000)
	{
		$data     = Yii::$app->lbs->nearby($center, $tags, $radius);
		$new_data = false;
		foreach ($data as $row) {
			$update = true;
			if (isset($row['modify_time'])) {
				$modify_time = $row['modify_time'] + 7200;    //过期时间
				if (time() > $modify_time) {
					LBSHelper::poiOfflineLBS($row['title']);
					$update = false;
				}
			}

			if ($update) {
				$new_data[] = $row;
			}
		}

		return $new_data;
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
	 * 中心点到多个终点的路线距离
	 * @param $origns //原点坐标
	 * @param $destinations //坐标以“|”隔开的字符串
	 * @return bool
	 *
	 * @param $origns
	 * @param $destinations
	 * @return bool|mixed
	 */
	public static function routeMatrixForDestinationList($origns, $destinations)
	{
		$result = false;
		$res    = Yii::$app->lbs->routeMatrix($origns, $destinations);
		if ($res) {
			$result = $res;
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
	 * 更新位置到百度LBS云存储
	 */
	public static function poiOnlineLBS($shop_id)
	{
		$result   = false;
		$shopInfo = UserHelper::getShopInfo($shop_id);
		if ($shopInfo) {

			//TODO 这块需要做缓存
			$tags    = '';
			$cate_id = '';
			if ($shopInfo['type_second']) {

				$catelist = (new Query())->select(["id", 'name'])->from("bb_catelist")->where('id in (' . $shopInfo['type_second'] . ')')->all();

				if ($catelist)
					foreach ($catelist as $key => $value) {
						$tags    .= $value['name'] . ' ';
						$cate_id .= $value['id'] . ',';
					}
			}

			$shopInfo['tags'] = substr($tags, 0, -1);
			$data             = [
				'shop_id'            => $shopInfo['id'],                //商家ID
				'shops_name'         => $shopInfo['shops_name'],            //商家名称
				'shops_address'      => $shopInfo['shops_address'],        //地址
				'tags'               => $shopInfo['tags'],                //标签
				'shops_location_lat' => $shopInfo['shops_location_lat'],    //纬度
				'shops_location_lng' => $shopInfo['shops_location_lng'],    //经度
				'city_id'            => $shopInfo['city_id'],                //当前城市
				'shop_login_status'  => $shopInfo['shop_login_status'],    //是否在线（1在线，0不在线）
				'cate_id'            => $cate_id,                //分类ID
				'mobile'             => $shopInfo['utel'],                    //手机
				'user_id'            => $shopInfo['uid'],                //用户ID
				'range'              => $shopInfo['range'],         //商家接单范围（单位：米）
				'vip_status'         => $shopInfo['vip_status'],
				'job_time'           => $shopInfo['job_time'],
			];

			$key = 'baidu_lbs_key' . $shop_id;
			echo $lbs_parent_id = Yii::$app->cache->get($key);
			if ($lbs_parent_id) {                        //更新，更新失败执行删除
				$data['poi_id'] = $lbs_parent_id;
				$res            = Yii::$app->lbs->poiUpdate($data);
				if (isset($res['status']) && $res['status'] == 0)
					return "update success";    //更新成功不继续往下走
			}

			//存在的数据删除处理
			$deleted = true;
			$listRes = Yii::$app->lbs->lists(['title' => $shop_id]);
			if ($listRes['status'] === 0 && $listRes['total'] > 0) {    //多条数据就全部删除处理
				foreach ($listRes['pois'] as $row) {
					$delRes = Yii::$app->lbs->poiDelete($row['id']);

					if ($delRes['status'] != 0) {
						$deleted = false;
					}
				}
			}

			//不存在就创建数据
			if (isset($listRes['status']) && $listRes['status'] == 0 && $listRes['total'] == 0 && $deleted) {

				$poi_id = Yii::$app->lbs->poiCreate($data);
				Yii::$app->cache->set($key, $poi_id, 3600);
				$result = $poi_id;

			} else {
				var_dump($listRes);
				$result = "poidata is exist,create failed";
			}
		}

		return $result;
	}

	//离线就把LBS数据删除 节省资源
	public static function poiOfflineLBS($shop_id)
	{
		$res = Yii::$app->lbs->lists(['title' => $shop_id]);
		if (isset($res['status']) && $res['status'] === 0 && $res['total'] > 0) {    //多条数据就全部删除处理
			foreach ($res['pois'] as $row) {
				Yii::$app->lbs->poiDelete($row['id']);
			}
		}

		return true;
	}

	public static function convert_Amap2Baidu($coords)
	{
		$result       = $coords;
		$coords       = self::coordToStr($coords);
		$new_location = Yii::$app->lbs->convert($coords);
		if ($new_location) {
			$result = $new_location;
		}

		return $result;
	}
}

