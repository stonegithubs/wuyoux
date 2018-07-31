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
use api\modules\v1\traits\ErrandTrait;
use common\components\Ref;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use Yii;

class ErrandController extends ControllerAccess
{
	use ErrandTrait;

	/**
	 * 帮我办首页
	 */
	//TODO （废弃）迭代到 ErrandDoController  并更新为 index
	public function actionHelper()
	{
		$userInfo      = $this->getUserInfo();
		$checkOrder    = ErrandDoHelper::checkCanOrder($this->user_id);
		$user_location = SecurityHelper::getBodyParam("user_location");
		$this->_data   = [
			'service_price' => ErrandDoHelper::getServicePrice($user_location),        //TODO ?
			'use_mobile'    => isset($userInfo['mobile']) ? $userInfo['mobile'] : null,
			'order_no'      => $checkOrder['order_no'],
			"order_count"   => intval($checkOrder['order_count']),
		];

		return $this->response();
	}

	/**
	 * 获取单价
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionGetServicePrice()
	{
		$service_location = SecurityHelper::getBodyParam("service_location");
		$this->_data      = [
			'service_price' => ErrandDoHelper::getServicePrice($service_location)
		];

		return $this->response();
	}

	/**
	 * 新建快送订单
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionCreate()
	{
		//service_time,order_from,user_mobile,card_id,user_location,user_address,content,user_mobile,first_fee

		$service_qty    = SecurityHelper::getBodyParam('service_qty', 1);
		$orderAmount    = ErrandDoHelper::getServicePrice(SecurityHelper::getBodyParam("start_location")) * $service_qty;
		$userInfo       = UserHelper::getUserInfo($this->user_id);
		$online_money   = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$discount_money = OrderHelper::getOnlineDiscount($online_money);
		$params         = [
			"base"     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND,
				'region_id'   => RegionHelper::getRegionId(SecurityHelper::getBodyParam("city_name")),
				'order_from'  => SecurityHelper::getBodyParam("order_from"),
				'user_mobile' => SecurityHelper::getBodyParam("user_mobile"),
				'order_type'  => Ref::ORDER_TYPE_ERRAND,
				'user_id'     => $this->user_id,
			],
			'amount'   => [
				'order_amount' => $orderAmount,
			],
			'location' => [
				'user_location'  => SecurityHelper::getBodyParam("user_location"),
				'user_address'   => SecurityHelper::getBodyParam("user_address"),
				'start_location' => SecurityHelper::getBodyParam("start_location"),
				'start_address'  => SecurityHelper::getBodyParam("start_address"),
				'end_location'   => SecurityHelper::getBodyParam("end_location"),
				'end_address'    => SecurityHelper::getBodyParam("end_address")
			],
			'errand'   => [
				'service_price'  => ErrandDoHelper::getServicePrice(SecurityHelper::getBodyParam("start_location")),
				'service_time'   => strtotime(SecurityHelper::getBodyParam('service_time')),
				'errand_type'    => SecurityHelper::getBodyParam('errand_type', Ref::ERRAND_TYPE_DO),
				'errand_content' => SecurityHelper::getBodyParam('content'),
				'mobile'         => SecurityHelper::getBodyParam('user_mobile'),
				'first_fee'      => SecurityHelper::getBodyParam('first_fee'),
				'service_qty'    => SecurityHelper::getBodyParam('service_qty')
			]
		];

		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($params);
		$res = $orderHelper->save();
		if (is_array($res)) {

			$res['total_fee']      = sprintf("%.2f", SecurityHelper::getBodyParam('first_fee'));
			$res['online_money']   = $discount_money;
			$res['card_available'] = CouponHelper::getOrderCardNum($res['order_no']);
			$this->_data           = $res;
		} else {

			$this->setCodeMessage(StateCode::ERRAND_CREATE);
		}

		return $this->response();
	}

	/**
	 * 获取订单计算明细
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionCalculation()
	{
		$userInfo               = $this->getUserInfo();
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['card_id']      = SecurityHelper::getBodyParam('card_id', 0);
		$params['errand_type']  = SecurityHelper::getBodyParam('errand_type');
		$online_money           = isset($userInfo['online_money']) ? (int)$userInfo['online_money'] : 0;
		$params['online_money'] = OrderHelper::getOnlineDiscount($online_money);
		$data                   = ErrandDoHelper::getCalc($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_CALCULATION);
		}

		return $this->response();
	}

	/**
	 * 订单明细
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionDetail()
	{
		$params['order_no']     = SecurityHelper::getBodyParam('order_no');
		$params['errand_type']  = SecurityHelper::getBodyParam('errand_type');    //该参数无用到
		$params['current_page'] = SecurityHelper::getBodyParam('current_page');
		$data                   = ErrandDoHelper::userDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_DETAIL);
		}

		return $this->response();
	}


	/**
	 * 小帮抢单成功的操作
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionWorkerTask()
	{

		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $this->provider_id;
		$data                  = ErrandDoHelper::workerTask($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_WORKER_DETAIL);
		}

		return $this->response();
	}

	/**
	 * 小帮详情页
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionWorkerDetail()
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $this->provider_id;
		$params['app_data']    = null;
		$data                  = ErrandDoHelper::workerDetail($params);
		if ($data) {
			$this->_data = $data;
		} else {
			Yii::error("actionWorkerDetail" . json_encode($data));
			$this->setCodeMessage(StateCode::ERRAND_WORKER_DETAIL);
		}

		return $this->response();
	}

	/**
	 * 小帮工作流程
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionWorkerProgress()
	{
		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['errand_status']    = SecurityHelper::getBodyParam('errand_status');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = SecurityHelper::getBodyParam('current_location');

		$params['provider_id'] = $this->provider_id;
		$data                  = ErrandDoHelper::workerProgress($params);
		if ($data) {
			ErrandDoHelper::pushToUserNotice($params['order_no'], ErrandDoHelper::PUSH_USER_TYPE_TASK_PROGRESS, $params);
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_WORKER_PROGRESS);
		}

		return $this->response();
	}


	/**
	 * 订单确认
	 */
	//TODO （废弃）迭代到 ErrandDoController
	public function actionConfirm()
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$res                = ErrandDoHelper::userConfirm($params);
		if ($res) {
			$params['current_page'] = "finish";
			$detail                 = $this->_data = ErrandDoHelper::userDetail($params);
			$detail ? $this->_data = $detail : $this->setCodeMessage(StateCode::ERRAND_DETAIL);
			$this->_message = "订单已确认";
			ErrandDoHelper::pushToProviderNotice($params['order_no'], ErrandDoHelper::PUSH_PROVIDER_TYPE_CONFIRM, $params);
		} else {

			$this->setCodeMessage(StateCode::ERRAND_USER_CONFIRM);
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
	//TODO （废弃）迭代到 ErrandDoController
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

	//TODO 需要优化更新	 （废弃）迭代到 ErrandDoController
	public function actionAddFee()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['fee']      = SecurityHelper::getBodyParam('fee');
		$orderRes           = ErrandDoHelper::addCustomFee($params);
		if ($orderRes) {

			$payment_id     = $orderRes['payment_id'];
			$this->_message = "小费添加成功";

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = ErrandDoHelper::addCustomFeeSuccess($orderRes['transaction_no'], $trade_no, $payment_id, $orderRes['fee'], "小费余额支付");

				if ($isSuccess) {

					$total_fee   = OrderHelper::getTotalFee($orderRes['order_id']);
					$this->_data = [
						'total_fee'    => sprintf("%.2f", $total_fee),
						'order_amount' => sprintf("%.2f", $orderRes['order_amount']),
						'data'         => '',
						'payment_id'   => $payment_id
					];

				} else {
					$this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_BALANCE);

					return $this->response();
				}
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {    //微信支付
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("wxpay/errand-do-add-custom-fee");
				$wxRes                       = WxpayHelper::appOrder($payParams);

				if ($wxRes) {
					$total_fee   = OrderHelper::getTotalFee($orderRes['order_id']) + doubleval($params['fee']);
					$this->_data = [
						'total_fee'    => sprintf("%.2f", $total_fee),
						'order_amount' => sprintf("%.2f", $orderRes['order_amount']),
						'data'         => $wxRes,
						'payment_id'   => $payment_id
					];
				} else {

					$this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_WECHAT);

					return $this->response();
				}
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/errand-do-add-custom-fee");
				$alipayRes                   = AlipayHelper::appOrder($payParams);
				if ($alipayRes) {

					$total_fee   = OrderHelper::getTotalFee($orderRes['order_id']) + doubleval($params['fee']);
					$this->_data = [
						'total_fee'    => sprintf("%.2f", $total_fee),
						'order_amount' => sprintf("%.2f", $orderRes['order_amount']),
						'data'         => $alipayRes,
						'payment_id'   => $payment_id
					];

				} else {

					$this->setCodeMessage(StateCode::ERRAND_PER_PAYMENT_WECHAT);

					return $this->response();
				}
			}
		} else {

			$this->setCodeMessage(StateCode::ERRAND_ADD_CUSTOM_FEE);
		}

		return $this->response();
	}
}