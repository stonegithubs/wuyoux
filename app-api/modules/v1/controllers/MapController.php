<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace api\modules\v1\controllers;

use api\modules\v1\api\MapAPI;
use api\modules\v1\helpers\StateCode;
use common\components\ControllerAPI;
use common\helpers\orders\OrderHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\AMapHelper;
use Yii;

class MapController extends ControllerAPI
{

	public function actionGetList()
	{

		$params['page']   = SecurityHelper::getBodyParam("page", 1);
		$params['limit']  = SecurityHelper::getBodyParam("limit", 100);
		$params['filter'] = Yii::$app->request->get("filter");

		$data = Yii::$app->amap->poiLists($params);

		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}

	/**
	 * 地址搜索
	 * @return array
	 */
	public function actionSearch()
	{
		$params['city']     = SecurityHelper::getBodyParam("city", "中山市");
		$params['keywords'] = SecurityHelper::getBodyParam("keywords", "");
		$params['limit']    = SecurityHelper::getBodyParam("limit", 15);
		$params['page']     = SecurityHelper::getBodyParam("page", 1);


		$data = AMapHelper::keywordSearch($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->_code    = StateCode::OTHER_EMPTY_DATA;
			$this->_message = "暂无数据";
		}

		return $this->response();
	}

	/**
	 * 坐标转换
	 */
	public function actionCovert()
	{
		$params['locations'] = SecurityHelper::getBodyParam("locations", "");
		$params['coordsys']  = SecurityHelper::getBodyParam("coordsys", "baidu");

		$data = AMapHelper::convert($params['locations'], $params['coordsys']);
		if ($data) {
			$this->_data = ['coord' => $data];
		} else {
			$this->_code    = StateCode::OTHER_EMPTY_DATA;
			$this->_message = "转换失败";
		}

		return $this->response();
	}

	/**
	 * 通用获取当前价格和距离
	 * @return array
	 */
	public function actionGetRange()
	{
		$params['start_location'] = SecurityHelper::getBodyParam("start_location");
		$params['end_location']   = SecurityHelper::getBodyParam("end_location");
		$params['cate_id']        = SecurityHelper::getBodyParam("cate_id");
		$params['city_id']        = SecurityHelper::getBodyParam("city_id");

		$data = OrderHelper::getRangeAndPrice($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->_code    = StateCode::OTHER_EMPTY_DATA;
			$this->_message = "获取数据失败";
		}

		return $this->response();
	}

	/**
	 * 用户附近的小帮
	 * @return array
	 */
	public function actionProviderNearby()
	{
		if ($this->api_version == '1.0') {
			$center      = SecurityHelper::getBodyParam("user_location");    //高德坐标
			$type        = SecurityHelper::getBodyParam("type");            //类型：1摩的，2快送
			$this->_data = MapAPI::providerNearbyV10($center, $type);
		}

		return $this->response();
	}


}