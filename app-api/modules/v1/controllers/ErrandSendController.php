<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api\modules\v1\controllers;

use api\modules\v1\api\ErrandSendAPI;
use api\modules\v1\helpers\StateCode;
use api\modules\v1\traits\ErrandTrait;

/**
 * 帮我送控制器
 * Class ErrandSendController
 * @package api\modules\v1\controllers
 */
class ErrandSendController extends ControllerAccess
{

	/**
	 * 小帮快送通用trait
	 *
	 * @see  \api\modules\v1\traits\ErrandTrait::actionPrePayment()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionRobbing()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionUserCancel()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionUserCancelFlow()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionUserDelete()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionWorkerCancel()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionWorkerCancelFlow()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionWorkerDelete()
	 * @see  \api\modules\v1\traits\ErrandTrait::actionWorkerProgress()
	 */
	use ErrandTrait;

	////用户端
	//首页				/v1/errand-send/index	ok
	//创建订单			/v1/errand-send/create	ok
	//预支付  			/v1/errand-send/pre-payment ok
	//指定地点-计算距离	/v1/errand-send/get-range	ok
	//计价接口			/v1/errand-send/calculation	ok

	//订单任务页			/v1/errand-send/user-task
	//订单详情			/v1/errand-send/user-detail
	//订单取消 + 推送		/v1/errand-send/user-cancel
	//订单取消流程 + 推送	/v1/errand-send/user-cancel
	//订单删除			/v1/errand-send/user-delete
	//订单确认 + 推送		/v1/errand-send/user-confirm
	//订单评价(通用)		/v1/evaluate/save

	//添加小费 + 推送		/v1/errand-send/add-fee

	////小帮端
	//抢单接口(通用) + 推送	/v1/errand-send/robbing	 	(ErrandController/Robbing)
	//订单任务页				/v1/errand-send/worker-task
	//订单详情				/v1/errand-send/worker-detail
	//工作流程 + 推送			/v1/errand-send/worker-progress
	//订单取消 + 推送			/v1/errand-send/worker-cancel
	//订单取消流程 + 推送		/v1/errand-send/worker-cancel
	//订单删除				/v1/errand-send/worker-delete

	//增加费用 + 推送			/v1/errand-send/expense

	//帮我买 解决思路
	//1、首页（根据不同距离路程算出最低价格）
	//2、创建订单，支付完毕后进行推送订单
	//3、小帮抢单，同时推送通知给用户告知情况
	//4、小帮流程，1待接单，2已接单，3拨打电话，4开始配送，5配送到达，7拍照流程


	/**
	 * 首页
	 * @return array
	 */
	public function actionIndex()
	{

		if ($this->api_version == '1.0') {

			$this->_data = ErrandSendAPI::IndexV10($this->user_id);
		}

		return $this->response();
	}

	/**
	 * 指定地点-计算距离
	 * @return array
	 */
	public function actionGetRange()
	{

		if ($this->api_version == '1.0') {
			$this->_data = ErrandSendAPI::getRangeV10($this->user_id);
		}

		return $this->response();
	}

	/**
	 * 新建快送订单
	 */
	public function actionCreate()
	{

		if ($this->api_version == '1.0') {

			$res = ErrandSendAPI::createOrderV10($this->user_id);
			if ($res) {
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

			$res = ErrandSendAPI::orderCalcV10($this->user_id);
			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_CREATE_FAILED);
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

			$data = ErrandSendAPI::UserTaskV10();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_DETAIL);
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

			$data = ErrandSendAPI::userDetailV10();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮抢单成功的操作
	 */
	public function actionWorkerTask()
	{
		if ($this->api_version == '1.0') {

			$data = ErrandSendAPI::workerTaskV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_DETAIL);
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

			$data = ErrandSendAPI::workerDetailV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮拍照流程
	 */
	public function actionTakePhoto()
	{
		if ($this->api_version == '1.0') {
			$data = ErrandSendAPI::takePhotoV10($this->provider_id);
			if ($data) {
				$this->_data    = $data;
				$this->_message = '照片已提交';
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_CONFIRM);
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
			$data = ErrandSendAPI::userConfirmV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_CONFIRM);
			}
		}

		return $this->response();
	}

	/**
	 * 增加小费
	 * @return array
	 */
	public function actionAddFee()
	{
		if ($this->api_version == '1.0') {
			$res = ErrandSendAPI::addFeeV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
			$this->_message ? null : $this->_message = "小费添加成功";
		}

		return $this->response();
	}
}