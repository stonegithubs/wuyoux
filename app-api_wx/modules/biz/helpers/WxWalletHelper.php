<?php

namespace api_wx\modules\biz\helpers;

use common\helpers\HelperBase;
use common\helpers\payment\TransactionHelper;
use common\helpers\security\SecurityHelper;
use common\models\orders\Order;
use common\models\orders\OrderFee;
use common\models\orders\RechargeOrder;
use common\models\payment\Transaction;
use yii\data\Pagination;
use yii\db\Query;
use Yii;

class WxWalletHelper extends HelperBase
{
	public static function transactionList($params)
	{
		$result       = false;
		$where        = [];
		$where['uid'] = $params['user_id'];
		if (isset($params['type'])) {
			switch ($params['type']) {
				case 1://收入
					$where['type'] = 1;
					break;
				case 2:
					//支出
					$where['type'] = 2;
					break;
				default:
			}
		}
		$current_page = !empty($params['page']) ? intval($params['page']) : 1;
		$pageSize     = !empty($params['pageSize']) ? intval($params['pageSize']) : 20;
		$separateDate = $params['last_separate'];

		$subQuery = (new Query())->from('bb_51_income_pay')->select('id')->where($where)->andWhere(['!=', 'title', '小帮快送-商品费用']);
		$count    = $subQuery->count();
		$page     = 0;
		if ($current_page > 0) {
			$page = $current_page - 1;
		}
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $pageSize;
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = (new Query())->from(['ip' => 'bb_51_income_pay', 't2' => $subQuery])->select("*")->where("ip.id=t2.id")->orderBy(['ip.create_time' => SORT_DESC])->all();

		$list          = self::getTransactionData($separateDate, $data, $params['user_id']);
		$last_separate = 0;
		if ($list) {
			$last_separate = $list['last_separate'];
			unset($list['last_separate']);
		}

		$result['list'] = $list;

		$result['pagination']
			= [
			'page'          => $current_page,
			'pageSize'      => $pageSize,
			'pageCount'     => $pagination->pageCount,
			'totalCount'    => $pagination->totalCount,
			'last_separate' => $last_separate,
		];

		return $result;

	}

	public static function getTransactionData($separateDate, $data, $user_id)
	{
		$result = [];
		foreach ($data as $key => $value) {
			if (date('Y-m', $value['create_time']) != $separateDate) {
				$separateDate                  = date('Y-m', $value['create_time']);
				$result[$key]['separate']      = 1;
				$result[$key]['separate_date'] = $separateDate;
				$money                         = self::getMonthMoney($separateDate, $user_id);
				$result[$key]['spend_money']   = $money['spend_money'];
				$result[$key]['gain_money']    = $money['gain_money'];
				$result['last_separate']       = $separateDate;
			} else {
				$result[$key]['separate'] = 0;
			}

			if ($value['type'] == 1) {
				$result[$key]['money'] = '+' . $value['money'];
			} else {
				$result[$key]['money'] = '-' . $value['money'];
			}
			$result[$key]['date']     = date('Y-m-d', $value['create_time']);
			$result[$key]['year']     = date('Y', $value['create_time']);
			$result[$key]['monthDay'] = date('m-d', $value['create_time']);
			$result[$key]['title']    = $value['title'];
			$result[$key]['id']       = $value['id'];
			$result[$key]['icon']     = $value['type2'];
			$result[$key]['type']     = $value['type'];
			$result['last_separate']  = isset($result['last_separate']) ? $result['last_separate'] : $separateDate;
		}

		return $result;
	}

	public static function getMonthMoney($separateDate, $user_id)
	{
		$result = [];
		$date   = explode('-', $separateDate);
		if ($date[1] == 12) {
			$endDate = ($date[0] + 1) . '-01-01';
		} else {
			$endDate = $date[0] . '-' . ($date[1] + 1) . '-01';
		}
		$startDate             = $separateDate . '-01';
		$startTime             = strtotime($startDate);
		$endTime               = strtotime($endDate);
		$result['spend_money'] = (new Query())->from('bb_51_income_pay')->where(['uid' => $user_id, 'type' => 2])->andWhere(['between', 'create_time', $startTime, $endTime])->sum('money');
		$result['gain_money']  = (new Query())->from('bb_51_income_pay')->where(['uid' => $user_id, 'type' => 1])->andWhere(['between', 'create_time', $startTime, $endTime])->sum('money');

		return $result;
	}

