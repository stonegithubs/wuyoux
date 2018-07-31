<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Ted
 * Date: Ted 2017/8/18
 */

namespace api\modules\api\blocks;

use api\modules\v1\helpers\StateCode;
use common\helpers\HelperBase;
use common\helpers\orders\CateListHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UtilsHelper;
use Yii;

class PushBlock extends HelperBase
{
	/**
	 * 获取推送链接参数
	 * @return array
	 */
	public static function getLinkParam()
	{
		$result = [];
		$param  = [
			'user_type'   => SecurityHelper::getBodyParam("user_type"),
			'link'        => SecurityHelper::getBodyParam("link"),
			'push_id'     => SecurityHelper::getBodyParam("push_id"),
			'title'       => SecurityHelper::getBodyParam("title"),
			'content'     => SecurityHelper::getBodyParam("content"),
			'province_id' => SecurityHelper::getBodyParam("province_id", ""),
			'city_id'     => SecurityHelper::getBodyParam("city_id", ""),
			'area_id'     => SecurityHelper::getBodyParam("area_id", ""),
			'mobile'      => SecurityHelper::getBodyParam("mobile", ""),
			'start_time'  => SecurityHelper::getBodyParam("start_time", ""),
			'end_time'    => SecurityHelper::getBodyParam("end_time", ""),
			'msg_title'   => SecurityHelper::getBodyParam("msg_title", ""),
			'msg_content' => SecurityHelper::getBodyParam("msg_content", ""),
		];
		if (empty($param['link'])) return $result;
		if (empty($param['user_type'])) return $result;
		if (empty($param['title'])) return $result;
		if (empty($param['content'])) return $result;
		if (empty($param['msg_title'])) return $result;
		if (empty($param['msg_content'])) return $result;
		if (empty($param['push_id'])) return $result;

		return $param;

	}

	/**
	 * 推送链接给用户
	 * @param $param
	 * @return bool
	 */
	public static function pushLinkToUser($param)
	{
		$result = true;
		switch ($param['user_type']) {
			case "user":
				QueueHelper::pushLinkToUser($param);
				break;
			case "provider":
				QueueHelper::pushLinkToProvider($param);
				break;
			case "over_user":
				QueueHelper::pushLinkToOverUser($param);
				break;
		}

		return $result;
	}

	/**
	 * 获取推送活动参数
	 * @return array
	 */
	public static function _getActivityParam()
	{
		$result = [];
		$param  = [
			'user_type'   => SecurityHelper::getBodyParam("user_type"),
			'title'       => SecurityHelper::getBodyParam("title"),
			'content'     => SecurityHelper::getBodyParam("content"),
			'link'        => SecurityHelper::getBodyParam("link"),
			'province_id' => SecurityHelper::getBodyParam("province_id", ""),
			'city_id'     => SecurityHelper::getBodyParam("city_id", ""),
			'area_id'     => SecurityHelper::getBodyParam("area_id", ""),
			'mobile'      => SecurityHelper::getBodyParam("mobile", ""),
			'start_time'  => SecurityHelper::getBodyParam("start_time", ""),
			'end_time'    => SecurityHelper::getBodyParam("end_time", ""),
			'msg_title'   => SecurityHelper::getBodyParam("msg_title", ""),
			'msg_content' => SecurityHelper::getBodyParam("msg_content", ""),
			'push_id'     => SecurityHelper::getBodyParam("push_id"),
		];
		if (empty($param['user_type'])) return $result;
		if (empty($param['title'])) return $result;
		if (empty($param['content'])) return $result;
		if (empty($param['msg_title'])) return $result;
		if (empty($param['msg_content'])) return $result;
		if (empty($param['link'])) return $result;

		return self::_setActivityParam($param);
	}

	/**
	 * 设置推送活动参数
	 * @param $param
	 * @return array
	 */
	public static function _setActivityParam($param)
	{
		return [
			'info_content' => [
				'glx'            => 20,
				'link'           => $param['link'],
				'title'          => $param['title'],
				'inform_content' => $param['content'],
			],
			'title'        => $param['title'],
			'content'      => $param['content'],
			'user_type'    => $param['user_type'],
			'province_id'  => $param['province_id'],
			'city_id'      => $param['city_id'],
			'area_id'      => $param['area_id'],
			'mobile'       => $param['mobile'],
			'start_time'   => $param['start_time'],
			'end_time'     => $param['end_time'],
			'msg_title'    => $param['msg_title'],
			'msg_content'  => $param['msg_content'],
			'push_id'      => $param['push_id'],
		];
	}

	/**
	 * 推送活动给用户工作流
	 * @param $param
	 * @return bool
	 */
	public static function pushActivityToUserWorkFlow($param)
	{
		$result = true;
		switch ($param['user_type']) {
			case "user":
				QueueHelper::pushActivityToUser($param);
				break;
			case "provider":
				QueueHelper::pushActivityToProvider($param);
				break;
		}

		return $result;
	}

	/**
	 * 后台查找附近的数据
	 * @param $center_location
	 * @param $cate_id
	 * @return array|bool
	 */
	public static function nearbyShop($center_location, $cate_id)
	{
		$data = false;

		if ($center_location) {
			$filter      = ['cate_id:' . $cate_id];
			$nearbyShops = AMapHelper::around($center_location, $filter);
		} else {

			Yii::$app->debug->job_info('中心点坐标无法解析为高德坐标');

			return $data;
		}

		if ($nearbyShops) {
			foreach ($nearbyShops as $key => $value) {

				$locationStr = AMapHelper::coordToStr($center_location);

				$route              = AMapHelper::bicycling(AMapHelper::coordToStr($center_location), $value['_location']);
				$ending_distance    = is_array($route) ? $route['distance'] : '0';
				$value['distance']  = UtilsHelper::distance($ending_distance);
				$value['cate_name'] = CateListHelper::getCateName($value['cate_id']);
				$data[]             = $value;

			} //foreach
		}//if

		return $data;
	}

}