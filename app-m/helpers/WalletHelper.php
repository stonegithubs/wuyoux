<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/10
 * Time: 18:09
 */

namespace m\helpers;

use common\helpers\HelperBase;
use common\models\orders\Order;
use common\models\orders\OrderFee;
use common\models\payment\Transaction;
use yii\db\Query;

class WalletHelper extends HelperBase
{
	/**
	 * 用户收支明细html 样式
	 *
	 * @param int type (1:红包;2:话费充值3:余额充值;4:订单支付;5:订单金额退款;6:后台充值;7:后台回收;8:平台赠送;9:提现)
	 */
	public static function incomeUserStyle($type)
	{
		switch (intval($type)) {
			case 1:
				$data = 'iconfont icon-hongbao txt_red';
				break;//红包
			case 2:
				$data = 'iconfont icon-shouji1 txt_green';
				break;//话费充值
			case 3:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//余额充值
			case 4:
				$data = 'iconfont icon-dingdan3 txt_orange';
				break;//订单支付
			case 5:
				$data = 'iconfont icon-dingdan3 txt_orange';
				break;//订单金额退款
			case 6:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//后台充值
			case 7:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//后台回收
			case 8:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//平台赠送
			case 9:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//营销收入
			case 11:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//购买优惠券
			case 12:
				$data = 'iconfont icon-chongzhi50 txt_pink';
				break;//营销收入
		}

		return $data;
	}

