<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_worker\modules\v1\controllers;

use api_worker\modules\v1\api\ErrandBuyAPI;
use api_worker\modules\v1\helpers\StateCode;
use api_worker\modules\v1\traits\ErrandTrait;

class ErrandBuyController extends ControllerAccess
{

	/**
	 * 小帮快送小帮端 trait
	 *
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionRobbing()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerCancel()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerCancelFlow()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerDelete()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerProgress()
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


	/**
	 * 小帮抢单成功的操作
	 */
	public function actionWorkerTask()
	{
		if ($this->api_version == '1.0') {

			$data = ErrandBuyAPI::workerTaskV10($this->provider_id,$this->appData);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮详情页
	 */
	public function actionWorkerDetail()
	{
		if ($this->api_version == '1.0') {

			$data = ErrandBuyAPI::workerDetailV10($this->provider_id,$this->appData);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮添加商品费用
	 */
	public function actionAddExpense()
	{

		if ($this->api_version == '1.0') {
			$data = ErrandBuyAPI::addExpenseV10($this->provider_id);
			if ($data) {
				$this->_data    = $data;
				$this->_message = "提交成功";
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_ADD_EXPENSE);
			}
		}

		return $this->response();
	}


	//配送到达
	public function actionDeliveryArrival()
	{
		if ($this->api_version == '1.0') {
			$data = ErrandBuyAPI::arriveAndPayV10($this->provider_id);
			if ($data) {
				$this->_data    = $data;
				$this->_message = "提交成功";
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DELIVERY_ARRIVAL);
			}
		}

		return $this->response();
	}
}