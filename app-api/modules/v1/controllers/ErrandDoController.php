<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api\modules\v1\controllers;

use api\modules\v1\api\ErrandDoAPI;
use api\modules\v1\helpers\StateCode;
use api\modules\v1\traits\ErrandTrait;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\security\SecurityHelper;
use Yii;

class ErrandDoController extends ControllerAccess
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

	/**
	 * 首页
	 */
	public function actionIndex()
	{

		if ($this->api_version == '1.0') {

			$this->_data = ErrandDoAPI::IndexV10($this->user_id);
		}

		return $this->response();
	}

	/**
	 * 获取单价
	 */
	public function actionGetServicePrice()
	{
		if ($this->api_version == '1.0') {

			$this->_data = ErrandDoAPI::getServicePriceV10($this->user_id);
		}

		return $this->response();
	}

	/**
	 * 新建快送订单
	 */
	public function actionCreate()
	{

		if ($this->api_version == '1.0') {

			$res = ErrandDoAPI::createOrderV10($this->user_id);
			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_CREATE);
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

			$res = ErrandDoAPI::orderCalcV10($this->user_id);
			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_SEND_CREATE_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 订单明细
	 */
	public function actionDetail()
	{
		if ($this->api_version == '1.0') {

			$res = ErrandDoAPI::userDetailV10();
			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_DETAIL);
			}
		}

		return $this->response();
	}


	/**
	 * 小帮任务页
	 */
	public function actionWorkerTask()
	{

		if ($this->api_version == '1.0') {

			$data = ErrandDoAPI::workerTaskV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_DETAIL);
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
			$data = ErrandDoAPI::workerDetailV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_DETAIL);
			}
		}

		return $this->response();
	}


	/**
	 * 订单确认
	 */
	public function actionConfirm()
	{

		if ($this->api_version == '1.0') {

			$data = ErrandDoAPI::userConfirmV10($this->user_id);
			if ($data) {

				$data['current_page'] = "finish";        //这里显示帮我办和其他快送不一样的
				$detail               = $this->_data = ErrandDoHelper::userDetail($data);
				$detail ? $this->_data = $detail : $this->setCodeMessage(StateCode::ERRAND_DETAIL);
				$this->_message = "订单已确认";
			} else {

				$this->setCodeMessage(StateCode::ERRAND_USER_CONFIRM);
			}
		}

		return $this->response();
	}


	//1、未接单 订单还没有人接单，用户取消（并退款）

	//已接订单由用户发起
	//2-1、更新errand order 透传用户申请取消 已接订单 进入退单流程

	//已接订单由小帮发起
	//3-1、更新errand order 透传小帮申请取消 已接订单 进入退单流程

	//退单流程
	//由用户发起的
	//1-1、小帮同意后，订单取消成功，结果透传通知给用户
	//1-2、小帮不同意，订单取消失败，结果透传通知给用户，后续转线下处理，结果透传通知给小帮和用户

	//由小帮发起的
	//1-1、用户同意后，订单取消成功，结果透传通知给小帮
	//1-2、用户不同意，订单取消失败，结果透传通知给小帮，后续转线下处理，结果透传通知给小帮和用户

	public function actionPlatformCancel()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$data     = ErrandDoHelper::platformCancel($order_no);
		if ($data) {
			$this->_message = "平台取消成功";
		} else {

			$this->setCodeMessage(StateCode::ERRAND_DELETE);
		}

		return $this->response();
	}


	//交易流水表

	//订单金额计算方式
	//新建订单   余额冻结用户金额(bb_51_user [money,freeze_money])
	//支付成功 支出收支明细表(bb_51_income_pay)

	//完成订单
	//	用户 余额方式扣除用户金额(bb_51_user [freeze_money] ) 写入数据 history_money
	//	用户 在线宝扣除
	//	用户 优惠券金额 扣除管理员
	//	小帮 累加小帮金额(bb_51_shops[shops_money,shops_history_money]) ->加入收入明细(bb_51_income_shop)

	//取消订单
	// 余额解冻用户金额(bb_51_user [money,freeze_money])
	//退款成功 收入收支明细表(bb_51_income_pay)

	//添加
	//增加小费 -> 小费表  流水表
	//回调 流水表 ->更新小费表 ->更新订单总小费 ->推送消息给小帮（已接单）

	/**
	 * 增加小费
	 * @return array
	 */
	public function actionAddFee()
	{
		if ($this->api_version == '1.0') {
			$res = ErrandDoAPI::addFeeV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
			$this->_message ? null : $this->_message = "小费添加成功";
		}

		return $this->response();
	}
}