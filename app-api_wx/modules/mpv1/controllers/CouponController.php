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
use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use Yii;

class CouponController extends ControllerAccess
{

	/**
	 * 快送和出行 优惠券列表
	 * @return array
	 */
	public function actionCardList()
	{
		$params['user_id']   = $this->user_id;
		$params['page']      = SecurityHelper::getBodyParam('page');
		$params['page_size'] = SecurityHelper::getBodyParam('pageSize');
		$data                = CouponHelper::CardList($params);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}

	/**
	 * 快送和出行 订单可使用的优惠券
	 * @return array
	 */
	public function actionOrderCard()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$data               = CouponHelper::showOrderCard($params);
		if (is_array($data) && count($data) > 0) {
			$this->_data = $data;
		} else {

			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	/**
	 * 企业送 优惠券列表
	 * @return array
	 */
	public function actionBizCardList()
	{
		$data = CouponHelper::bizCardList($this->user_id);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	/**
	 * 企业送 订单可使用的优惠券
	 * @return array
	 */
	public function actionBizOrderCard()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$data     = CouponHelper::showBizOrderCard($order_no,$this->user_id);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

}

