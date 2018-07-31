<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\api\ErrandBuyAPI;
use api_user\modules\v1\helpers\StateCode;
use api_user\modules\v1\traits\ErrandTrait;
use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;

class ErrandBuyController extends ControllerAccess
{

	/**
	 * 小帮快送通用trait
	 *
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionPrePayment()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionRobbing()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionUserCancel()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionUserCancelFlow()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionUserDelete()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionWorkerCancel()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionWorkerCancelFlow()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionWorkerDelete()
	 * @see  \api_user\modules\v1\traits\ErrandTrait::actionWorkerProgress()
	 */
	use ErrandTrait;


	////用户端
	//首页				/v1/errand-buy/index
	//创建订单			/v1/errand-buy/create
	//预支付(通用)		/v1/errand-buy/pre-payment  (ErrandController/PrePayment)
	//指定地点-计算距离	/v1/errand-buy/get-range
	//计价接口			/v1/errand-buy/calculation
	//订单任务页			/v1/errand-buy/user-task
	//订单详情			/v1/errand-buy/user-detail
	//订单取消 + 推送		/v1/errand-buy/user-cancel
	//订单取消流程 + 推送	/v1/errand-buy/user-cancel
	//订单确认 + 推送		/v1/errand-buy/user-confirm
	//订单删除			/v1/errand-buy/user-delete
	//订单评价(通用)		/v1/evaluate/save
	//商品费用付款 + 推送	/v1/errand-buy/pay-expense


	////小帮端
	//抢单接口(通用) + 推送	/v1/errand-buy/robbing	 	(ErrandController/Robbing)
	//订单任务页				/v1/errand-buy/worker-task
	//订单详情				/v1/errand-buy/worker-detail
	//工作流程 + 推送			/v1/errand-buy/worker-progress
	//订单取消 + 推送			/v1/errand-buy/worker-cancel
	//订单取消流程 + 推送		/v1/errand-buy/worker-cancel
	//订单删除				/v1/errand-buy/worker-delete
	//增加费用 + 推送			/v1/errand-buy/expense


	//帮我买 解决思路
	//1、首页（根据不同距离路程算出最低价格）
	//2、创建订单，支付完毕后进行推送订单
	//3、小帮抢单，同时推送通知给用户告知情况
	//4、小帮流程，1待接单，2已接单，3拨打电话，4开始配送，5配送到达，6商品费用

	//首页
	public function actionIndex()
	{

		if ($this->api_version == '1.0') {

			$this->_data = ErrandBuyAPI::IndexV10($this->user_id);
		}

		return $this->response();
	}

	//指定地点-计算距离
	public function actionGetRange()
	{

		if ($this->api_version == '1.0') {
			$this->_data = ErrandBuyAPI::getRangeV10($this->user_id);
		}

		return $this->response();
	}

	/**
	 * 新建快送订单
	 */
	public function actionCreate()
	{

		if ($this->api_version == '1.0') {
			$userInfo       = UserHelper::getUserInfo($this->user_id);
			$user_city_id   = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
			$user_location  = SecurityHelper::getBodyParam('user_location');
			$start_location = SecurityHelper::getBodyParam('start_location');
			$check          = RegionHelper::checkCurrentRegionAndOpening($user_location, $start_location, Ref::CATE_ID_FOR_ERRAND_BUY, $user_city_id);
			if (!$check['pass']) {
				$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);
				$this->_message = $check['message'];

				return $this->response();
			}

			$res = ErrandBuyAPI::createOrderV10($this->user_id, $user_city_id);
			if ($res) {
				//TODO 获取最合适的优惠券
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 获取订单计算明细
	 */
	public function actionCalculation()
	{

		if ($this->api_version == '1.0') {

			$res = ErrandBuyAPI::orderCalcV10($this->user_id);
			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_CREATE_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 用户流程
	 */
	public function actionUserTask()
	{

		if ($this->api_version == '1.0') {

			$data = ErrandBuyAPI::UserTaskV10();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 用户明细
	 */
	public function actionUserDetail()
	{
		if ($this->api_version == '1.0') {

			$data = ErrandBuyAPI::userDetailV10();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 订单确认
	 */
	public function actionUserConfirm()
	{

		if ($this->api_version == '1.0') {
			$data = ErrandBuyAPI::userConfirmV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_CONFIRM);
			}
		}

		return $this->response();
	}

	/**
	 * 用户支付商品费用
	 * @return array
	 */
	public function actionPayExpense()
	{
		if ($this->api_version == '1.0') {
			$res = ErrandBuyAPI::payExpenseV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();
	}
}
