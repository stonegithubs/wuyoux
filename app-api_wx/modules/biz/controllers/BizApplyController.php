<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/24
 */

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\helpers\StateCode;
use api_wx\modules\biz\helpers\WxBizSendHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\BizHelper;

/**
 * 企业送入驻控制器
 * Class ErrandSendController
 * @package api_wx\modules\biz\controllers
 */
class BizApplyController extends ControllerAccess
{
	/**
	 * 企业送提交入驻
	 * @return array
	 */
	public function actionApplySave()
	{

		if (BizHelper::getBizStatus($this->user_id)) {
			$this->setCodeMessage(StateCode::BUSINESS_HAVE_ENTER);

			return $this->response();
		}

		$params = [
			'user_id'         => $this->user_id,
			'biz_name'        => SecurityHelper::getBodyParam("biz_name"),
			'biz_location'    => SecurityHelper::getBodyParam("biz_location"),
			'biz_address'     => SecurityHelper::getBodyParam("biz_address"),
			'biz_address_ext' => SecurityHelper::getBodyParam("biz_address_ext"),
			'biz_mobile'      => SecurityHelper::getBodyParam("biz_mobile"),
			'biz_tag_id'      => SecurityHelper::getBodyParam('biz_tag'),
		];

		$data = BizHelper::applySave($params);
		if ($data) {

		} else {
			$this->setCodeMessage(StateCode::BUSINESS_ENTER_FAILED);
		}

		return $this->response();
	}

	/**
	 * 修改企业送信息(微信暂时不用修改)
	 * @return array
	 */
	public function actionUpdateBiz()
	{
		//

		$params = [
			'user_id'         => $this->user_id,
			'biz_location'    => SecurityHelper::getBodyParam("biz_location"),
			'biz_address'     => SecurityHelper::getBodyParam("biz_address"),
			'biz_address_ext' => SecurityHelper::getBodyParam("biz_address_ext"),
			'biz_mobile'      => SecurityHelper::getBodyParam("biz_mobile"),
		];
		$data   = WxBizSendHelper::updateBiz($params);
		if ($data) {

		} else {
			$this->setCodeMessage(StateCode::BUSINESS_UPDATE_FAILED);
		}

		return $this->response();
	}


	/**
	 * 检查是否企业送
	 * @return array
	 */
	public function actionApplyCheck()
	{
		$this->_data = ['status' => 3];//0待审核;1是企业送;2:不是企业送;3:没有提交审核的
		$data        = WxBizSendHelper::getWxBizStatus($this->user_id);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}

	/**
	 * 获取入驻标签列表
	 * @return array
	 */
	public function actionTagList()
	{
		$this->_data = BizHelper::getTagList();
		return $this->response();
	}
}