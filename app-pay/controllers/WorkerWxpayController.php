<?php

namespace pay\controllers;

use common\components\Ref;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\QueueHelper;
use Yii;
use yii\web\Controller;

/**
 * 小帮版 App支付回调结果处理
 * Class WorkerWxpayController
 * @package pay\controllers
 */
class WorkerWxpayController extends Controller
{
	public function init()
	{
		parent::init(); // TODO: Change the autogenerated stub

		$this->enableCsrfValidation = false;
	}

	//缴纳保证金
	public function actionBailPay()
	{
		$response = Yii::$app->worker_app_wechat->getPayment()->handlePaidNotify(function ($notify, $fail) {

			Yii::$app->debug->pay_info("worker_bail_pay_notify", $notify);
			if ($notify['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
				// 用户是否支付成功
				if ($notify['result_code'] === 'SUCCESS') {

					$trade_no       = $notify['transaction_id'];
					$transaction_no = $notify['out_trade_no'];
					$fee            = $notify['total_fee'] * 0.01;
					$data           = json_encode($notify);
					$orderRes       = ShopHelper::bailPaySuccess($transaction_no, $trade_no, $fee, "微信异步回调", $data);
					if ($orderRes) {
						return true;
					}
				}
			}

			return false; //默认未处理完成，让微信继续回调
		});

		return $response;
	}
}