<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/7/10 23:41
 */

namespace console\jobs;

use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\TripBikeHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\UrlHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use m\helpers\MarketHelper;
use Yii;

class OrderJob extends JobBase
{

	//小帮快送推送订单
	public function errandSendOrder()
	{
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : null;
		if ($order_id)
			PushHelper::errandSendOrder($order_id);
		echo "order_id:" . $order_id;
	}

	//小帮快送推送订单辅助推送
	public function errandSendOrderPlus()
	{
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : null;
		if ($order_id)
			PushHelper::errandSendOrderPlus($order_id);
		echo "order_id:" . $order_id;
	}

	//企业送推送订单
	public function bizSendOrder()
	{
		$batchNo = isset($this->params['batch_no']) ? $this->params['batch_no'] : null;
		if ($batchNo)
			PushHelper::bizSendOrder($batchNo);
		var_dump($batchNo);
	}

	//企业送推送订单
	public function bizSendOrderPlus()
	{
		$batchNo = isset($this->params['batch_no']) ? $this->params['batch_no'] : null;
		if ($batchNo)
			PushHelper::bizSendOrderPlus($batchNo);
		var_dump($batchNo);
	}

	//平台取消订单
	public function errandPlatformCancelNotice()
	{
		$data = isset($this->params['data']) ? $this->params['data'] : null;
		$role = isset($this->params['role']) ? $this->params['role'] : null;
		if ($data)
			ErrandHelper::pushPlatformCancelMsg($data, $role);


		Yii::$app->debug->job_info("errandCancelMsg", $this->params);

	}

	//自动取消快送订单
	public function autoCancelErrandOrder()
	{
		$orderId = isset($this->params['order_id']) ? $this->params['order_id'] : null;
		Yii::$app->debug->job_info("auto_cancel_order_no:", $orderId);
		if ($orderId) {

			$Res = ErrandHelper::autoCancelOrder($orderId);
			if ($Res) {
				ErrandHelper::preRefund($Res);                    //退款
				ErrandHelper::pushAutoCancelNotice($Res['order_no']);    //推送结果
			}
		}
	}

	//取消快送临时发单
	public function autoCancelBizTmpOrder()
	{
		$batchNo = isset($this->params['batch_no']) ? $this->params['batch_no'] : null;
		if ($batchNo) {
			BizSendHelper::autoCancelBizTmpOrder($batchNo);
		}
	}

	//自动确认订单
	public function autoConfirmErrandOrder()
	{
		$params = isset($this->params['data']) ? $this->params['data'] : null;
		$type   = isset($params['errand_type']) ? $params['errand_type'] : null;

		Yii::$app->debug->job_info("auto_confirm_order_no:", $params);
		if ($params) {

			if ($type == Ref::ERRAND_TYPE_BUY) {
				ErrandBuyHelper::userConfirm($params);
			}

			if ($type == Ref::ERRAND_TYPE_SEND) {
				ErrandSendHelper::userConfirm($params);
			}

			if ($type == Ref::ERRAND_TYPE_DO) {
				ErrandDoHelper::userConfirm($params);
			}

			if ($type == Ref::ERRAND_TYPE_BIZ) {
				BizSendHelper::userConfirm($params);
			}
		}
	}

	//新订单提醒
	public function newOrderNotice()
	{
		$order_no = isset($this->params['order_no']) ? $this->params['order_no'] : null;
		$type     = isset($this->params['type']) ? $this->params['type'] : 'errand';

		@file_get_contents(UrlHelper::adminDomain() . '/admin.php?s=/Bmob/orderNotice&type=' . $type . '&order_no=' . $order_no);

	}

	//收货人短信推送
	public function smsToReceiver()
	{
		$order_no = isset($this->params['order_no']) ? $this->params['order_no'] : null;

		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);

			$shopInfo = ShopHelper::shopDetailByMobile($order->provider_mobile);

			$market_code = '1806200002';    //默认营销号
			$userMarket  = MarketHelper::getUserMarket($shopInfo['uid']);
			if ($userMarket) {
				$market_code = $userMarket['market_code'];
			}
			$params = [
				'provider_mobile' => $order->provider_mobile,
				'provider_name'   => $shopInfo['uname'],
				'order_no'        => $order->order_no,
				'market_code'     => $market_code,
			];

			SmsHelper::sendReceiverSmsNotice($errand->mobile, $params);

			var_dump($params);
			exit();
		}
	}

	//小帮出行 推送订单
	public function tripSendOrder()
	{
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : null;
		if ($order_id)
			PushHelper::tripSendOrder($order_id);
		echo "order_id:" . $order_id;
	}

	//小帮出行 取消订单
	public function autoCancelTripOrder()
	{
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : null;
		Yii::$app->debug->job_info("auto_cancel_order_id:", $order_id);
		if ($order_id) {
			//取消订单
			$Res = TripBikeHelper::autoCancelTripOrder($order_id);

			echo "auto cancel";
		}
	}

	//小帮出行 待接单流程推送信息
	public function tripTipMessage()
	{
		$tip_times = isset($this->params['tip_times']) ? $this->params['tip_times'] : null;
		$order_id  = isset($this->params['order_id']) ? $this->params['order_id'] : null;
		if ($tip_times) {
			$params = ['tip_times' => $tip_times];
			TripBikeHelper::pushToUserNotice($order_id, TripBikeHelper::PUSH_USER_TYPE_SEARCH_WORKER, $params);
		}
	}
}