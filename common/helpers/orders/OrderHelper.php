<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace common\helpers\orders;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderFee;
use common\models\orders\OrderLog;
use common\models\orders\OrderTrip;
use yii\base\Exception;
use Yii;

class OrderHelper extends HelperBase
{

	//参数
	private $orderParams
		= [
			"base"     => [
				'cate_id'      => null,
				'region_id'    => null,
				'city_id'      => null,
				'area_id'      => null,
				'order_from'   => 0,
				'user_mobile'  => null,
				'order_type'   => null,
				'user_id'      => 0,
				'payment_id'   => 0,
				'order_amount' => 0,
			],
			'amount'   => [
				'card_id'                  => 0,
				'order_amount'             => 0,
				'amount_payable'           => 0,    //实付金额 =（订单金额+第一次小费 - 优惠金额）
				'discount'                 => 0,
				'provider_estimate_amount' => 0,
				'provider_actual_amount'   => 0,
				'online_money'             => 0,
				'online_money_discount'    => 0
			],
			'location' => [
				'user_location'     => null,
				'user_address'      => null,
				'provider_location' => null,
				'provider_address'  => null,
				'start_location'    => null,
				'start_address'     => null,
				'end_location'      => null,
				'end_address'       => null,
			],
			'trip'     => [
				'trip_status'       => Ref::TRIP_STATUS_WAIT,
				'estimate_amount'   => 0,
				'estimate_distance' => 0,
			],
			'errand'   => [
				'errand_status'  => Ref::ERRAND_STATUS_WAITE,
				'service_price'  => 0,
				'service_time'   => 0,
				'errand_type'    => Ref::ERRAND_TYPE_DO,
				'errand_content' => null,
				'mobile'         => null,
				'first_fee'      => 0,
			]
		];

	/**
	 * @param array $orderParams
	 */
	public function setOrderParams($orderParams)
	{
		$this->orderParams = $orderParams;
	}

	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function getOrderParams($key = null)
	{
		$params = $this->orderParams;

		if ($key)
			return isset($params[$key]) ? $params[$key] : null;
		else
			return $params;
	}

	/**
	 * 判断非空数据
	 */
	public function checkErrandParams()
	{
		$params = $this->orderParams;

		if (!$params['errand']['errand_content']) {    //发单内容不为空
			return true;
		}

		if ($params['errand']['service_price'] <= 0) {    //发单价格必须大于0
			return true;
		}

		return false;
	}

