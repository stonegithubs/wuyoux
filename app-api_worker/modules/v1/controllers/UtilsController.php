<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/06/11
 */

namespace api_worker\modules\v1\controllers;

use api_worker\modules\v1\helpers\StateCode;
use common\helpers\images\ImageHelper;
use common\helpers\utils\PostsHelper;
use Yii;

class UtilsController extends ControllerAccess
{

	//阿里云OSS图片回调
	public function actionImageCallback()
	{
		$imageId = ImageHelper::AliyunOssCallback();
		if ($imageId) {

			$this->_data = $imageId;
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = "上传失败";
		}

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