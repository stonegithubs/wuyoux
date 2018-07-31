<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\api\BizSendAPI;
use api_wx\modules\biz\helpers\StateCode;
use api_wx\modules\biz\helpers\WxBizHelper;
use api_wx\modules\biz\helpers\WxBizSendHelper;
use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\BizHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UrlHelper;


/**
 * 企业送控制器
 * Class BizSendController
 * @package api_wx\modules\biz\controllers
 */
class BizSendController extends ControllerAccess
{
	//临时发单部分 Start
	/**
	 * @see actionTmpHome           1、发单首页
	 * @see actionTmpOrderNow       2、一键发单
	 * @see actionTmpCheckOrder     3、检测发单
	 * @see actionTmpCancelOrder    4、取消发单
	 * @see actionTmpCancelConfirm  5、确认取消信息
	 * @see actionTmpOrderView      6、发布中订单详情
	 * @see actionAddDistrict       7、添加配送区域
	 * @see actionDeleteDistrict    8、删除用户配送区域
	 */

	//1、发单首页
	public function actionTmpHome()
	{
		if ($this->api_version == '1.0') {
			//检查是否支付
			if (BizSendHelper::checkBizOrderStatus($this->user_id)) {
				$this->_data    = 'not_pay';
				$this->_message = "请支付配送到达的订单才能继续下单";

				return $this->response();
			}

			//检查余额是否低于30元
			if (BizSendHelper::checkBizUserBalance($this->user_id)) {
				$this->_data    = 'not_balance';
				$this->_message = "您的账户余额已低于30元，请及时充值";

				return $this->response();
			}

			//检查是否下单
			if (BizSendHelper::getBizSendHome($this->user_id)) {
				$this->_data = 'not_finish';
			}
		}

		if ($this->api_version == '1.1') {
			//配送区域
			$bizDistrict      = BizSendHelper::getDistrict($this->user_id);
			$data['district'] = $bizDistrict;
			$data['status']   = 'normal';

			//检查是否支付
			if (BizSendHelper::checkBizOrderStatus($this->user_id)) {
				$data['status'] = 'not_pay';
				$this->_data    = $data;
				$this->_message = "请支付配送到达的订单才能继续下单";

				return $this->response();
			}

			//检查余额是否低于30元
			if (BizSendHelper::checkBizUserBalance($this->user_id)) {
				$data['status'] = 'not_balance';
				$this->_data    = $data;
				$this->_message = "您的账户余额已低于30元，请及时充值";

				return $this->response();
			}

			//检查是否下单
			if (BizSendHelper::getBizSendHome($this->user_id)) {
				$data['status'] = 'not_finish';
			}

			$this->_data = $data;

		}

		return $this->response();
	}

	//2、一键发单
	public function actionTmpOrderNow()
	{

		if ($this->api_version == '1.0') {

			if (BizSendHelper::checkBizOrderStatus($this->user_id)) {
				$this->_data    = 'not_pay';
				$this->_message = "请支付配送到达的订单才能继续下单";

				return $this->response();
			}

			if (BizSendHelper::checkBizUserBalance($this->user_id)) {
				$this->_data    = 'not_balance';
				$this->_message = "您的账户余额已低于30元，请及时充值";

				return $this->response();
			}

			$res = BizSendAPI::orderNowV10($this->user_id);
			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
			}

			return $this->response();
		}

		if ($this->api_version == '1.1') {
			if (BizSendHelper::checkBizOrderStatus($this->user_id)) {
				$this->_data    = 'not_pay';
				$this->_message = "请支付配送到达的订单才能继续下单";

				return $this->response();
			}

			if (BizSendHelper::checkBizUserBalance($this->user_id)) {
				$this->_data    = 'not_balance';
				$this->_message = "您的账户余额已低于30元，请及时充值";

				return $this->response();
			}

			$data = BizSendAPI::orderNowV11($this->user_id);

			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = "创建订单失败";
			}

