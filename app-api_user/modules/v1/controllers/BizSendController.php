<?php
/**
 * Created by PhpStorm.
 * User: JasonLeung
 * Date: 2018/1/19
 * Time: 11:27
 */

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\api\BizSendAPI;
use api_user\modules\v1\helpers\StateCode;
use api_user\modules\v1\helpers\UserBizSendHelper;
use common\helpers\orders\BizSendHelper;


class BizSendController extends ControllerAccess
{


	//临时发单部分 Start
	/**
	 * @see actionTmpHome           1、发单首页
	 * @see actionTmpOrderNow       2、一键发单
	 * @see actionTmpCheckOrder     3、检测发单
	 * @see actionTmpCancelOrder    4、取消发单
	 * @see actionTmpCancelConfirm  5、确认取消信息
	 * @see actionTmpOrderView 		6、发布中订单详情
	 * @see actionAddDistrict       7、添加配送区域
	 * @see actionDeleteDistrict    8、删除用户配送区域
	 */

	//1、发单首页
	public function actionTmpHome()
	{
		//检查是否支付
		if ($this->api_version == '1.0') {
			$data           = BizSendAPI::HomeV10($this->user_id);
			$this->_data    = $data['data'];
			$this->_message = $data['message'];
		}

		//检查是否支付
		if ($this->api_version == '1.1') {
			$data           = BizSendAPI::HomeV11($this->user_id);
			$this->_data    = $data['data'];
			$this->_message = $data['message'];
		}

		return $this->response();
	}

	//2、一键发单
	public function actionTmpOrderNow()
	{
		if ($this->api_version == '1.0') {
			if (BizSendHelper::checkBizOrderStatus($this->user_id)) {
				$this->setCodeMessage(StateCode::BIZ_SEND_NEED_PAY);

				return $this->response();
			}

			if (BizSendHelper::checkBizUserBalance($this->user_id)) {
				$this->setCodeMessage(StateCode::BIZ_SEND_BALANCE_RECHARGE);

				return $this->response();
			}

			$data = BizSendAPI::orderNowV10($this->user_id);

			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "创建订单失败";
			}
		}

