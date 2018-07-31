<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/3/17
 * Time: 9:47
 */

namespace m\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\utils\RegionHelper;
use yii\db\Query;

class RuleHelper extends HelperBase
{
	/**
	 * 获取小帮出行城市费率
	 * @param $params
	 * @return bool
	 */
	public static function getPriceTripBike($params)
	{
		$result    = false;
		$cityPrice = RegionHelper::getPriceByDay($params['city_id'], $params['area_id'], Ref::CATE_ID_FOR_MOTOR);
		if ($cityPrice) {
			$cityPrice['day_range_init']   = $cityPrice['day_range_init'] / 1000;
			$cityPrice['night_range_init'] = $cityPrice['night_range_init'] / 1000;
			$result                        = $cityPrice;
		}

		return $result;
	}

	/**
	 * 获取所在城市
	 * @param $city_id
	 * @return array|bool
	 */
	public static function getCity($city_id)
	{
		$result = false;
		if (!$city_id) {
			$city_id = 95;
		}
		$city = (new Query())->select('*')->from('wy_region')->where(['region_id' => $city_id])->one();
		if ($city) {
			$result = $city;
		}

		return $result;
	}
}