<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/11/07
 */

namespace api_user\modules\v1\api;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UtilsHelper;
use yii\helpers\ArrayHelper;

class MapAPI extends HelperBase
{
	/**
	 * @param      $center string  坐标例如[13.12555, 22.52965]
	 * @param null $type int  类型 1是摩的，2是快送
	 * @return array
	 */
	public static function providerNearbyV10($center, $type = null)
	{
		$result = [
			'count' => 0,
			'list'  => [],
		];

		$filter = [];
		if ($type == Ref::ORDER_TYPE_TRIP) {
			$filter[] = 'tags:' . Ref::CATE_ID_FOR_MOTOR;
		} elseif ($type == Ref::ORDER_TYPE_ERRAND) {
			$filter[] = 'tags:' . Ref::CATE_ID_FOR_ERRAND;
		}

		$data = AMapHelper:: around($center, $filter, $radius = 12000);
		if ($data) {
			$result['count'] = count($data);
			$list            = [];
			foreach ($data as $key => $value) {

				$map = [
					'provider_id'   => $value['provider_id'],
					'name'          => $value['_name'],
					'distance_text' => "距离您约" . UtilsHelper::distance($value['_distance']),
					'distance'      => $value['_distance'],
					'mobile'        => $value['mobile'],
					'location'      => $value['original_coord']    //防止该值报错
				];
				if ($type == Ref::ORDER_TYPE_ERRAND) {
					$map['location'] = "[" . $value['_location'] . "]"; //返回高德坐标
				}

				$list[] = $map;
			}

			$result['list'] = $list;
		}

		return $result;
	}


	/**
	 * 附近的小帮 1.1版本
	 * @param string $center 中心坐标
	 * @param int    $type 类型 1是摩的，2是快送
	 * @param int    $radius 半径
	 * @return array
	 */
	public static function providerNearbyV11($center, $type, $radius = 5000)
	{
		$shortestTime = 1;
		$providerList = self::providerNearby($center, $type, $radius);      //附近5公里内的小帮, 类型：1摩的，2快送
		$serviceTip   = '附近暂无小帮';
		//附近有小帮的话
		if (isset($providerList['list'][0])) {
			ArrayHelper::multisort($providerList['list'], 'distance', SORT_ASC);   //根据小帮离用户距离进行排序
			$nearestLocation =  $providerList['list'][0]['location'];
			$route           = AMapHelper::bicycling(AMapHelper::coordToStr($nearestLocation), AMapHelper::coordToStr($center)); //得到最近的小帮离用户的距离和时间
			$shortestTime    = isset($route['distance']) ? ceil($route['distance'] / 1000 * 1.5) : 1;	//出行根据90秒走1公里来计算
			$serviceTip      = $shortestTime . '分钟后开始服务';
		}

		$result = [
			'shortest_time'  => $shortestTime . '分钟',    //用户要等待的最短时间
			'service_tip'    => $serviceTip,               //服务提示
			'provider_count' => $providerList['count'],    //小帮个数
			'provider_list'  => $providerList['list'],     //小帮列表
		];

		return $result;
	}


	//附近的小帮
	//作为被调用的子方法
	public static function providerNearby($center, $type, $radius = 5000)
	{
		$result = [
			'count' => 0,
			'list'  => [],
		];

		$filter = [];
		if ($type == Ref::ORDER_TYPE_TRIP) {
			$filter[] = 'tags:' . Ref::CATE_ID_FOR_MOTOR;
		} elseif ($type == Ref::ORDER_TYPE_ERRAND) {
			$filter[] = 'tags:' . Ref::CATE_ID_FOR_ERRAND;
		}

		$data = AMapHelper:: around($center, $filter, $radius);
		if ($data) {
			$result['count'] = count($data);
			$list            = [];
			foreach ($data as $key => $value) {

				$map    = [
					'provider_id'   => $value['provider_id'],
					'name'          => $value['_name'],
					'distance_text' => "距离您约" . UtilsHelper::distance($value['_distance']),
					'distance'      => $value['_distance'],
					'mobile'        => $value['mobile'],
					'location'      => '[' . $value['_location'] . ']',   //高德坐标
				];
				$list[] = $map;
			}

			$result['list'] = $list;
		}

		return $result;
	}

}