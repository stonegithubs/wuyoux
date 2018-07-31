<?php

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\helpers\StateCode;
use common\components\ControllerAPI;
use common\helpers\security\SecurityHelper;
use api_wx\modules\mpv1\api\MapAPI;
use common\traits\APIMapTrait;
use common\helpers\utils\AMapHelper;


class MapController extends ControllerAPI
{
	use APIMapTrait;

	/**
	 * 用户附近的小帮
	 * @return array
	 */
	public function actionProviderNearby()
	{
		$center = SecurityHelper::getBodyParam("user_location");    //高德坐标,必须是 [113.39867,22.50898] 这种格式
		$type   = SecurityHelper::getBodyParam("type", 1);          //类型：1摩的，2快送
		if ($this->api_version == '1.0') {
			$this->_data = MapAPI::providerNearbyV10($center, $type);
		}
		if ($this->api_version == '1.1') {
			$this->_data = MapAPI::providerNearbyV11($center, $type);
		}

		return $this->response();
	}


	//附近商圈
	public function actionNearbyCbd()
	{
		$params['location'] = AMapHelper::coordToStr(SecurityHelper::getBodyParam('user_location'));  //用户坐标
		$params['page']     = SecurityHelper::getBodyParam('page');        //页码
		$params['pageSize'] = SecurityHelper::getBodyParam('page_size');

		if ($this->api_version == '1.0') {
			$data = AMapHelper::searchAround($params);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
				$this->_message = "暂无数据";
			}
		}

		return $this->response();
	}
}

