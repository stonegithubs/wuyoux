<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\helpers\StateCode;
use common\helpers\orders\BizSendHelper;
use common\helpers\security\SecurityHelper;
use Yii;

class BizOrderController extends ControllerAccess
{

	public function actionOrderList()
	{
		$params['user_id']   = $this->user_id;
		$params['status']    = SecurityHelper::getBodyParam('status');
		$params['page']      = SecurityHelper::getBodyParam('page');
		$params['page_size'] = SecurityHelper::getBodyParam('page_size');
		$data                = BizSendHelper::getBizOrderList($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	public function actionIssueOrder()
	{
		if($this->api_version == '1.0'){
			$data = BizSendHelper::getBizPublishOrder($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		if($this->api_version == '1.1'){
			$data = BizSendHelper::getBizPublishOrderV11($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}

		}

		return $this->response();
	}
}

