<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/18
 */

namespace m\helpers;

use common\helpers\HelperBase;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UtilsHelper;

class ScheduleHelper extends HelperBase
{
	/**
	 * 预计小帮行程
	 * @param $provider_location
	 * @param $end_location
	 * @return mixed
	 */
	public static function providerToEndLocation($provider_location, $end_location)
	{
		$route                     = AMapHelper::bicycling(AMapHelper::coordToStr($provider_location), AMapHelper::coordToStr($end_location));
		$distance                  = is_array($route) ? $route['distance'] : '0';
		$duration                  = is_array($route) ? $route['duration'] : '0';
		$result['ending_distance'] = UtilsHelper::distanceLabel($distance);
		$result['spend_time']      = UtilsHelper::durationLabel($duration);
		$result['distance'] = $distance;
		return $result;
	}
}