	/**
	 * 订单保存
	 * @return bool
	 */
	public function save()
	{
		Yii::info("save start");
		$result      = false;
		$transaction = \Yii::$app->db->beginTransaction();
		try {

			$order                 = new Order();
			$order->order_status   = Ref::ORDER_STATUS_AWAITING_PAY;
			$order->payment_status = Ref::PAY_STATUS_WAIT;
			$order->attributes     = $this->getOrderParams('base');
			$order->attributes     = $this->getOrderParams("location");
			$order->attributes     = $this->calcOrder($order->order_type);
			$order->order_no       = self::getOrderNo();   //生成订单号
			$order->create_time    = time();
			$order->save() ? $result = true : Yii::error("order save:" . json_encode($order->getErrors()));

			$id_order = $order->order_id;

			if ($order->order_type == Ref::ORDER_TYPE_TRIP) {
				$result &= $this->saveTrip($order->order_id);
			}

			if ($order->order_type == Ref::ORDER_TYPE_ERRAND) {

				$result &= $this->saveErrand($order->order_id);
			}

			//记录业务日志
			$result &= self::saveLogContent($id_order, "order_status", (string)Ref::ORDER_STATUS_AWAITING_PAY, "新建");
			if ($result) {
				//TODO 确认生成数据后，队列生成横排数据

				$result = [
					'order_id'     => $order->order_id,
					'order_no'     => $order->order_no,
					'order_amount' => sprintf("%.2f", $order->order_amount)
				];;
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	//交通出行
	public function saveTrip($order_id)
	{

		$result           = false;
		$trip             = new OrderTrip();
		$trip->attributes = $this->getOrderParams('trip');
		$trip->order_id   = $order_id;
		$trip->save() ? $result = true : Yii::$app->debug->log_info("trip_save", $trip->getErrors());;

		return $result;
	}

	//小帮快送
	public function saveErrand($order_id)
	{
		$result = false;
		$errand = new OrderErrand();

		$errand->errand_status  = Ref::ERRAND_STATUS_WAITE;
		$errand->attributes     = $this->getOrderParams('errand');
		$errand->order_id       = $order_id;
		$errand->total_fee      = $errand->first_fee;
		$errand->order_distance = $this->getDistanceStartAndEnd();    //订单起点与终点距离

		$errand->save() ? $result = true : Yii::error("errand save:" . json_encode($errand->getErrors()));

		if ($errand->first_fee > 0) {

			$logParams = [
				'ids_ref' => $order_id,
				'type'    => Ref::FEE_TYPE_ORDER,
				'amount'  => $errand->first_fee,
				'status'  => Ref::PAY_STATUS_COMPLETE,
			];
			$result    &= self::addFee($logParams);//记录小费
		}

		return $result;
	}

	/**
	 * 保存log内容记录
	 * @param $order_id
	 * @param $key
	 * @param $data
	 * @param $remark
	 * @return bool
	 */
	public static function saveLogContent($order_id, $key, $data, $remark)
	{
		//记录业务日志
		$logParams = [
			'order_id'  => $order_id,
			'log_key'   => $key,
			'log_value' => json_encode($data),
			'remark'    => $remark
		];

		$result             = false;
		$model              = new OrderLog();
		$model->attributes  = $logParams;
		$model->create_time = time();
		$model->save() ? $result = true : Yii::error("addLog save:" . json_encode($model->getErrors()));

		return $result;
	}

	/**
	 * 获取log内容记录
	 * @param $order_id
	 * @param $key
	 */
	public static function getLogContent($order_id, $key)
	{
		$result = false;
		$model  = OrderLog::findOne(['order_id' => $order_id, 'log_key' => $key]);
		if ($model) {
			$result = json_decode($model->log_value, true);
		}

		return $result;
	}

	public static function addFee($params)
	{
		$result             = false;
		$model              = new OrderFee();
		$model->attributes  = $params;
		$model->create_time = time();
		$model->save() ? $result = true : Yii::error("addFee save:" . json_encode($model->getErrors()));
		if ($result) {
			$result = [
				'fee_id' => $model->fee_id
			];
		}

		return $result;
	}

	public function calcOrder($type)
	{

		$amount = $this->getOrderParams("amount");

		if ($type == Ref::ORDER_TYPE_ERRAND) {    //跑腿计算方式
			$errand       = $this->getOrderParams("errand");
			$first_fee    = isset($errand['first_fee']) ? $errand['first_fee'] : 0;
			$order_amount = $amount['order_amount'] + $first_fee;
			$card_id      = isset($amount['card_id']) ? $amount['card_id'] : 0;

			$discount_amount = isset($amount['online_money_discount']) ? $amount['online_money_discount'] : 0;

			$card_amount = CouponHelper::getCardAmount($card_id);

			$discount       = $discount_amount + $card_amount;    //在线宝+ 优惠券  100  - 91  -10 =  -1  (90 + 10)
			$amount_payable = $order_amount - $discount;        //订单总金额-折扣  //TODO 负数。

			$amount_payable < 0 ? $amount_payable = 0 : null;
			$amount = [
				'card_id'                  => $card_id,
				'order_amount'             => $order_amount,        //订单总额 =  服务单价* 时间 + 首次小费
				'amount_payable'           => $amount_payable,
				'discount'                 => $discount,
				'provider_estimate_amount' => 0,                    //小帮预计收到 订单金额（基本金额+第一次小费） - 抽佣 TODO?
				'online_money'             => $discount_amount
			];
		}

		return $amount;

	}

	/**
	 * @param $params
	 *
	 * @return bool]
	 */
	public function updatePrepayment($params)
	{
		$result      = false;
		$transaction = \Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $params['order_no'], 'payment_status' => Ref::PAY_STATUS_WAIT]);

			if ($order) {
				$order_amount = $order->order_amount;

				$updateData        = $this->getOrderCalc($order_amount, $params);
				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("order update pre payment save:" . json_encode($order->getErrors()));

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, "update_payment", $updateData, "预支付");

				$tranParams['user_id'] = $order->user_id;
				$tranRes               = TransactionHelper::createOrderTrade($order->order_id, $order->payment_id, $tranParams);    //创建交易流水
				$result                &= $tranRes;
				if ($result) {
					$result = [
						'order_no'       => $order->order_no,
						'order_id'       => $order->order_id,
						'transaction_no' => $tranRes['transaction_no'],
						'fee'            => $tranRes['fee'],
						'transaction_id' => $tranRes['id'],
					];
					CouponHelper::updateCard($updateData['card_id'], $updateData['card_amount']);    //更新优惠券
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	//生成一条预支付信息
	public function generatePrePaymentSingle($params)
	{
		$result      = false;
		$transaction = \Yii::$app->db->beginTransaction();
		try {

			$order = Order::findOne(['order_no' => $params['order_no'], 'payment_status' => [Ref::PAY_STATUS_WAIT, Ref::PAY_STATUS_REFUND_ALL], 'order_status' => Ref::ORDER_STATUS_AWAITING_PAY]);

			if ($order) {
				$order_amount = $order->order_amount;

				$updateData        = $this->getOrderCalc($order_amount, $params);
				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("order update pre payment save:" . json_encode($order->getErrors()));

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, "update_payment", $updateData, "预支付");

				//交易流水
				$tranParams['user_id'] = $order->user_id;
				$tranRes               = TransactionHelper::createOrderTrade($order->order_id, $order->payment_id, $tranParams);    //创建交易流水
				$result                &= $tranRes;
				if ($result) {
					$result = [
						'order_no'       => $order->order_no,
						'order_id'       => $order->order_id,
						'transaction_no' => $tranRes['transaction_no'],
						'fee'            => $tranRes['fee'],
						'transaction_id' => $tranRes['id'],
					];
					CouponHelper::updateCard($updateData['card_id'], $updateData['card_amount']);
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;

	}

	//生成多条预支付信息
	public function generatePrePaymentBatch($orderData, $params)
	{

		$result      = false;
		$transaction = \Yii::$app->db->beginTransaction();
		try {

			if ($orderData) {
				$result   = true;
				$orderIds = [];
				foreach ($orderData as $order_id => $item) {
					$orderIds[] = $order_id;
					$order      = Order::findOne(['order_id' => $order_id, 'payment_status' => [Ref::PAY_STATUS_WAIT, Ref::PAY_STATUS_REFUND_ALL], 'order_status' => Ref::ORDER_STATUS_AWAITING_PAY]);

					if ($order) {
						$order_amount      = $order->order_amount;
						$orderParams       = [
							'card_id'    => $item['card_id'],
							'payment_id' => $params['payment_id'],
						];
						$updateData        = $this->getOrderCalc($order_amount, $orderParams);
						$order->attributes = $updateData;
						$order->save() ? $result = true : Yii::error("order update pre payment save:" . json_encode($order->getErrors()));

						CouponHelper::updateCard($updateData['card_id'], $updateData['card_amount']);
						//记录业务日志
						$result &= self::saveLogContent($order->order_id, "update_payment", $updateData, "预支付");
					} else {
						$result = false;    //防止订单已经支付
					}
				}

				if ($result) {
					//交易流水
					$tranParams['user_id'] = $params['user_id'];
					$tranRes               = TransactionHelper::createOrderTrade($orderIds, $params['payment_id'], $tranParams);    //创建交易流水
					$result                &= $tranRes;
				}

				if ($result) {
					$result = [
						'transaction_no' => $tranRes['transaction_no'],
						'fee'            => $tranRes['fee'],
						'transaction_id' => $tranRes['id'],
					];
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	public function getOrderCalc($order_amount, $params)
	{

		$card_id      = isset($params['card_id']) ? $params['card_id'] : 0;
		$online_money = 0;//isset($params['online_money']) ? $params['online_money'] : 0;//产品需求 整个产品去掉在线宝折扣
		$card_amount  = CouponHelper::getCardAmount($card_id);

		$online_money_discount = 0;                         //扣除在线宝可用金额部分。

		$amount_payable = bcsub($order_amount, $card_amount, 2);     //扣减优惠券

		if ($amount_payable > 0) {

//			$amount_payable -= $online_money;                //继续扣减在线宝
//			if ($amount_payable > 0) {
//
//				$online_money_discount = $online_money;
//
//			} else { //amount_payable = -1 所以得出在线宝应该扣减=  $online_money + (-1 )
//
//				$online_money_discount = $online_money + $amount_payable;
//
//			}
			//TODO 其他优惠
		} else {

			$card_amount = $order_amount; //可支付金额小于0 实际使用的优惠券金额 = 订单金额
		}

		if ($amount_payable < 0) {
			$amount_payable = 0;
		}

		$discount = $card_amount + $online_money_discount;

		return [
			'payment_id'     => isset($params['payment_id']) ? $params['payment_id'] : null,
			'card_id'        => $card_id,
			'amount_payable' => $amount_payable,
			'discount'       => $discount,
			'online_money'   => $online_money_discount,
			'card_amount'    => $card_amount        //扣除优惠券的金额
		];
	}

	/**
	 * 年 月 日 时 分 秒+2位随机
	 *
	 * @param string $user_id
	 *
	 * @return string (1706 2216 2633 2位随机数)
	 */
	public static function getOrderNo($user_id = '0000')
	{
//		$len    = strlen($user_id);
//		$min    = $len < 4 ? sprintf("%04d", $user_id) : 0;
//		$num    = $len >= 4 ? substr($user_id, -4, 4) : $min;
		$result = date('ymdHis') . rand(10, 99);

		return strval($result);
	}

	/**
	 * 计算起点和终点的距离
	 */
	public function getDistanceStartAndEnd()
	{
		$params         = $this->getOrderParams("location");
		$start_location = isset($params['start_location']) ? $params['start_location'] : 0;
		$end_location   = isset($params['end_location']) ? $params['end_location'] : 0;
		$distance       = 0;
		if ($start_location && $end_location) {
			$route    = AMapHelper::bicycling(AMapHelper::coordToStr($start_location), AMapHelper::coordToStr($end_location));
			$distance = is_array($route) ? $route['distance'] : 0;
		}

		return $distance;
	}

	//TODO 暂时统一为0.2
	public static function getOnlineDiscount($online_money)
	{
		return sprintf("%.2f", $online_money * 0.2);
	}

	/**
	 * 支付成功后的回调
	 *
	 * @param $order_id
	 * @param $payment_type
	 *
	 * @return bool
	 */
	public static function paySuccess($order_id, $payment_type)
	{
		$result = false;
		$model  = Order::findOne(['order_id' => $order_id]);

		if ($model) {
			$updateData        = [
				'order_status'   => Ref::ORDER_STATUS_DOING,
				'payment_status' => Ref::PAY_STATUS_COMPLETE,
				'payment_time'   => time(),
				'payment_id'     => $payment_type,
				'update_time'    => time()
			];
			$model->attributes = $updateData;
			$model->save() ? $result = true : Yii::error("order pay success save:" . json_encode($model->getErrors()));
			//记录业务日志
			$result &= self::saveLogContent($model->order_id, 'pay_success', $updateData, '支付成功更新订单');

			if ($model->card_id > 0) {

				$result &= CouponHelper::beUseCard($model->card_id);//更新卡券
			}
		}

		if ($result) {
			//TODO 队列更改数据
		}

		return $result;
	}

	//获取总小费
	public static function getTotalFee($order_id)
	{
		return OrderFee::find()->where(['ids_ref' => $order_id, 'status' => Ref::PAY_STATUS_COMPLETE])->sum('amount');
	}

	//根据支付方式获取小费
	public static function getTotalFeeByPaymentId($order_id, $payment_id)
	{
		return OrderFee::find()->where(['ids_ref' => $order_id, 'status' => Ref::PAY_STATUS_COMPLETE, 'payment_id' => $payment_id])->sum('amount');
	}

	//支付成功更新小费信息
	public static function SmallFeePaySuccess($fee_id)
	{

		//更新小费表
		//更新 errand总小费
		$result   = false;
		$orderFee = OrderFee::findOne(['fee_id' => $fee_id]);
		if ($orderFee) {
			$order_id = $orderFee->ids_ref;
			$errand   = OrderErrand::findOne(['order_id' => $order_id]);
			$order    = Order::findOne(['order_id' => $order_id]);

			//以往成功的小费记录 + 该次将要成功的记录
			$all_total_fee = self::getTotalFee($order_id);
			$total_fee     = doubleval($orderFee->amount) + $all_total_fee;

			//更新用户   订单总金额 = 服务单价 + 总小费
			$new_order_amount = doubleval($errand->service_price) * $errand->service_qty + $total_fee;
			$updateData       = [
				'total_fee'    => $total_fee,
				'status'       => Ref::PAY_STATUS_COMPLETE,
				'order_amount' => $new_order_amount,
			];

			$orderFee->attributes = $updateData;
			$orderFee->save() ? $result = true : Yii::error("SmallFeePaySuccess fee save:" . json_encode($orderFee->getErrors()));

			$order->attributes = $updateData;
			$order->save() ? $result &= true : Yii::error("SmallFeePaySuccess fee save:" . json_encode($order->getErrors()));

			$errand->attributes = $updateData;
			$errand->save() ? $result &= true : Yii::error("SmallFeePaySuccess errand save:" . json_encode($errand->getErrors()));

			$updateData = array_merge($updateData, ['小费表ID' => $orderFee->fee_id, '本次添加小费' => $orderFee->amount]);
			$result     &= self::saveLogContent($order_id, 'small_fee_pay_success', $updateData, '小费支付成功更改记录');//记录业务日志
		}

		return $result;
	}

	/**
	 * 更新状态
	 *
	 * @param $fee_id
	 *
	 * @return bool
	 */
	public static function updateSmallFeeStatus($fee_id)
	{

		$result   = false;
		$orderFee = OrderFee::findOne(['fee_id' => $fee_id]);
		if ($orderFee) {

			$updateData           = [
				'status' => Ref::PAY_STATUS_REFUND_ALL,
			];
			$order_id             = $orderFee->ids_ref;
			$orderFee->attributes = $updateData;
			$orderFee->save() ? $result = true : Yii::error("SmallFeePaySuccess fee save:" . json_encode($orderFee->getErrors()));

			$updateData = array_merge($updateData, ['小费表ID' => $orderFee->fee_id]);
			$result     &= self::saveLogContent($order_id, 'small_fee_update_status', $updateData, '小费支付成功更改记录');//记录业务日志
		}

		return $result;
	}

	/**
	 * 退款成功后的更新
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public static function refundSuccess($order_id)
	{
		$result = false;
		$model  = Order::findOne(['order_id' => $order_id]);

		if ($model) {
			$updateData        = [
				'payment_status' => Ref::PAY_STATUS_REFUND_ALL,
				'update_time'    => time()
			];
			$model->attributes = $updateData;
			$model->save() ? $result = true : Yii::error("order refund success save:" . json_encode($model->getErrors()));
			//记录业务日志
			$result &= self::saveLogContent($model->order_id, 'refund_success', $updateData, '退款成功更新订单');
		}

		return $result;
	}

	/**
	 * 更新评价
	 *
	 * @param $order_no
	 *
	 * @return bool
	 */
	public static function updateEvaluate($order_no)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $order_no, 'order_status' => Ref::ORDER_STATUS_COMPLETED]);
		if ($model) {
			$updateData        = [
				'order_status' => Ref::ORDER_STATUS_EVALUATE,
				'update_time'  => time()
			];
			$model->attributes = $updateData;
			$model->save() ? $result = true : Yii::error("order pay success save:" . json_encode($model->getErrors()));
			//记录业务日志
			$result &= self::saveLogContent($model->order_id, 'evaluate', $updateData, '对订单进行评价');
		}

		return $result;
	}

	public static function getOrderTypeShow($key = null)
	{

		if ($key == Ref::ORDER_STATUS_CANCEL || $key == Ref::ORDER_STATUS_DECLINE || $key == Ref::ORDER_STATUS_CALL_OFF)
			return "交易取消";

		elseif ($key == Ref::ORDER_STATUS_COMPLETED)
			return "交易完成";

		elseif ($key == Ref::ORDER_STATUS_EVALUATE)
			return "已评价";
		else
			return self::getOrderType($key);

	}

	public static function getOrderType($key = null)
	{
		$data = [
			Ref::ORDER_STATUS_DEFAULT        => '待处理',
			Ref::ORDER_STATUS_AWAITING_PAY   => "等待支付",
			Ref::ORDER_STATUS_PAYMENT_VERIFY => "等待网关确认支付",
			Ref::ORDER_STATUS_CANCEL         => "客户取消",
			Ref::ORDER_STATUS_DECLINE        => '小帮取消',
			Ref::ORDER_STATUS_DOING          => '进行中',
			Ref::ORDER_STATUS_COMPLETED      => '已完成',
			Ref::ORDER_STATUS_EVALUATE       => '已评价',
			Ref::ORDER_STATUS_DISPUTE        => '客服处理',
			Ref::ORDER_STATUS_CALL_OFF       => '平台取消',
		];


		return isset($data[$key]) ? $data[$key] : $key;
	}

	/**
	 * 预退款
	 */
	public static function preRefund($params)
	{

		Yii::$app->debug->pay_info("preRefund", $params);
		if (count($params['trade']) > 0) {
			$tradeData = $params['trade'];
			foreach ($tradeData as $trade) {

				$out_trade_no   = $trade['trade_no'];            //原支付宝交易单号
				$transaction_no = $trade['transaction_no'];        //新退款单号
				$fee            = $trade['fee'];
				$payment_id     = $trade['payment_id'];
				$type           = $trade['type'];
				$app_id         = isset($trade['app_id']) ? $trade['app_id'] : null;

				if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {//支付宝
					$payRes = AlipayHelper::refundByTradeNo($out_trade_no, $fee);

					if ($payRes) {
						//更新订单- 更新流水表
						if ($type == Ref::TRANSACTION_TYPE_REFUND)
							TransactionHelper::successOrderRefund($transaction_no, '支付宝订单退款成功', json_encode($payRes));

						if ($type == Ref::TRANSACTION_TYPE_TIPS_REFUND)
							TransactionHelper::successOrderFeeRefund($transaction_no, '支付宝附加费退款成功', json_encode($payRes));
					}
				}

				if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {//微信

					$payRes = WxpayHelper::refundForAllProvider($app_id, $out_trade_no, $transaction_no, $fee);
					if ($payRes) {

						if ($type == Ref::TRANSACTION_TYPE_REFUND)
							TransactionHelper::successOrderRefund($transaction_no, '微信订单退款成功', json_encode($payRes));

						if ($type == Ref::TRANSACTION_TYPE_TIPS_REFUND)
							TransactionHelper::successOrderFeeRefund($transaction_no, '微信小费退款成功', json_encode($payRes));

					} else {

						//TODO 队列去查询微信是否退款
					}
				}

				if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {//余额

					if ($type == Ref::TRANSACTION_TYPE_REFUND)
						TransactionHelper::successOrderRefund($transaction_no, '订单余额退款成功');

					if ($type == Ref::TRANSACTION_TYPE_TIPS_REFUND)
						TransactionHelper::successOrderFeeRefund($transaction_no, '小费余额退款成功');
				}
			}
		}
	}

	//自动取消订单
	public static function autoCancelOrder($orderId)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_id' => $orderId, 'robbed' => Ref::ORDER_ROB_NEW, 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {

				$updateData        = [
					'order_status' => Ref::ORDER_STATUS_CALL_OFF,
					'cancel_time'  => time()
				];
				$order->attributes = $updateData;

				$order->save() ? $result = true : Yii::error("auto cancel order:" . json_encode($order->getErrors()));

				//记录业务日志
				$result &= self::saveLogContent($order->order_id, 'auto_cancel_order', $updateData, '订单超时自动取消');

				$tradeRes = null;
				//自动退款

				if ($order->payment_status == Ref::PAY_STATUS_COMPLETE) {

					//创建交易退款流水记录
					$params['user_id'] = $order->user_id;
					$tradeRes          = TransactionHelper::createOrderRefund($order->order_id, $order->payment_id, $params);
					$result            &= $tradeRes;
				}

				if ($result) {
					$result = [
						'order_no'          => $order->order_no,
						'robbed'            => $order->robbed,
						'cancel_type'       => '',
						"user_id"           => $order->user_id,
						"provider_id"       => $order->provider_id,
						"request_cancel_id" => $order->request_cancel_id,
						'trade'             => $tradeRes,
						'payment_id'        => $order->payment_id,
						'order_from'        => $order->order_from,
					];
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 *用户逻辑删除
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function userDelete($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$status = [
				Ref::ORDER_STATUS_COMPLETED,
				Ref::ORDER_STATUS_EVALUATE,
				Ref::ORDER_STATUS_CALL_OFF,
				Ref::ORDER_STATUS_DECLINE,
				Ref::ORDER_STATUS_CANCEL,
			];

			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => $status]);
			if ($order) {
				$updateData = [
					'user_deleted' => true,
					'update_time'  => time(),
				];

				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("user delete:" . json_encode($order->getErrors()));
				$result &= self::saveLogContent($order->order_id, 'user_delete', $updateData, '用户删除列表订单');
				if ($result) {
					$result = [
						'order_no' => $order->order_no
					];
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 小帮删除
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function workerDelete($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$status = [
				Ref::ORDER_STATUS_COMPLETED,
				Ref::ORDER_STATUS_EVALUATE,
				Ref::ORDER_STATUS_CALL_OFF,
				Ref::ORDER_STATUS_DECLINE,
				Ref::ORDER_STATUS_CANCEL,
			];

			$order = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id'], 'order_status' => $status]);
			if ($order) {
				$updateData = [
					'provider_deleted' => true,
					'update_time'      => time(),
				];

				$order->attributes = $updateData;
				$order->save() ? $result = true : Yii::error("worker delete:" . json_encode($order->getErrors()));
				$result &= self::saveLogContent($order->order_id, 'worker_delete', $updateData, '小帮删除列表订单');
				if ($result) {
					$result = [
						'order_no' => $order->order_no
					];
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 判断是否未完成订单
	 */
	public static function isCanOrder($user_id, $cate_id = Ref::CATE_ID_FOR_ERRAND_BUY)
	{
		$result = [
			'order_no'    => "0",
			'order_count' => 0,
		];
		$order  = Order::find()->select(["order_no"])
			->where(['user_id' => $user_id, 'order_status' => Ref::ORDER_STATUS_DOING, 'cate_id' => $cate_id])
			->orderBy("order_id DESC")->asArray()
			->one();

		if (count($order) > 0) {
			$result['order_count'] = 1;    //前端判断是否有订单	//默认1条
			$result['order_no']    = $order['order_no'];
		}

		return $result;
	}


	public static function isDoingOrder($provider_id)
	{
		$result = false;
		$order  = Order::find()->select(["count(order_id) as order_count"])
			->where(['provider_id' => $provider_id, 'order_status' => Ref::ORDER_STATUS_DOING])
			->asArray()
			->one();
		if ($order['order_count'] > 0) {
			$result = $order['order_count'];
		}

		return $result;
	}

	/**
	 * 通用获取距离和价格的方法
	 */
	public static function getRangeAndPrice($params)
	{
		$start_location = $params['start_location'];
		$end_location   = $params['end_location'];
		$cate_id_type   = $params['cate_id'];

		$route         = AMapHelper::bicycling(AMapHelper::coordToStr($start_location), AMapHelper::coordToStr($end_location)); //订单起点坐标，商家当前坐标
		$distance      = 0;
		$distance_text = '0米';
		$duration      = 0;
		$duration_text = '0秒';
		if (is_array($route)) {
			$duration      = $route['duration'];
			$duration_text = UtilsHelper::durationLabel($route['duration']);
			$distance      = $route['distance'];
			$distance_text = UtilsHelper::distance($distance);
		}

		$user_city_id = isset($params['city_id']) ? $params['city_id'] : null;

		$city_price = RegionHelper::getCityPrice($start_location, $user_city_id, $cate_id_type);

		$range_init = $city_price['range_init'];//初始距离
		$range      = $distance - $range_init;
		$price      = $city_price['range_init_price'];

		//根据距离得出价格
		if ($range > 0) {
			$price = $city_price['range_init_price'] + $range / 1000 * $city_price['range_unit_price'];
		}

		$price += $city_price['service_fee'];

		return [
			'distance'      => $distance,
			'distance_text' => '约' . $distance_text,
			'price'         => sprintf("%.2f", $price),
			'duration'      => $duration,
			'duration_text' => $duration_text,
		];
	}

	public static function getHistoryAddress($user_id)
	{
		$result = [];
		$data   = Order::find()->select(['end_location', 'end_address'])->where(['cate_id' => [Ref::CATE_ID_FOR_ERRAND_BUY, Ref::CATE_ID_FOR_ERRAND_SEND, Ref::CATE_ID_FOR_ERRAND_DO], 'user_id' => $user_id])->orderBy(['create_time' => SORT_DESC])->limit(5)->asArray()->all();
		if ($data) {
			foreach ($data as $k => $v) {
				$result[$k]['history_location'] = $v['end_location'];
				$address_arr                    = explode('-', $v['end_address']);
				$result[$k]['address']          = $address_arr[0];
				$result[$k]['ext_address']      = isset($address_arr[1]) ? $address_arr[1] : '';
			}
		}

		return $result;
	}
}

