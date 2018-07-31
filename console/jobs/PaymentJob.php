<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/11
 */
namespace console\jobs;

use common\helpers\orders\BizSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\WalletHelper;
use Yii;

class PaymentJob extends JobBase
{
	public function preRefund()
	{
		$data = isset($this->params['data']) ? $this->params['data'] : null;
		if ($data) {

			Yii::$app->debug->job_info("pre_refund_data", $data);
			OrderHelper::preRefund($data);
		}
	}

	public function marketToSaveProfit()
	{
		$orderData = isset($this->params['orderData']) ? $this->params['orderData'] : null;
		$agentId   = isset($this->params['agentId']) ? $this->params['agentId'] : null;

		if ($orderData) {
			Yii::$app->debug->job_info("market_profit_order_data", $orderData);
			WalletHelper::marketProfit($orderData, $agentId);
		}
	}

	public function autoBizFreezeBalance()
	{
		$data = isset($this->params['data']) ? $this->params['data'] : null;
		if ($data) {
			BizSendHelper::autoFreezeBalanceExt($data);
		}
	}
}