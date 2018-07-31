<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/24
 */

namespace m\helpers\activity;


use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use yii\data\Pagination;
use yii\db\Query;
use common\helpers\activity\ActivityHelper as Activity;

class ActivityHelper extends Activity
{
	/**
	 * 获取活动列表
	 * @param $params
	 */
	public static function getActivityList($params)
	{
		$list = false;
		$num  = 0;
		if ($params) {
			$query = (new Query())->from("bb_activity");
			$query->andWhere(['status' => 1, 'type' => 1]);
			if (isset($params['active_object']) && $params['active_object']) $query->andWhere(['in', 'active_object', $params['active_object']]);

			if (isset($params['city_id'])) {
				$query->andWhere(['in', 'city_id', ['0', $params['city_id']]]);
			}
			$query->andWhere(['<', "start_time", time()]);
			$query->andWhere(['>', "end_time", time()]);

			$num        = $query->count();//记录总数
			$pagination = new Pagination([
				'totalCount' => $query->count(),
				'pageSize'   => $params['pageSize'],
				'page'       => ($params['curr'] - 1),
			]);
			$query->orderBy(['create_time' => SORT_DESC]);//排序
			$queryList = $query->offset($pagination->offset)->limit($pagination->limit)
				->all();
			if ($queryList) {
				foreach ($queryList as $key => $value) {
					$list[$key] = [
						'title'       => $value['title'],
						'url'         => $value['content'],
						'brief'       => $value['brief'],
						'create_time' => date("Y-m-d", $value['create_time']),
						'pic'         => ImageHelper::getUserPhoto($value['pic'])
					];
				}
			}
		}

		return ['list' => $list, "num" => $num, "curr" => $params['curr']];
	}

	/**
	 * 活动列表分页参数
	 * @param $params
	 * @return array
	 */
	public static function getActivityListPageParams($params)
	{
		$num = 0;
		if ($params) {
			$query = (new Query())->from("bb_activity");
			$query->andWhere(['status' => 1, 'type' => 1]);
			if (isset($params['active_object']) && $params['active_object']) $query->andWhere(['in', 'active_object', $params['active_object']]);
			if (isset($params['city_id'])) {
				$query->andWhere('in', 'city_id', ['0', $params['city_id']]);
			}
			$query->andWhere(['<', "start_time", time()]);
			$query->andWhere(['>', "end_time", time()]);
			$num = $query->count();
			$num = $num ? $num : 0;
		}

		return ["num" => $num, "group" => ceil($num / $params['pageSize']), "curr" => $params['curr']];
	}
}