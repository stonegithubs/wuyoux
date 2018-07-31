<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api\modules\v1\controllers;


use api\modules\v1\helpers\StateCode;
use common\helpers\payment\WalletHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use Yii;

class UserController extends ControllerAccess
{
	//获取用户信息		v1/user/get-info

	//提交反馈意见   		v1/user/feedBack

	/**
	 * 提交反馈意见
	 * @return array
	 */
	public function actionFeedBack()
	{
		$data = UserHelper::feedBack($this->user_id);
		if ($data) {

		} else {
			$this->setCodeMessage(StateCode::OTHER_FEEDBACK_SAVE);
		}

		return $this->response();
	}

	/**
	 * 获取用户信息
	 */
	public function actionGetInfo()
	{
		if ($this->api_version == '1.0') {
			$data = UserHelper::getUserInfo($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::USER_NOT_EXIST);
			}

			return $this->response();
		}
	}

}

