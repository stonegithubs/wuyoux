<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace common\helpers\payment;


use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\OrderHelper;
use common\helpers\utils\QueueHelper;
use common\models\orders\Order;
use common\models\orders\OrderFee;
use common\models\payment\Transaction;
use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class TransactionHelper extends HelperBase
{

	const PREFIX_TRADE  = "T";   //交易标识
	const PREFIX_REFUND = "R";   //退款标识

	/**
	 * 创建订单流水记录
	 *
	 * @param       $ids_ref
	 * @param int   $payment_id
	 * @param array $params
	 *
	 * @return array|bool
	 */
	public static function createOrderTrade($ids_ref, $payment_id = 0, $params = [])
	{
		$result                = false;
		$fee                   = (new Query())->from(Order::tableName())->where(['order_id' => $ids_ref, 'payment_status' => [Ref::PAY_STATUS_WAIT, Ref::PAY_STATUS_REFUND_ALL]])
			->sum("amount_payable");
		$ids_ref               = is_array($ids_ref) ? implode(",", $ids_ref) : $ids_ref;
		$model                 = new Transaction();
		$model->ids_ref        = (string)$ids_ref;
		$model->transaction_no = isset($params['transaction_no']) ? $params['transaction_no'] : self::getTransactionNo(self::PREFIX_TRADE);
		$model->payment_id     = $payment_id;
		$model->fee            = isset($fee) ? $fee : 0;
		$model->status         = Ref::PAY_STATUS_WAIT;
		$model->type           = isset($params['type']) ? $params['type'] : Ref::TRANSACTION_TYPE_ORDER;
		$model->data           = isset($params['data']) ? json_encode($params['data']) : '';
		$model->create_time    = time();

		$model->save() ? $result = true : Yii::error("transaction save:" . json_encode($model->getErrors()));

		// 如果是余额支付的  冻结用户资金 并判断用户余额 是否够支付
		if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {

			$result &= WalletHelper::checkUserMoney($params['user_id'], $fee);
		}

		if ($result) {

			$result = [
				'transaction_no' => $model->transaction_no,
				'fee'            => $model->fee,
				'id'             => $model->id,
			];
		}

		return $result;
	}

	/**
	 * 创建订单交易退款流水记录
	 *
	 * @param       $order_id
	 * @param       $payment_id
	 * @param array $params
	 *
	 * @return array|bool
	 */
	public static function createOrderRefund($order_id, $payment_id, $params = [])
	{
		$result = false;
		//1、查出交易流水表对应单号
		$trade = Transaction::findOne(['ids_ref' => $order_id, 'payment_id' => $payment_id, 'status' => Ref::PAY_STATUS_COMPLETE, 'type' => Ref::TRANSACTION_TYPE_ORDER]);

		if ($trade) {
			$model                 = new Transaction();
			$model->ids_ref        = (string)$order_id;
			$model->transaction_no = TransactionHelper::getTransactionNo(self::PREFIX_REFUND);
			$model->trade_no       = $trade->trade_no;
			$model->payment_id     = $payment_id;
			$model->fee            = $trade->fee;
			$model->status         = Ref::PAY_STATUS_WAIT;
			$model->type           = Ref::TRANSACTION_TYPE_REFUND;
			$model->data           = isset($params['data']) ? json_encode($params['data']) : '';
			$model->app_id         = $trade->app_id;
			$model->create_time    = time();

			$model->save() ? $result = true : Yii::error("refund transaction save:" . json_encode($model->getErrors()));

			//对应的附加费
			$feeRes = self::_saveOrderFeeRefund($order_id, $payment_id);
			$result &= $feeRes;
			if ($result) {
				$main[] = [
					'transaction_no' => $model->transaction_no,
					'fee'            => $model->fee,
					'id'             => $model->id,
					'trade_no'       => $model->trade_no,
					'payment_id'     => $payment_id,
					'type'           => Ref::TRANSACTION_TYPE_REFUND,
					'app_id'         => $model->app_id
				];

				if (is_array($feeRes)) {

					$result = array_merge($main, $feeRes);
				} else {
					$result = $main;
				}

			}
		}

		return $result;
	}


	/**
	 * 获取平台流水号
	 * T 表示交易单号
	 * R 表示退款单号
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public static function getTransactionNo($prefix = 'T')
	{
		return $prefix . date('YmdHis') . rand(11111, 99999);
	}

	/**
	 * @param      $transaction_no
	 * @param      $trade_no
	 * @param      $payment_type
	 * @param null $remark
	 *
	 * @return array|bool|int
	 */
	public static function successOrderTrade($transaction_no, $trade_no, $payment_type, $fee, $remark = null, $data = null)
	{
		$result = false;

		$transaction = Yii::$app->db->beginTransaction();
		try {
			$model    = Transaction::find()->where(['transaction_no' => $transaction_no, 'status' => Ref::PAY_STATUS_WAIT])->one();
			$order_no = null;
			if ($model) {
				$model->trade_no    = $trade_no;
				$model->status      = Ref::PAY_STATUS_COMPLETE;
				$model->remark      = $remark;
				$model->data        = $data;
				$model->app_id      = self::getAppIDByData($data, $model->payment_id);
				$model->update_time = time();
				if ($model->save()) {
					$ids_ref = $model->ids_ref;
					$result  = OrderHelper::paySuccess($ids_ref, $payment_type);
				} else {
					Yii::error("transaction update:" . json_encode($model->getErrors()));
				}

//				if (!YII_ENV_DEV) {        //不处于dev环境，必须验证金额
//
//					if ($model->fee != $fee) { //验证金额，订单异常
//
//						Yii::error("transaction_fee_异常:".json_encode($model->getErrors()));
//						//TODO 需要记录日志
//						$result = false;
//					}
//				}

				$order = Order::findOne(['order_id' => $model->ids_ref]);
				if ($order) {


					//同步数据到旧架构
					if ($model->payment_id == Ref::PAYMENT_TYPE_BALANCE && $model->fee > 0) {
						//冻结用户金额
						$result &= WalletHelper::frozenMoney($order->user_id, $model->fee);
					}
					//扣减在线宝

					$pay_online_money = $order->online_money;
					if ($pay_online_money > 0) {

						$result &= WalletHelper::decreaseOnlineMoney($order->user_id, $pay_online_money, $order->order_no);    //扣在线宝和收支明细
					}

					if ($model->fee > 0) {//用户收支明细表

						$result &= WalletHelper::userIncomePay('2', '5', $order->user_id, $model->transaction_no, $model->fee, "小帮快送-支付订单");
					}

					$order_no = $order->order_no;
				}
			}

			if ($result) {
				$result = ArrayHelper::toArray($model);
				$transaction->commit();

				QueueHelper::newOrderNotice($order_no);
			}
		}
		catch (Exception $e) {

			$transaction->rollback();
		}

		return $result;
	}


	/**
	 * @param $id_pay
	 *
	 * @return mixed|string
	 */
	public static function getPaymentType($id_pay)
	{
		$arr = [
			Ref::PAYMENT_TYPE_WECHAT  => '微信支付',
			Ref::PAYMENT_TYPE_ALIPAY  => '支付宝',
			Ref::PAYMENT_TYPE_CASH    => '现金支付',
			Ref::PAYMENT_TYPE_BALANCE => '余额支付'
		];

		$result = isset($arr[$id_pay]) ? $arr[$id_pay] : '';

		return $result;
	}

	/**
	 * 订单退款异步更新
	 *
	 * @param $transaction_no
	 * @param $remark
	 *
	 * @return bool
	 */

	public static function successOrderRefund($transaction_no, $remark = null, $data = null)
	{
		$result = false;

		$transaction = Yii::$app->db->beginTransaction();
		try {
			$model = Transaction::find()->where(['transaction_no' => $transaction_no, 'status' => Ref::PAY_STATUS_WAIT])->one();

			if ($model) {
				$model->status      = Ref::PAY_STATUS_COMPLETE;
				$model->remark      = $remark;
				$model->data        = $data;
				$model->update_time = time();
				if ($model->save()) {
					$ids_ref = $model->ids_ref;
					$result  = OrderHelper::refundSuccess($ids_ref);
				} else {
					Yii::$app->debug->pay_info("transaction update refund", $model->getErrors());
				}

				//同步数据到旧架构
				$order = Order::findOne(['order_id' => $model->ids_ref]);
				if ($order) {

					//同步数据到旧架构
					if ($model->payment_id == Ref::PAYMENT_TYPE_BALANCE && $model->fee > 0) {
						//解冻用户金额
						$result &= WalletHelper::unFrozenMoney($order->user_id, $model->fee);
					}
					if ($model->fee > 0) {//用户收支明细表
						$result &= WalletHelper::userIncomePay('1', '7', $order->user_id, $model->transaction_no, $model->fee, "小帮快送-订单取消，收入" . $model->fee . "元");
					}

					if ($order->card_id) {
						$result &= CouponHelper::refundUserCard($order->card_id, $model->ids_ref);
					}
				}
				$result ? '' : Yii::$app->debug->pay_info("transaction update refund order", $order);
			}
			if ($result) {
				$transaction->commit();
			}
		}
		catch (Exception $e) {

			$transaction->rollback();
		}

		return $result;
	}

	/**
	 * 小费成功退费
	 *
	 * @param      $transaction_no
	 * @param null $remark
	 * @param null $data
	 *
	 * @return array|bool
	 */
	public static function successOrderFeeRefund($transaction_no, $remark = null, $data = null)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$model = Transaction::find()->where(['transaction_no' => $transaction_no, 'status' => Ref::PAY_STATUS_WAIT])->one();

			if ($model) {
				$model->status      = Ref::PAY_STATUS_COMPLETE;
				$model->remark      = $remark;
				$model->data        = $data;
				$model->update_time = time();
				if ($model->save()) {
					$ids_ref = $model->ids_ref;
					$result  = OrderHelper::updateSmallFeeStatus($ids_ref); //TODO 小费
				} else {
					Yii::$app->debug->pay_info("transaction update fee refund", $model->getErrors());
				}

				$orderFee = OrderFee::findOne(['fee_id' => $model->ids_ref]);
				if ($orderFee) {
					$order = Order::findOne(['order_id' => $orderFee->ids_ref]);

					if ($order) {

						//同步数据到旧架构
						if ($model->payment_id == Ref::PAYMENT_TYPE_BALANCE && $model->fee > 0) {
							//解冻用户金额
							$result &= WalletHelper::unFrozenMoney($order->user_id, $model->fee);
						}
						if ($model->fee > 0) {//用户收支明细表
							$result &= WalletHelper::userIncomePay('1', '7', $order->user_id, $model->transaction_no, $model->fee, "小帮快送-订单小费取消，收入" . $model->fee . "元");
						}

					} else {
						Yii::error("异常订单ID" . $orderFee->ids_ref);
					}
				}
				$result ? '' : Yii::$app->debug->pay_info("transaction update refund order", $orderFee);
				if ($result) {
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollback();
		}

		return $result;
	}

	//创建小费预退款单
	private static function _saveOrderFeeRefund($order_id, $payment_id, $params = [])
	{
		$data = OrderFee::find()->where(['ids_ref' => $order_id])->all();

		$result = true;
		if ($data) {
			$tradeRes = [];
			foreach ($data as $item) {
				//1、查出交易流水表对应单号
				$trade = Transaction::findOne(['ids_ref' => $item['fee_id'], 'payment_id' => $payment_id, 'status' => Ref::PAY_STATUS_COMPLETE, 'type' => Ref::TRANSACTION_TYPE_TIPS]);
				if ($trade) {
					$model                 = new Transaction();
					$model->ids_ref        = (string)$trade->ids_ref;
					$model->transaction_no = TransactionHelper::getTransactionNo(self::PREFIX_REFUND);
					$model->trade_no       = $trade->trade_no;
					$model->payment_id     = $trade->payment_id;
					$model->fee            = $trade->fee;
					$model->status         = Ref::PAY_STATUS_WAIT;
					$model->type           = Ref::TRANSACTION_TYPE_TIPS_REFUND;
					$model->app_id         = $trade->app_id;
					$model->data           = isset($params['data']) ? json_encode($params['data']) : '';
					$model->create_time    = time();

					$model->save() ? $result = true : Yii::error("refund transaction save:" . json_encode($model->getErrors()));
					if ($result) {
						$tradeRes[] = [
							'transaction_no' => $model->transaction_no,
							'fee'            => $model->fee,
							'id'             => $model->id,
							'trade_no'       => $model->trade_no,
							'payment_id'     => $payment_id,
							'type'           => Ref::TRANSACTION_TYPE_TIPS_REFUND,
							'app_id'         => $model->app_id
						];
					}
				}
			}

			if (count($tradeRes) > 0)
				$result = $tradeRes;
		}

		return $result;
	}


	//重构通用交易流水
	//创建流水
	public static function createTrade($ids_ref, $fee, $params = [])
	{

		$result                = false;
		$model                 = new Transaction();
		$model->ids_ref        = (string)$ids_ref;
		$model->fee            = $fee;
		$model->transaction_no = isset($params['transaction_no']) ? $params['transaction_no'] : self::getTransactionNo(self::PREFIX_TRADE);
		$model->payment_id     = isset($params['payment_id']) ? $params['payment_id'] : Ref::PAYMENT_TYPE_CASH;
		$model->status         = isset($params['status']) ? $params['status'] : Ref::PAY_STATUS_WAIT;
		$model->type           = isset($params['type']) ? $params['type'] : Ref::TRANSACTION_TYPE_ORDER;
		$model->data           = isset($params['data']) ? json_encode($params['data']) : '';
		$model->create_time    = time();
		$model->remark         = isset($params['remark']) ? $params['remark'] : null;
		$model->save() ? $result = true : Yii::error("transaction save:" . json_encode($model->getErrors()));

		if ($result) {
			$result = [
				'transaction_no' => $model->transaction_no,
				'fee'            => $model->fee,
				'id'             => $model->id,
			];
		}

		return $result;
	}

	//更新流水
	public static function updateTrade($transaction_no, $trade_no, $fee, $remark = null, $data = null)
	{

		$result = false;
		$model  = Transaction::find()->where(['transaction_no' => $transaction_no, 'status' => Ref::PAY_STATUS_WAIT])->one();
		if ($model) {
			$model->trade_no    = $trade_no;
			$model->status      = Ref::PAY_STATUS_COMPLETE;
			$model->remark      = $remark;
			$model->data        = $data;
			$model->app_id      = self::getAppIDByData($data, $model->payment_id);
			$model->update_time = time();
			$model->save() ? $result = true : Yii::error("transaction update trade:" . json_encode($model->getErrors()));

			if (!YII_ENV_DEV) {        //不处于dev环境，必须验证金额

				if ($model->fee != $fee) { //验证金额，订单异常

					Yii::$app->debug->pay_info("回调资金不一致", $trade_no);
				}
			}

			if ($result) {
				$result = ArrayHelper::toArray($model);
			}
		}

		return $result;
	}

	/**
	 * 通过解析data获取APP ID
	 * @param $data
	 * @param $payment_id
	 * @return null
	 */
	private static function getAppIDByData($data, $payment_id)
	{
		$app_id = null;
		if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
			$res    = json_decode($data, true);
			$app_id = isset($res['appid']) ? $res['appid'] : null;
		}

		if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
			$res    = json_decode($data, true);
			$app_id = isset($res['app_id']) ? $res['app_id'] : null;
		}

		return $app_id;
	}

	public static function getIncomeType($id_pay)
	{
		$arr = [
			1 => '红包收入',
			2 => '红包支出',
			3 => '余额充值',
			4 => '话费充值',
			5 => '订单支付',
			6 => '订单退款',
			7 => '订单退款',
		];

		$result = isset($arr[$id_pay]) ? $arr[$id_pay] : '';

		return $result;
	}

	//打赏小帮
	//创建流水
	//$params['fee','user_id']
	public static function createFeeTrade($ids_ref, $payment_id = 0, $params)
	{
		$result                = false;
		$fee                   = isset($params['fee']) ? $params['fee'] : 0;
		$ids_ref               = is_array($ids_ref) ? implode(",", $ids_ref) : $ids_ref;
		$model                 = new Transaction();
		$model->ids_ref        = (string)$ids_ref;
		$model->transaction_no = isset($params['transaction_no']) ? $params['transaction_no'] : self::getTransactionNo(self::PREFIX_TRADE);
		$model->payment_id     = $payment_id;
		$model->fee            = $fee;
		$model->status         = Ref::PAY_STATUS_WAIT;
		$model->type           = isset($params['type']) ? $params['type'] : Ref::TRANSACTION_TYPE_TIPS; //小费
		$model->data           = isset($params['data']) ? json_encode($params['data']) : '';
		$model->create_time    = time();

		$model->save() ? $result = true : Yii::error('create_fee_trade: ' . json_encode($model->getErrors()));

		// 如果是余额支付的 判断用户余额 是否够支付
		if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {
			$result &= WalletHelper::checkUserMoney($params['user_id'], $fee);
		}

		if ($result) {

			$result = [
				'transaction_no' => $model->transaction_no,
				'fee'            => $model->fee,
				'id'             => $model->id,
			];
		}

		return $result;
	}

}