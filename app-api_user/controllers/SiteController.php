<?php

namespace api_user\controllers;

use api_user\modules\v1\helpers\StateCode;
use common\components\ControllerAPI;

/**
 * Site controller
 */

use common\helpers\utils\OssHelper;
use Yii;

class SiteController extends ControllerAPI
{

	public function actionError()
	{

		$this->_code    = "10000";
		$this->_message = "Interface does not exist";

		return $this->response();
	}

	/**
	 * Displays homepage.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		die("Hello User API,it's not funny.");
	}

	public function actionImageCallback()
	{


//		$data = OssHelper::imageCallBack();
//		if ($data) {
//
//		} else {
//			$this->_code    = StateCode::COMMON_OPERA_ERROR;
//			$this->_message = "上传失败";
//		}

		$this->_data=['id'=>88888];

		return $this->response();
	}
}