	/**
	 * 获取收支
	 * @return array|bool|mixed
	 */
	public static function getIncome($income_id)
	{
		$result = false;
		$income = (new Query())->from("bb_51_income_pay")->where(['id' => $income_id])->one();
		if ($income) {
			$income_data = [
				'icon'           => $income['type2'],                                                            //图标
				'title'          => $income['title'],                                                            //明细标题
				'money'          => ($income['type'] == 1) ? '+' . $income['money'] : '-' . $income['money'],    //收支金额
				'current_satus'  => '交易完成',                                                                   //当前状态
				'create_time'    => date('Y-m-d H:i:s', $income['create_time']),                                 //创建时间
				'transaction_no' => $income['did'],                                                              //交易单号
				'trade_no'       => null,                                                                        //商户单号
				'list_style'     => 'iconfont icon-dingdan3 txt_orange'
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
	 * 获取商家收支明细详情
	 */
	public static function getShopIncomeDetail($income_id)
	{
		$result = (new Query())->select('*')->from('bb_51_income_shop')->where(['id' => $income_id])->one();
		if ($result) {
			$back_data                     = $result;
			$back_data['create_time_date'] = ($result['create_time'] == 0) ? date('Y-m-d H:i:s', $result['update_time']) : date('Y-m-d H:i:s', $result['create_time']);

			switch ($result['type']) {
				case 1:
					//订单收款
					$back_data['list_style'] = 'iconfont icon-dingdan3 txt_orange';
					$new_order               = (new Query())->select('*')->from('wy_order')->where(['order_no' => $result['order_id']])->one();
					if ($new_order) {
						$cate_list                     = (new Query())->select('*')->from('bb_catelist')->where(['id' => $new_order['cate_id']])->one();
						$back_data['list_title']       = $cate_list['name'] . '-订单收款';
						$back_data['cate_name']        = $cate_list['name'];
						$back_data['order_id']         = $result['order_id'];
						$back_data['total_price']      = $result['money'];
						$back_data['commission_price'] = 0;
					} else {
						$order    = (new Query())->select('cate_id,errand_type,money,s_money')->from('bb_51_orders')->where(['orderid' => $result['order_id']])->one();
						$cate     = (new Query())->select('name')->from('bb_catelist')->where(["id" => $order['cate_id']])->one();
						$catename = $cate['name'];

						if ($order['cate_id'] == 132) {
							$second_catename = '';
							if ($order['errand_type'] == 1) {
								$second_catename .= '-帮我买';
							} elseif ($order['errand_type'] == 2) {
								$second_catename .= '-帮我送';
							}
							$back_data['list_title'] = $catename . '-订单收款';
							$back_data['cate_name']  = $catename . '-' . $second_catename;
						} else {
							$back_data['list_title'] = $catename . '-订单收款';
							$back_data['cate_name']  = $catename;
						}
						$commission_price              = $order['money'] - $order['s_money'];
						$back_data['total_price']      = $order['money'];
						$back_data['commission_price'] = ($commission_price > 0) ? $commission_price : 0;
					}
					break;
				case 2:
					//提现
					if ($result['status'] == 0) {
						$back_data['list_statusname'] = '待提现';
					} else {
						$back_data['list_statusname'] = '提现成功';
					}
					$back_data['update_time_data'] = ($result['update_time'] == 0) ? date('Y-m-d H:i:s', $result['create_time']) : date('Y-m-d H:i:s', $result['update_time']);
					$back_data['list_style']       = 'iconfont icon-tixian txt_grass_green';
					$back_data['list_title']       = '提现';
					break;
				case 3:
					//平台赠送
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '平台赠送';
					break;
				case 4:
					//平台回收
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '平台回收';
					break;
				case 5:
					//红包发放
					$back_data['list_style'] = 'iconfont icon-hongbao txt_red';
					$back_data['list_title'] = '红包发放';
					break;
				case 6:
					//红包退回
					$back_data['list_style'] = 'iconfont icon-hongbao txt_red';
					$back_data['list_title'] = '红包退回';
					break;
				case 7:
					//解冻保证金
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '解冻保证金';
					break;
				case 8:
					//交纳保证金
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '交纳保证金';
					break;
				case 9:
					//在线宝转入余额
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '在线宝转入余额';
					break;
				case 10:
					//在线宝转入余额
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '余额支付保证金';
					break;
				case 11:
					//余额支付保证金
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '保险扣费';
					break;
				case 12:
					//全民营销
					$back_data['list_style'] = 'iconfont icon-chongzhi50 txt_pink';
					$back_data['list_title'] = '营销收入';
					break;
			}
			if ($result['account_type'] == 1) {
				$back_data['show_money'] = '+' . $result['money'];
			} else {
				$back_data['show_money'] = '-' . $result['money'];
			}
		} else {
			$back_data = '';
		}

		return $back_data;
	}

	/**
	 * 获取多个订单信息
	 * @param $income_id
	 * @return array|bool
	 */
	public static function findOrders($income_id)
	{
		$result = null;
		//交易数据
		$income = (new Query())->select('did,money,title,type,type2')->from('bb_51_income_pay')->where(['id' => $income_id])->one();
		if ($income) {
			if ($income['type'] == 1) {
				$income['money'] = '+' . $income['money'];
			} else {
				$income['money'] = '-' . $income['money'];
			}
			$income['icon_style'] = WalletHelper::incomeUserStyle($income['type2']);
		}
		$result['income'] = $income ? $income : null;

		//订单数据
		$transaction_info = (new Query())
			->select('ids_ref')
			->from('wy_transaction')
			->where(['transaction_no' => $income['did']])
			->one();
		$orderId          = explode(',', $transaction_info['ids_ref']);
		$order            = Order::find()->select('cate_id,order_no,create_time,order_amount,amount_payable,discount')
			->where(['order_id' => $orderId])->asArray()->all();
		if ($order) {
			foreach ($order as $key => $value) {
				$cate     = (new  Query())
					->select('name')
					->from('bb_catelist')
					->where(['id' => $value['cate_id']])
					->one();
				$cateName = $cate['name'];

				$orderInfo[$key] = [
					'order_no'       => $value['order_no'],
					'order_time'     => date('Y-m-d H:i:s', $value['create_time']),
					'order_amount'   => $value['order_amount'],
					'amount_payable' => $value['amount_payable'],
					'discount'       => $value['discount'],
					'order_name'     => $cateName
				];
			}
		}
		$result['order'] = isset($orderInfo) ? $orderInfo : null;

		return $result;
	}
}