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
use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use Yii;

class CouponController extends ControllerAccess
{


	public function actionList()
	{
		$status    = SecurityHelper::getBodyParam('status');
		$card_type = SecurityHelper::getBodyParam('card_type', Ref::ORDER_TYPE_ERRAND);
		$data      = CouponHelper::getList($this->user_id, $card_type, $status);
		if (is_array($data) && count($data) > 0) {
			$this->_data = $data;
		} else {

			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}


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

	public function actionIndex()
	{
		$params['user_id']  = $this->user_id;
		$params['page']     = SecurityHelper::getBodyParam('page');
		$params['pageSize'] = SecurityHelper::getBodyParam('page_size');
		$data               = CouponHelper::CardList($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

}

