<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\helpers\StateCode;
use api_user\modules\v1\helpers\UserSettingHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\BizHelper;
use common\helpers\users\UserHelper;
use api_user\modules\v1\api\UserAPI;
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

		}

		return $this->response();
	}

	/**
	 * 获取标签列表
	 * @return array
	 */
	public function actionTagIndex()
	{

		$this->_data = BizHelper::tagIndex($this->user_id);

		return $this->response();
	}

	/**
	 * 修改标签
	 * @return array
	 */
	public function actionUpdateTag()
	{
		if ($this->api_version == '1.0') {
			$tag_id = SecurityHelper::getBodyParam('tag_id');
			$data   = BizHelper::updateTag($this->user_id, $tag_id);
			if ($data) {

			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "修改标签失败";
			}
		}

		return $this->response();
	}

	/**
	 * 侧边栏
	 * @return array
	 */
	public function actionSideBar()
	{
		if ($this->api_version == '1.0') {
			$data = UserSettingHelper::sideBar($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "用户数据错误";
			}
		}

		return $this->response();
	}

	/**
	 * 获取企业送信息
	 * @return array
	 */
	public function actionGetBizInfo()
	{
		if ($this->api_version == '1.0') {

			$data = UserSettingHelper::getBizInfo($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "不是企业送用户";
			}
		}

		return $this->response();
	}

	/**
	 * 更新用户信息
	 * @return array
	 */
	public function actionUpdateUserInfo()
	{
		if ($this->api_version == '1.0') {

			$res            = UserAPI::updateUserInfo($this->user_id);
			$this->_data    = $res['data'];
			$this->_message = $res['message'];
		}

		return $this->response();
	}

	/**
	 * 获取全民营销二维码
	 * @return array
	 */
	public function actionGetCode()
	{
		if ($this->api_version == '1.0') {
			$data = UserHelper::getMarketCode($this->user_id);

			if($data){
				$this->_data = $data;
				$this->_message="获取成功";
			}else{
				$this->setCodeMessage(StateCode::COMMON_GET_ERROR);

			}
		}

		return $this->response();
	}
}

