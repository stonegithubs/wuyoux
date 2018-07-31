<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\helpers\StateCode;
use api_wx\modules\mpv1\helpers\WxOrderHelper;
use common\helpers\orders\BizSendHelper;
use common\helpers\security\SecurityHelper;

class OrderController extends ControllerAccess
{
	//小帮快送订单列表
	public function actionErrandList()
	{
		$params['user_id']  = $this->user_id;
		$params['status']   = SecurityHelper::getBodyParam('status');
		$params['page']     = SecurityHelper::getBodyParam('page');
		$params['pageSize'] = SecurityHelper::getBodyParam('pageSize');
		$params['select']   = SecurityHelper::getBodyParam('select');
		$data               = WxOrderHelper::getList($params);
		$this->_data        = $data;

		return $this->response();
	}

	//小帮出行订单列表
	public function actionTripList()
	{
		$params['user_id']   = $this->user_id;
		$params['status']    = SecurityHelper::getBodyParam('status');
		$params['page']      = SecurityHelper::getBodyParam('page');
		$params['page_size'] = SecurityHelper::getBodyParam('page_size');
		$this->_data         = WxOrderHelper::getTripList($params);

		return $this->response();
	}

	//企业送订单列表
	public function actionBizList()
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

	//企业送发布中订单
	public function actionBizPublish()
	{
		$data = BizSendHelper::getBizPublishOrderV11($this->user_id);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}


}