		if ($this->api_version == '1.1') {
			if (BizSendHelper::checkBizOrderStatus($this->user_id)) {
				$this->setCodeMessage(StateCode::BIZ_SEND_NEED_PAY);

				return $this->response();
			}

			if (BizSendHelper::checkBizUserBalance($this->user_id)) {
				$this->setCodeMessage(StateCode::BIZ_SEND_BALANCE_RECHARGE);

				return $this->response();
			}

			$data = BizSendAPI::orderNowV11($this->user_id);

			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "创建订单失败";
			}
		}

		return $this->response();
	}

	//3、检测接单
	public function actionTmpCheckOrder()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::tmpCheckOrderV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {

				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	//4、取消发单
	public function actionTmpCancelOrder()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::cancelOrderV10($this->user_id);

			if ($data) {

			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "取消订单失败";
			}
		}

		return $this->response();
	}

	//5、确认取消信息
	public function actionTmpCancelConfirm()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::tmpCancelConfirmV10($this->user_id);
			if ($data) {
				$this->_message = "提交成功";
				$this->_data    = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
			}
		}

		return $this->response();
	}

	//6、发布中订单详情
	public function actionTmpOrderView()
	{
		if ($this->api_version == '1.0') {

			$data = BizSendAPI::tmpViewV10($this->user_id);

			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "获取失败订单或被改派";
			}
		}

		return $this->response();
	}

	//7、添加用户配送区域
	public function actionAddDistrict()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::addDistrictV10($this->user_id);
			$this->setCodeMessage($data['code']);
		}

		return $this->response();
	}

	//8、删除用户配送区域
	public function actionDeleteDistrict()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::deleteDistrictV10($this->user_id);
			$this->setCodeMessage($data['code']);
		}

		return $this->response();
	}
	//临时发单部分 End

	//真实订单部分 Start
	/**
	 * @see actionUserTask                  1 订单任务页
	 * @see actionGetSinglePaymentDetail    2.1 获取单张支付信息
	 * @see actionPrePaymentSingle          2.2 单张订单预支付
	 * @see xxx                             2.3 单张优惠券列表  TODO 需要写
	 * @see actionGetBatchPaymentDetail      3.1 获取批量支付信息
	 * @see actionPrePaymentBatch            3.2 批量订单预支付
	 * @see actionGetCouponMatchList         3.3 获取优惠券匹配列表
	 * @see actionSmartCouponCal             3.4 智能计算优惠券匹配
	 * @see actionUserCancel                4 申请取消订单
	 * @see actionUserCancelFlow            5 取消订单工作流
	 * @see actionUserDetail                6 订单详情页
	 * @see actionUserDelete                7 删除订单
	 * @see actionValuationDetail           8 计价明细
	 */

	//1、订单任务页
	public function actionUserTask()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::userTaskV10();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::BIZ_SEND_DETAIL);
			}
		}

		return $this->response();
	}

	//2.1 获取单张支付信息
	public function actionGetSinglePaymentDetail()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::GetAutoCouponPaymentV10($this->user_id);
			if ($data) {
				$this->_message = "提交成功";
				$this->_data    = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	//2.2 单张订单预支付
	public function actionPrePaymentSingle()
	{
		if ($this->api_version == '1.0') {
			$data = UserBizSendHelper::prePayment();
			$this->setCodeMessage($data['code']);
			$this->_data = $data['data'];
		}

		return $this->response();
	}

	//TODO 优惠券列表 看看能不能用旧列表
	public function actionCouponList()
	{

	}

	//3.1 获取批量支付信息
	public function actionGetBatchPaymentDetail()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::getBatchPaymentDetailV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	//3.2 批量订单预支付
	public function actionPrePaymentBatch()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::prePaymentBatchV10($this->user_id);
			$this->setCodeMessage($data['code']);
			$this->_data = $data['data'];
		}

		return $this->response();
	}


	//3.3 获取优惠券匹配列表
	public function actionGetCouponMatchList()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::getCouponMatchListV10($this->user_id);
			$this->setCodeMessage($data['code']);
			$this->_data = $data['data'];
		}

		return $this->response();
	}

	//3.4 智能计算优惠券匹配
	public function actionSmartCouponCal()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::smartCouponCalV10($this->user_id);
			$this->setCodeMessage($data['code']);
			$this->_data = $data['data'];
		}

		return $this->response();
	}

	//4、申请取消订单
	public function actionUserCancel()
	{
		$data = BizSendAPI::userCancelV10($this->user_id);
		if ($data) {
			$this->_message = "申请成功待小帮处理";
			$this->_data    = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
		}

		return $this->response();
	}

	//5、取消订单工作流
	public function actionUserCancelFlow()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::userCancelFlow($this->user_id);
			if ($data) {
				$this->_message = '提交成功';
			} else {
				$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
			}
		}

		return $this->response();
	}

	//6、订单详情页
	public function actionUserDetail()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::userDetailV10();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::BIZ_SEND_DETAIL);
			}
		}

		return $this->response();
	}

	//7、删除订单
	public function actionUserDelete()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::userDeleteV10($this->user_id);
			if ($data) {
				$this->_message = "删除成功";
			} else {

				$this->setCodeMessage(StateCode::ERRAND_DELETE);
			}
		}

		return $this->response();
	}

//	//8、计价明细
//	public function actionValuationDetail()
//	{
//		if ($this->api_version == '1.0') {
//			$data = BizSendAPI::valuationDetailV10();
//			if ($data) {
//				$this->_data = $data;
//			} else {
//				$this->_code    = StateCode::BIZ_SEND_DETAIL;
//				$this->_message = "暂无订单计价明细";
//			}
//		}
//
//		return $this->response();
//	}

	public function actionSaveGuide()
	{
		if ($this->api_version == '1.0') {
			$data = BizSendAPI::saveGuideV10($this->user_id);
			if ($data) {

			} else {

				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
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

			$data = BizSendAPI::userConfirmV10($this->user_id);
			if ($data) {

				$this->_message = "订单已确认";
			} else {

				$this->setCodeMessage(StateCode::ERRAND_USER_CONFIRM);
			}
		}

		return $this->response();
	}

}