	public static function transactionDetail()
	{
		$result      = false;
		$income_id   = SecurityHelper::getBodyParam('transactionId');
		$income_data = (new Query())->from("bb_51_income_pay")->where(['id' => $income_id])->one();
		if ($income_data) {
			$sub = substr($income_data['did'], 0, 1);
			if ($sub == "T" || $sub == "R") {
				$order              = self::getOrderNo($income_data['did']);
				$result['order_no'] = isset($order['order_no']) ? $order['order_no'] : null;
				$result['payment']  = isset($order['payment']) ? $order['payment'] : null;
				$result['title']    = isset($order['title']) ? $order['title'] : null;
			} else {
				$old_order          = (new Query())->from('bb_51_orders')->select(['paylx'])->where(['orderid' => $income_data['did']])->one();
				$result['order_no'] = $income_data['did'];
				$result['payment']  = TransactionHelper::getPaymentType($old_order['paylx']);
				$result['title']    = TransactionHelper::getIncomeType($income_data['title']);
			}
			$result['create_time'] = date('Y-m-d H:i:s', $income_data['create_time']);
			$result['content']     = $income_data['title'];
			$symbol                = $income_data['type'] == 1 ? '+' : '-';
			$result['money']       = $symbol . $income_data['money'];
			$result['icon']        = $income_data['type2'];
		}

		return $result;
	}

	public static function getOrderNo($income_order)
	{
		$result      = [];
		$transaction = Transaction::findOne(['transaction_no' => $income_order]);
		if ($transaction) {
			switch ($transaction->type) {
				case 1://订单
					$order              = Order::find()->select(['order_no'])->where(['order_id' => $transaction->ids_ref])->one();
					$result['order_no'] = $order->order_no;
					$result['title']    = "订单支付";
					break;
				case 2://充值
					$recharge_order     = RechargeOrder::findOne(['id' => $transaction->ids_ref]);
					$result['order_no'] = $recharge_order->recharge_no;
					$result['title']    = "余额充值";
					break;
				case 3://退款
					$order              = Order::find()->select(['order_no'])->where(['order_id' => $transaction->ids_ref])->one();
					$result['order_no'] = $order->order_no;
					$result['title']    = "订单退款";
					break;
				case 4:
					//小费
					$fee                = OrderFee::find()->select(['ids_ref'])->where(['fee_id' => $transaction->ids_ref])->one();
					$order              = Order::find()->select(['order_no'])->where(['order_id' => $fee->ids_ref])->one();
					$result['order_no'] = $order->order_no;
					$result['title']    = "小费支付";
					break;
			}
			$result['payment'] = TransactionHelper::getPaymentType($transaction->payment_id);
		}

		return $result;
	}

	/**
	 * 获取收支1.1
	 * @return array|bool|mixed
	 */
	public static function transactionDetailV11()
	{
		$result    = false;
		$income_id = SecurityHelper::getBodyParam('transactionId');
		$income    = (new Query())->from("bb_51_income_pay")->where(['id' => $income_id])->one();
		if ($income) {
			$income_data = [
				'icon'           => $income['type2'],                                                            //图标
				'title'          => $income['title'],                                                            //明细标题
				'money'          => ($income['type'] == 1) ? '+' . $income['money'] : '-' . $income['money'],    //收支金额
				'current_satus'  => '交易完成',                                                                   //当前状态
				'create_time'    => date('Y-m-d H:i:s', $income['create_time']),                                 //创建时间
				'transaction_no' => $income['did'],                                                              //交易单号
				'trade_no'       => null,                                                                        //商户单号
			];

			$sub = substr($income['did'], 0, 1);
			if ($sub == "T" || $sub == "R") {
				$result = self::getNewOrderIncome($income_data);
			} else {
				$result = self::getOldOrderIncome($income_data);
			}
		}

		return $result;
	}

