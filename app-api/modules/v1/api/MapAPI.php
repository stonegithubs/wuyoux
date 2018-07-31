<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/11/07
 */

namespace api\modules\v1\api;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UtilsHelper;

class MapAPI extends HelperBase
{
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

		$data = AMapHelper:: around($center, $filter);
		if ($data) {
			$result['count'] = count($data);
			$list            = [];
			foreach ($data as $key => $value) {

				$list[] = [
					'provider_id'   => $value['provider_id'],
					'name'          => $value['_name'],
					'distance_text' => "距离您约" . UtilsHelper::distance($value['_distance']),
					'distance'      => $value['_distance'],
					'mobile'        => $value['mobile'],
					'location'      => $value['original_coord']	//防止该值报错 //百度坐标
				];
			}

			$result['list'] = $list;
		}

		return $result;
	}

}