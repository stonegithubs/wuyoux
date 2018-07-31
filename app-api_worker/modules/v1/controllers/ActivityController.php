<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User:
 * Date:
 */

namespace api_worker\modules\v1\controllers;


use api_worker\modules\v1\api\ActivityAPI;
use api_worker\modules\v1\helpers\StateCode;
use Yii;

class ActivityController extends ControllerAccess
{
	/**
	 * 生成商家未领取记录
	 * @param order_no	订单号
	 * @return array
	 */
	public function actionGetPackage()
	{
		if($this->api_version == "1.0")
		{
			$data = ActivityAPI::getPackageV10($this->provider_id,$this->user_id);
			if($data)
			{
				$this->_data = $data;
				$this->_message = "生成记录成功";
			}else{
				$this->setCodeMessage(StateCode::ACTIVITY_EMPTY);
			}
			return $this->response();
		}
	}

	/**
	 * 领取商家礼包
	 * @param	record_id	记录ID
	 * @return array
	 */
	public function actionOpenPackage()
	{
		if($this->api_version == "1.0")
		{
			$data = ActivityAPI::openPackageV10($this->provider_id,$this->user_id);
			$this->_data = $data;
			$this->_message = "领取成功!";
			return $this->response();
		}
	}

}