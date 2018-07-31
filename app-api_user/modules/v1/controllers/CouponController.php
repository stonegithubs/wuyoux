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
use common\helpers\payment\CouponHelper;
use common\helpers\security\SecurityHelper;

class CouponController extends ControllerAccess
{

	/**
	 * 用户订单优惠券
	 * @return array
	 */
	public function actionOrderCard()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$data               = CouponHelper::showOrderCard($params);
		if (is_array($data) && count($data) > 0) {
			$this->_data = $data;
		} else {

			$this->setCodeMessage(StateCode::COUPON_EMPTY_DATA);
		}

		return $this->response();
	}

	/**
	 * 用户优惠券列表
	 * @return array
	 */
	public function actionIndex()
	{
		$params['user_id']   = $this->user_id;
		$params['page']      = SecurityHelper::getBodyParam('page');
		$params['page_size'] = SecurityHelper::getBodyParam('page_size');
		$data                = [];
		if ($this->api_version == '1.0') {

			$data = CouponHelper::CardList($params);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::COUPON_EMPTY_DATA);
			}
		}
		if ($this->api_version == '1.1') {
			$data        = CouponHelper::CardListV11($params);
			$this->_data = $data;
			/*$pagination = $data['pagination'];
			if ($pagination['totalCount'] > 0) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::PAGE_EMPTY_DATA);
			}*/
		}

		return $this->response();
	}

	/**
	 * 企业送优惠券列表
	 * @return array
	 */
	public function actionBizCardList()
	{
		$data = CouponHelper::bizCardAvailableData($this->user_id);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::COUPON_EMPTY_DATA);
		}


		return $this->response();
	}

	/**
	 * 企业送订单可用优惠券
	 * @return array
	 */
	public function actionBizOrderCard()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$data     = CouponHelper::showBizOrderCard($order_no, $this->user_id);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::COUPON_EMPTY_DATA);
		}

		return $this->response();
	}

}

