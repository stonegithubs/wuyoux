<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/16
 */

namespace api_worker\modules\v1\controllers;

use api_worker\modules\v1\api\BizSendAPI;
use api_worker\modules\v1\api\ErrandAPI;
use api_worker\modules\v1\helpers\StateCode;
use api_worker\modules\v1\helpers\WorkerOrderHelper;
use api_worker\modules\v1\traits\ErrandTrait;
use common\helpers\orders\BizSendHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\PushHelper;

class BizSendController extends ControllerAccess
{
	//1、抢单接口 robbing actionRobbing ok
	//2、抢单成功页 tmp-task actionTmpTask ok
	//3、录入电话创建订单  create-order actionCreateOrder ok

	//4.1、订单任务页 worker-task actionWorkerTask ok
	//4.2、订单详情 worker-detail actionWorkerDetail ok
	//4.3、配送到达 delivery-arrival actionDeliveryArrival ok

	//5、小帮取消订单 worker-cancel actionWorkerCancel ok
	//6、小帮取消订单流程工作流 worker-cancel-flow actionWorkerCancelFlow ok
	//7、订单删除 //actionWorkerDelete ok

	//临时发单申请取消
	//临时发单取消工作流

	/**
	 * 小帮抢单
	 */
	public function actionRobbing()
	{
		if ($this->api_version == '1.0') {
			$judgeBail = ShopHelper::judgeBail($this->provider_id); //判断商家的保证金是否符合要求
			if (!$judgeBail) {
				$this->setCodeMessage(StateCode::SHOP_CANT_ROB);

				return $this->response();
			}

//			if (WorkerOrderHelper::judgeBizOrder($this->provider_id)) {
//				$this->setCodeMessage(StateCode::SHOP_DOING_BIZ_ORDER);
//
//				return $this->response();
//			}

			$blackList = ShopHelper::isBlacklist($this->provider_id);
			if ($blackList) {
				$this->setCodeMessage(StateCode::SHOP_BLACK);

				return $this->response();
			}

			$data = BizSendAPI::robbingV10($this->user_id, $this->provider_id);
			if ($data) {
				$this->_data = $data;
				BizSendHelper::pushTmpToUserNotice($data['tmp_no'], PushHelper::TMP_BIZ_SEND_WORKER_TASK);
			} else {
				$this->setCodeMessage(StateCode::ERRAND_ROBBING);
			}
		}

		return $this->response();
	}

	/**
	 * 临时订单任务页
	 * @return array
	 */
	public function actionTmpTask()
	{
		if ($this->api_version == '1.0') {

			$data = BizSendAPI::TmpTaskV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 录入电话创建订单
	 * @return array
	 */
	public function actionCreateOrder()
	{

		if ($this->api_version == '1.0') {

			$data = BizSendAPI::createOrderV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
				BizSendHelper::pushTmpToUserNotice($data['tmp_no'], PushHelper::TMP_BIZ_SEND_WORKER_INPUT);
			} else {
				$this->setCodeMessage(StateCode::BIZ_SEND_CREATE_ORDER_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 发单取消订单
	 * @return array
	 */
	public function actionTmpCancel()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::tmpOrderCancelV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
				BizSendHelper::pushTmpToUserNotice($data['tmp_no'], PushHelper::TMP_BIZ_SEND_WORKER_CANCEL);
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_PROGRESS);
			}
		}

		return $this->response();
	}

	/**
	 * 订单任务页
	 * @return array
	 */
	public function actionWorkerTask()
	{
		if ($this->api_version == '1.0') {

			$data = BizSendAPI::workerTaskV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::BIZ_SEND_WORKER_TASK);
			}
		}

		return $this->response();
	}

	/**
	 * 订单详情
	 * @return array
	 */
	public function actionWorkerDetail()
	{
		if ($this->api_version == '1.0') {

			$data = BizSendAPI::workerDetailV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮删除订单
	 */
	public function actionWorkerDelete()
	{

		if ($this->api_version == '1.0') {
			$data = BizSendAPI::workerDeleteV10($this->provider_id);
			if ($data) {
				$this->_message = '删除成功';
				$this->_data    = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_DELETE);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮取消订单
	 */
	public function actionWorkerCancel()
	{

		if ($this->api_version == '1.0') {
			$data = BizSendAPI::workerCancelV10($this->provider_id);
			if ($data) {
				$this->_message = '申请成功待用户处理';
				$this->_data    = $data;
				BizSendHelper::pushToUserNotice($data['order_no'], BizSendHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_CANCEL);
			}
		}

		return $this->response();
	}

	/**
	 * 取消订单工作流
	 */
	public function actionWorkerCancelFlow()
	{
		if ($this->api_version == '1.0') {

			$data = BizSendAPI::workerCancelFlowV10($this->provider_id);
			if ($data) {
				$this->_message = "提交成功";
				$this->_data    = $data;
				BizSendHelper::pushToUserNotice($data['order_no'], BizSendHelper::PUSH_USER_TYPE_CANCEL_PROGRESS, $data);
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_CANCEL);
			}
		}

		return $this->response();
	}

	/**
	 * 配送到达
	 */
	public function actionDeliveryArrival()
	{
		if ($this->api_version == '1.0') {
			$res = BizSendAPI::deliveryArrivalV10($this->provider_id);
			if ($res) {
				$this->_data    = $res;
				$this->_message = "配送到达,请耐心等待用户付款！";
			} else {
//				$this->setCodeMessage(StateCode::ERRAND_WORKER_PROGRESS);
			}
		}

		return $this->response();
	}
}





