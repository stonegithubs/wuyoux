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
use common\helpers\payment\CouponHelper;
use common\helpers\security\SecurityHelper;

class BizCouponController extends ControllerAccess
{
	/**
	 * 优惠券列表
	 * @return array
	 */
	public function actionCardList()
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
	 * 企业送订单可用优惠券
	 * @return array
	 */
	public function actionOrderCard()
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

