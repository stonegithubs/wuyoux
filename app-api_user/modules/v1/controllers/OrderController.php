<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_user\modules\v1\controllers;

use common\helpers\orders\BizSendHelper;
use common\helpers\security\SecurityHelper;
use api_user\modules\v1\api\OrderAPI;
use api_user\modules\v1\helpers\StateCode;

class OrderController extends ControllerAccess
{
	//用户订单列表
	public function actionList()
	{
		if ($this->api_version == '1.0') {
			$data = OrderAPI::userOrderListV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}
		if ($this->api_version == '1.1') {
			$data = OrderAPI::userOrderListV11($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	//下单地址历史记录
	public function actionHistoryAddress()
	{
		if ($this->api_version == "1.0") {
			$data = OrderAPI::HistoryAddressV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}

		}

		return $this->response();
	}

	/**
	 * 企业送订单（不包括发布中）
	 * @return array
	 */
	public function actionBizOrderList()
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

	/**
	 * 企业送发布中的订单
	 * @return array
	 */
	public function actionIssueOrder()
	{
		if ($this->api_version == "1.0") {
			$data = BizSendHelper::getBizPublishOrder($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "您还没有发布中订单";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}
		if ($this->api_version == "1.1") {
			$data = BizSendHelper::getBizPublishOrderV11($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "您还没有发布中订单";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		return $this->response();
	}
}