<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace api_user\modules\v1\controllers;

use common\components\ControllerAPI;
use common\traits\APIMapTrait;
use api_user\modules\v1\api\MapAPI;
use api_user\modules\v1\helpers\StateCode;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\AMapHelper;
use Yii;

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