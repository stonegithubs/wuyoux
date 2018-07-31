<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\api\UtilsAPI;
use api_user\modules\v1\helpers\StateCode;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\PostsHelper;
use common\helpers\utils\UtilsHelper;
use Yii;

class UtilsController extends ControllerAccess
{

	/**
	 * 通过access_token换取session key
	 * @return array
	 */
	public function actionSessionKey()
	{
		$ak          = Yii::$app->request->getBodyParam("access_token");
		$this->_data = [
			'session_key' => SecurityHelper::setSessionKey($ak),
			'expire'      => 1800
		];

		return $this->response();
	}

	//APP启动页广告
	public function actionStartupAd()
	{
		if ($this->api_version == '1.0') {
			$data = PostsHelper::getStartUpData();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "获取数据失败";
			}
		}

		return $this->response();
	}


	//APP弹窗广告
	public function actionPopupAd()
	{
		if ($this->api_version == '1.0') {
			$data = PostsHelper::getPopupAdData();
			$tag  = rand(0, 1); //for test
			if ($data && $tag) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "获取数据失败";
			}
		}

		return $this->response();
	}
}