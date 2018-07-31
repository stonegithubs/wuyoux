<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\mpv1\controllers;

use common\components\ControllerAPI;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\AMapHelper;
use m\helpers\RuleHelper;

class UtilsController extends ControllerAPI
{


	/**
	 * 首页
	 * @return array
	 */
	public function actionWxConfig()
	{
		$url         = SecurityHelper::getBodyParam('url');
		$api         = SecurityHelper::getBodyParam('api');
		$apis        = explode(",", $api);
		$this->_data = WxpayHelper::getJsConfig($apis, $url);

		return $this->response();

	}

	public function actionGetLocation()
	{
		$location      = SecurityHelper::getBodyParam('location');
		$location_data = AMapHelper::getCityAddressLocation($location);
		if ($location_data) {
			$this->_data = $location_data;
		}

		return $this->response();
	}

	public function actionGetTripPrice()
	{
		$params                 = [
			'city_id' => SecurityHelper::getBodyParam('city_id'),
			'area_id' => SecurityHelper::getBodyParam('area_id'),
		];
		$cityPrice              = RuleHelper::getPriceTripBike($params);
		$city                   = RuleHelper::getCity($params['city_id']);
		$cityPrice['city_name'] = $city['region_name'];
		$this->_data            = $cityPrice;

		return $this->response();
	}
}