			return $this->response();
		}
	}

	//3、检测接单情况
	public function actionTmpCheckOrder()
	{

		if ($this->api_version == '1.0') {
			$batch_no = SecurityHelper::getBodyParam("batch_no");
			$data     = WxBizSendHelper::getLastTmpOrder($this->user_id, $batch_no);
			if ($data) {
				$this->_data = $data;
			} else {

				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		if ($this->api_version == '1.1') {
			$params['batch_no'] = SecurityHelper::getBodyParam('batch_no');
			$params['user_id']  = $this->user_id;
			$data               = WxBizSendHelper::tmpOrderDetail($params);
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
		$batch_no = SecurityHelper::getBodyParam("batch_no");
		$result   = WxBizSendHelper::cancelOrder($this->user_id, $batch_no);

		if ($result) {

		} else {
			$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
		}

		return $this->response();
	}

	//5、确认取消信息
	public function actionTmpCancelConfirm()
	{
		$params['tmp_no']  = SecurityHelper::getBodyParam('tmp_no');
		$params['user_id'] = $this->user_id;
		$data              = WxBizSendHelper::userTmpOrderCancelConfirm($params);
		if ($data) {
			$this->_message = "提交成功";
			$this->_data    = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
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
	 * @see actionGetBatchPaymentDetail      3.1 获取批量支付信息
	 * @see actionPrePaymentBatch            3.2 批量订单预支付
	 * @see actionGetCouponMatchList         3.3 获取优惠券匹配列表
	 * @see actionSmartCouponCal             3.4 智能计算优惠券匹配
	 * @see actionUserCancel                4 申请取消订单
	 * @see actionUserCancelFlow            5 取消订单工作流
	 * @see actionUserDetail                6 订单详情页
	 * @see actionUserDelete                7 删除订单
	 *
	 */

	//1、订单任务页
	public function actionUserTask()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'task';

		$data = BizSendHelper::userTaskAndDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::BIZ_SEND_DETAIL);
		}

		return $this->response();
	}

	//2.1 获取单张支付信息
	public function actionGetSinglePaymentDetail()
	{
		$params['user_id']  = $this->user_id;
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['card_id']  = SecurityHelper::getBodyParam('card_id');
		$data               = WxBizSendHelper::getAutoCouponPayment($params);
		if ($data) {
			$this->_message = "提交成功";
			$this->_data    = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	//2.2 单张订单预支付
	public function actionPrePaymentSingle()
	{
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['card_id']    = SecurityHelper::getBodyParam('card_id');
		$payment_id           = SecurityHelper::getBodyParam('payment_id');
		$params['payment_id'] = $payment_id;
		$orderHelper          = new OrderHelper();
		$orderRes             = $orderHelper->generatePrePaymentSingle($params);

		$result = [
			'code' => 0,
			'data' => null
		];

		if ($orderRes) {

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = BizSendHelper::orderPaymentSuccess($orderRes['transaction_no'], $trade_no, $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败 //TODO 支付记录
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("mini-biz-wxpay/biz-order-payment");
				$payParams['openid']         = $this->openid;
				$payRes                      = WxpayHelper::minAppBizOrder($payParams);
				$payRes ? $result['data']['payInfo'] = $payRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];

		return $this->response();
	}

	//3.1 获取批量支付信息
	public function actionGetBatchPaymentDetail()
	{
		$params['user_id'] = $this->user_id;
		$data              = WxBizSendHelper::getBatchPaymentDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	//3.2 批量订单预支付
	public function actionPrePaymentBatch()
	{

		$params['user_id']    = $this->user_id;
		$payment_id           = SecurityHelper::getBodyParam('payment_id');
		$params['payment_id'] = $payment_id;

		if (!$orderData = BizSendHelper::getPaymentCache($params)) {
			$this->_code    = StateCode::BIZ_SEND_PAY_BATCH_EXPIRE;
			$this->_message = "请求支付数据过期,请重试";

			return $this->response();
		}

		$orderHelper = new OrderHelper();
		$orderRes    = $orderHelper->generatePrePaymentBatch($orderData, $params);
		$result      = [
			'code' => 0,
			'data' => null
		];

		if ($orderRes) {

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = BizSendHelper::orderPaymentSuccess($orderRes['transaction_no'], $trade_no, $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("mini-biz-wxpay/biz-order-payment");
				$payParams['openid']         = $this->openid;
				$payRes                      = WxpayHelper::minAppBizOrder($payParams);
				$payRes ? $result['data']['payInfo'] = $payRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];

		return $this->response();
	}

	//3.3 获取优惠券匹配列表
	public function actionGetCouponMatchList()
	{

		$params['user_id'] = $this->user_id;
		if (!$orderData = BizSendHelper::getPaymentCache($params)) {
			$this->_code    = StateCode::BIZ_SEND_PAY_BATCH_EXPIRE;
			$this->_message = "请求支付数据过期,请重试";

			return $this->response();
		}

		$this->_data = BizSendHelper::getCouponMatchData($orderData, $this->user_id);

		return $this->response();
	}

	//3.4 智能计算优惠券匹配
	public function actionSmartCouponCal()
	{
		$params['user_id']     = $this->user_id;
		$select_card           = SecurityHelper::getBodyParam("select_card");
		$params['select_card'] = json_decode($select_card, true);
		if (!$orderData = BizSendHelper::getPaymentCache($params)) {
			$this->_code    = StateCode::BIZ_SEND_PAY_BATCH_EXPIRE;
			$this->_message = "请求支付数据过期,请重试";

			return $this->response();
		}

		$this->_data = BizSendHelper::smartCouponCal($orderData, $params);

		return $this->response();
	}

	//4、申请取消订单
	public function actionUserCancel()
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$data               = WxBizSendHelper::userCancel($params);
		if ($data) {

			WxBizSendHelper::pushToProviderNotice($params['order_no'], WxBizSendHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);
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
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$params['agreed']   = SecurityHelper::getBodyParam('agreed');
		$data               = WxBizSendHelper::userCancelFlow($params);
		if ($data) {
			$this->_message = $params['agreed'] == '提交成功';
			WxBizSendHelper::pushToProviderNotice($params['order_no'], WxBizSendHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data);
		} else {
			$this->setCodeMessage(StateCode::ERRAND_USER_CANCEL);
		}

		return $this->response();
	}

	//6、订单详情页
	public function actionUserDetail()
	{

		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'detail';
		$data                   = BizSendHelper::userTaskAndDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::BIZ_SEND_DETAIL);
		}

		return $this->response();
	}

	//7、删除订单
	public function actionUserDelete()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;

		$data = WxBizSendHelper::userDelete($params);
		if ($data) {
			$this->_message = "删除成功";
		} else {

			$this->setCodeMessage(StateCode::ERRAND_DELETE);
		}

		return $this->response();
	}


	//8、实时检查订单
	public function actionCheckOrder()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['current_page'] = 'task';

		$data = WxBizSendHelper::userTaskAndDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->_code    = StateCode::BIZ_SEND_TMP_INDEX;
			$this->_message = "暂无数据";
		}

		//TODO 错误数据

		return $this->response();
	}

	//9、获取订单计算明细
	public function actionCalculation()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['card_id']  = SecurityHelper::getBodyParam('card_id', 0);
		$data               = WxBizSendHelper::getCalc($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::BIZ_SEND_CALC_FAILED);
		}

		return $this->response();
	}


}