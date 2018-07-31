<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/1
 */

namespace common\helpers\payment;

use common\helpers\HelperBase;
use Yii;
use EasyWeChat\Payment\Order;

class AlipayHelper extends HelperBase
{

	/**
	 * body=标题
	 * detail= 明细
	 * transaction_no = 平台交易流水号
	 * fee    =金额
	 *
	 * @param $params
	 *
	 * @return bool
	 */
	public static function appOrder($params)
	{
		$total_amount = 0.01;
		if (YII_ENV_PROD) {
			$total_amount = $params['fee'];
		}
		$params['body']    = isset($params['body']) ? $params['body'] : '无忧帮帮-小帮快送';
		$params['subject'] = isset($params['subject']) ? $params['subject'] : '无忧帮帮-小帮快送';
		$content           = [
			'body'            => $params['body'],
			'subject'         => $params['subject'],
			'out_trade_no'    => $params['transaction_no'],
			'timeout_express' => '30m',
			'total_amount'    => $total_amount,
			'product_code'    => 'QUICK_MSECURITY_PAY',
		];

		$data = [
			'bizcontent' => $content
		];
		isset($params['notify_url']) ? $data['notify_url'] = $params['notify_url'] : null;

		Yii::$app->debug->pay_info("alipay_appOrder_info", $data);

		return Yii::$app->alipay->tradeApp($data);
	}

	public static function notify()
	{
		$notify = Yii::$app->alipay->notify();

		if ($notify) {

			return true;
		}

		return false;
	}


	public static function refundByTradeNo($trade_no, $refund_fee, $reason = '取消订单')
	{
		$refund_amount = 0.01;
		if (YII_ENV_PROD) {
			$refund_amount = $refund_fee;
		}
		$data = [
			'trade_no'       => $trade_no,        //原支付宝交易号
			'refund_amount'  => $refund_amount,
			'refund_reason'  => $reason,
			'out_request_no' => null
		];

		return Yii::$app->alipay->refund($data);

	}

}