	/**
	 * 获取旧订单收支
	 * @param $income_data
	 * @return mixed
	 */
	public static function getOldOrderIncome($income_data)
	{
		$result = [
			'many_orders' => 0,                   //是否多单 0否 1是
			'income'      => $income_data,        //交易数据
			'order'       => null,                //一单数据
			'orders'      => null                 //多单数据
		];

		if (!$income_data['transaction_no']) {
			return $result;
		}

		$order = (new Query())->from('bb_51_orders')->select('*')->where(['orderid' => $income_data['transaction_no']])->one();
		if ($order) {
			$cate     = (new  Query())
				->select('name')
				->from('bb_catelist')
				->where(['id' => $order['cate_id']])
				->one();
			$cateName = $cate['name'];
			if ($order['cate_id'] == 132) {
				if ($order['errand_type'] == 1) {
					$cateName = '帮我买';
				} else {
					$cateName = '帮我送';
				}
			}

			$result['order'] = [
				'order_no'       => $order['orderid'],
				'order_time'     => date('Y-m-d H:i:s', $order['create_time']),
				'order_amount'   => $order['money'],
				'amount_payable' => $order['n_money'],
				'discount'       => bcsub($order['money'], $order['n_money'], 2),
				'order_name'     => $cateName,
				'tips_fee'       => '0.00'
			];
		}

		return $result;
	}

	/**
	 * 获取新订单收支
	 * @param $income_data
	 * @return array
	 */
	public static function getNewOrderIncome($income_data)
	{
		$result = [
			'many_orders' => 0,                   //是否多单 0否 1是
			'income'      => $income_data,        //交易数据
			'order'       => null,                //一单数据
			'orders'      => null                 //多单数据
		];

		if (!$income_data['transaction_no']) {
			return $result;
		}

		$transaction = Transaction::findOne(['transaction_no' => $income_data['transaction_no']]);
		if ($transaction) {
			$result['income']['trade_no'] = $transaction->trade_no;
			switch ($transaction->type) {
				case 2://充值
					break;
				case 4://商品费用、小费
					$fee             = OrderFee::find()->select(['ids_ref'])->where(['fee_id' => $transaction->ids_ref])->one();
					$result['order'] = self::getOneNewOrder($fee->ids_ref);
					break;
				default ://订单
					if (strpos($transaction->ids_ref, ",")) {
						$result['many_orders'] = 1;
						$result['orders']      = self::getAllNewOrder($transaction->ids_ref);
					} else {
						$result['order'] = self::getOneNewOrder($transaction->ids_ref);
					}
					break;
			}

			//获取商品费用、小费金额
			$fee = OrderFee::find()->select(['amount'])->where(['ids_ref' => $result['order']['order_id']])->one();
			if ($fee) {
				if ($result['order']['cate_id'] != 135) {
					$result['order']['tips_fee'] = $fee->amount;
				}
			}
		}

		return $result;
	}


	/**
	 * 获取一条新订单
	 * @param $ids_ref
	 * @return array
	 */
	public static function getOneNewOrder($ids_ref)
	{
		$result = null;

		$order = Order::find()->select('order_id,cate_id,order_no,create_time,order_amount,amount_payable,discount')->where(['order_id' => $ids_ref])->one();
		if ($order) {
			$cate     = (new  Query())
				->select('name')
				->from('bb_catelist')
				->where(['id' => $order->cate_id])
				->one();
			$cateName = $cate['name'];

			$result = [
				'order_id'       => $order->order_id,
				'cate_id'        => $order->cate_id,
				'order_no'       => $order->order_no,
				'order_time'     => date('Y-m-d H:i:s', $order->create_time),
				'order_amount'   => $order->order_amount,
				'amount_payable' => $order->amount_payable,
				'discount'       => $order->discount,
				'order_name'     => $cateName,
				'tips_fee'       => '0.00'
			];
		}

		return $result;
	}

	/**
	 * 获取多条新订单
	 * @param $ids_ref
	 * @return mixed
	 */
	public static function getAllNewOrder($ids_ref)
	{
		$result = null;

		$orderId = explode(',', $ids_ref);
		$order   = Order::find()->select('cate_id,order_no,create_time,order_amount,amount_payable,discount')->where(['order_id' => $orderId])->asArray()->all();
		if ($order) {
			foreach ($order as $key => $value) {
				$cate     = (new  Query())
					->select('name')
					->from('bb_catelist')
					->where(['id' => $value['cate_id']])
					->one();
				$cateName = $cate['name'];

				$result[$key] = [
					'order_no'       => $value['order_no'],
					'order_time'     => date('Y-m-d H:i:s', $value['create_time']),
					'order_amount'   => $value['order_amount'],
					'amount_payable' => $value['amount_payable'],
					'discount'       => $value['discount'],
					'order_name'     => $cateName
				];
			}
		}

		return $result;
	}
}