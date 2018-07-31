<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/2/9
 */

namespace m\controllers;

use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use m\helpers\StateCode;
use m\helpers\activity\ActivityHelper;

class ActivityController extends ControllerAccess
{
	/**
	 * 货车开发加油
	 * @return string|\yii\web\Response
	 */
	public function actionTruck()
	{
		$result = ActivityHelper::find($this->user_id);
		if ($result) {
			return $this->redirect('click-on');
		} else {
			$data['url']       = UrlHelper::webLink('activity/filling');
			$data['click_url'] = UrlHelper::webLink('activity/click-on');

			return $this->renderPartial("truck", $data);
		}
	}

	/**
	 * 为货车加油
	 * @return string
	 */
	public function actionFilling()
	{
		$result = ActivityHelper::save($this->user_id);

		return json_encode($result);
	}

	/**
	 * 已经加过油
	 * @return string
	 */
	public function actionClickOn()
	{
		return $this->renderPartial('click-on');
	}

	/**
	 * 用户活动列表
	 * @return string
	 */
	public function actionUserActivityList()
	{
		$this->layout = "base";

		$params = [
			'city'     => SecurityHelper::getGetParam("city", ""),
			'curr'     => SecurityHelper::getGetParam("curr", 1),
			'object'   => "2,3",
			'pageSize' => SecurityHelper::getGetParam("pageSize", 6),
		];
		if ($params['city']) $params['city'] = urldecode($params['city']);
		if ($params['object']) $params['active_object'] = explode(",", $params['object']);
		$page = ActivityHelper::getActivityListPageParams($params);
		$data = [
			'curr'        => $params['curr'],
			'pageSize'    => $params['pageSize'],
			'num'         => $page['num'],
			'requestData' => [
				'curr'     => $params['curr'],
				'city'     => $params['city'],
				'object'   => $params['object'],
				'pageSize' => $params['pageSize']
			],
			'requestUrl'  => UrlHelper::webLink('activity/get-activity-list')
		];

		return $this->render('user-activity-list', ['params' => $data]);
	}

	/**
	 * 小帮活动列表
	 * @return string
	 */
	public function actionProviderActivityList()
	{
		$this->layout = "base";

		$params = [
			'city'     => SecurityHelper::getGetParam("city", ""),
			'curr'     => SecurityHelper::getGetParam("curr", 1),
			'object'   => "1,3",
			'pageSize' => SecurityHelper::getGetParam("pageSize", 6),
		];
		if ($params['city']) $params['city'] = urldecode($params['city']);
		if ($params['object']) $params['active_object'] = explode(",", $params['object']);
		$page = ActivityHelper::getActivityListPageParams($params);
		$data = [
			'curr'        => $params['curr'],
			'pageSize'    => $params['pageSize'],
			'num'         => $page['num'],
			'requestData' => [
				'curr'     => $params['curr'],
				'city'     => $params['city'],
				'object'   => $params['object'],
				'pageSize' => $params['pageSize']
			],
			'requestUrl'  => UrlHelper::webLink('activity/get-activity-list')
		];

		return $this->render('provider-activity-list', ['params' => $data]);
	}

	/**
	 * 小帮活动列表
	 */
	public function actionGetActivityList()
	{
		$params = [
			'city'     => SecurityHelper::getGetParam("city", ""),
			'curr'     => SecurityHelper::getGetParam("curr", 1),
			'object'   => SecurityHelper::getGetParam("object", ""),
			'pageSize' => SecurityHelper::getGetParam("pageSize", 6),
		];
		if ($params['city']) $params['city_id'] = RegionHelper::getCityId($params['city']);

		if ($params['object']) $params['active_object'] = explode(",", $params['object']);
		$this->_data = ActivityHelper::getActivityList($params);

		return $this->response();
	}
}