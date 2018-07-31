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
use common\helpers\images\ImageHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderFee;
use yii\base\Exception;
use Yii;
use yii\helpers\ArrayHelper;

//帮我办
class ErrandDoHelper extends ErrandHelper
{
	/**
	 * 默认每小时服务费
	 * @return float
	 */
	public static function getServicePrice($service_location, $user_id)
	{
		$user_info  = UserHelper::getUserInfo($user_id, 'city_id');
		$city_price = RegionHelper::getCityPrice($service_location, $user_info['city_id'], Ref::CATE_ID_FOR_ERRAND_DO);

		return sprintf("%.2f", $city_price['time_unit_price']);    //改为超过多少时的值
	}

	public static function checkCanOrder($user_id)
	{
		return parent::isCanOrder($user_id, Ref::CATE_ID_FOR_ERRAND_DO);
	}

	/**
	 * 用户端 详情
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function userDetail($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {

				$total_fee    = doubleval($errand->total_fee);
				$service_fee  = doubleval($errand->service_price) * $errand->service_qty;
				$order_amount = $total_fee + $service_fee;           //订单总金额 = 总小费 + 服务时长

				$result = [
					'order_no'              => $order->order_no,
					'order_type'            => "小帮快送-" . self::getErrandType($errand->errand_type),    //发单类型
					'service_location'      => $order->start_address, //服务地点
					'content'               => $errand->errand_content,                                    //内容
					'service_time'          => isset($errand->service_time) ? date("m-d H:i", $errand->service_time) : '',    //服务时间
					'user_mobile'           => $order->user_mobile,            //联系电话
					'service_qty'           => $errand->service_qty,        //服务数量
					'service_price'         => $service_fee,        //服务价格
					'total_fee'             => $total_fee,                    //总小费
					'order_amount'          => sprintf("%.2f", $order_amount),    //订单总金额
					'payment_type'          => TransactionHelper::getPaymentType($order->payment_id),    //支付方式
					'publish_time'          => isset($order->payment_time) ? date("m-d H:i", $order->payment_time) : null,   //发布时间 就是支付时间
					'robbed_time'           => isset($order->robbed_time) ? date("m-d H:i", $order->robbed_time) : null,    //接单时间
					'begin_time'            => isset($errand->begin_time) ? date("m-d H:i", $errand->begin_time) : null,//开始时间
					'finish_time'           => isset($errand->finish_time) ? date("m-d H:i", $errand->finish_time) : null,//完成时间
					'errand_status'         => $errand->errand_status,          //状态
					"platform_phone"        => Ref::PLATFORM_PHONE,        //平台电话
					"platform_service_time" => Ref::SERVICE_TIME,         //平台服务时间
					"robbed"                => $order->robbed,
					'cancel_time'           => isset($order->cancel_time) ? date('m-d H:i', $order->cancel_time) : null,
				];

				//抢单后 小帮信息
				if ($order->robbed == Ref::ORDER_ROBBED) {    //已经接单 显示小帮信息

					$provider = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->provider_address);
					$result   = array_merge($result, $provider);
				}


				//订单明细添加
				if ($params['current_page'] == "finish") {

					$evaluate = EvaluateHelper::getEvaluateInfo($order->order_no);
					if ($evaluate) {

						$result['evaluate']     = $evaluate;
						$result['can_evaluate'] = 0;    //有评价信息 不能评价

					} else {

						$result['evaluate']     = null;
						$result['can_evaluate'] = 1;    //没有评价信息可以评价
					}
				}

				if ($params['current_page'] == "task") {
					if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_APPLY) {
						$result['cancel_content'] = self::CANCEL_PROVIDER_APPLY_MSG;
					}

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE) {
						$result['cancel_content'] = self::CANCEL_PROVIDER_AGREE_MSG;
					}

					if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_DISAGREE) {
						$result['cancel_content'] = self::CANCEL_PROVIDER_DISAGREE_MSG;
					}

					//如果是客服的消息
					if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY || $errand->cancel_type == Ref::ERRAND_CANCEL_USER_NOTIFY) {
						$result['cancel_content'] = $order->order_status == Ref::ORDER_STATUS_CALL_OFF
							? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;
					}

					$result['cancel_type'] = $errand->cancel_type;
				}

				if ($params['current_page'] == 'cancel') {
					$result['order_status'] = OrderHelper::getOrderTypeShow($order->order_status);  //按照文档显示正确的状态
				}

			}
		}

		return $result;
	}

	/**
	 * 计价
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function getCalc($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no']]);//->getAttributes(['order_amount','order_id']);
		if ($model) {

			$errand       = OrderErrand::findOne(['order_id' => $model->order_id]);//->getAttributes(['total_fee','first_fee']);
			$first_fee    = isset($errand->first_fee) ? $errand->first_fee : 0;
			$total_fee    = isset($errand->total_fee) ? $errand->total_fee : 0;
			$order_amount = $model->order_amount;

			$price_data             = RegionHelper::getCityPrice($model->user_location, $model->region_id, Ref::CATE_ID_FOR_ERRAND_DO);
			$params['online_money'] = $params['online_money'] * $price_data['online_money_discount'];
			$orderHelper            = new OrderHelper();
			$calc                   = $orderHelper->getOrderCalc($order_amount, $params);
			if ($params['errand_type'] == Ref::ERRAND_TYPE_DO) {

				$card_amount    = sprintf("%.2f", $calc['card_amount']);
				$amount_payable = sprintf("%.2f", $calc['amount_payable']);
				$online_money   = sprintf("%.2f", $calc['online_money']);
				$total_fee      = sprintf("%.2f", $total_fee);
				$order_amount   = sprintf("%.2f", ($order_amount - $first_fee));
				$service_price  = sprintf("%.2f", $errand->service_price);
				$discount       = sprintf("%.2f", $calc['discount']);
				$html
								= <<<EOF
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1"><title>订单明细</title> <style>body, table{ font-size:13px; margin:0; padding:0; color:#888;font-family: Helvetica, Tahoma, Arial, "Hiragino Sans GB", "Hiragino Sans GB W3", "Microsoft YaHei", STXihei, STHeiti, Heiti, SimSun, sans-serif;font-weight: normal;} .je{ font-size:26px;margin-top: 1em;margin-right:5px;color: #FF0000;} h2{ font-size: 15px;font-weight: normal;text-indent: .5em; line-height:32px; padding-top:20px;color:#333;} p{padding-left: 2em;line-height: 22px;margin: .5em;}
table{ width:80%; margin:auto;}
table td{ line-height:28px;}
</style></head><body><table border="0" cellpadding="0" cellspacing="0">
<tbody>
  <tr>
    <td height="45" colspan="2" align="center"><h2>— 合计预估 —</h2></td>
    </tr>
  <tr>
  <tr>
    <td height="45" colspan="2" align="center"><span class="je">$amount_payable</span>元</td>
    </tr>
  <tr>
    <td width="50%">小帮赏金</td>
    <td align="right"><span>$order_amount<span>元</td>
  </tr>
  <tr>
    <td>小费</td>
    <td align="right"><span>$total_fee<span>元</td>
  </tr>
  <tr>
    <td>卡券抵扣</td>
    <td align="right"><span>$card_amount<span>元</td>
  </tr>
</tbody>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="0">
<tbody>
  <tr>
    <td height="45" colspan="2" align="center"><h2>— 计价规则 —</h2></td>
    </tr>
  <tr>
    <td width="45%">帮我办</td>
    <td align="right">预付费<span>$service_price<span>元/小时</td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">在日常使用过程中，如遇到超时的情况，可通过增加小费的方式，给小帮付款或打赏。</td>
  </tr>
</tbody>
</table>
</body>
</html>
EOF;
				$result         = [
					'card_amount'    => $card_amount, //扣除优惠券的金额
					'amount_payable' => $amount_payable, //实付金额
					'online_money'   => $online_money, //扣除在线宝可用金额部分
					'total_fee'      => $total_fee, //总小费
					'order_amount'   => $order_amount, //订单金额
					'service_price'  => $service_price,  //服务单价
					'discount'       => $discount, //优惠券和在线宝一共抵扣
					'html'           => htmlspecialchars_decode($html),
				];
			}
		}

		return $result;
	}

	/**
	 * 小帮抢单成功后的Task
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function workerTask($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {

				$total_fee      = doubleval($errand->total_fee);
				$service_amount = doubleval($errand->service_price * $errand->service_qty);
				$order_amount   = $service_amount + $total_fee;
				$result         = [
					'order_no'              => $order->order_no,
					'content'               => $errand->errand_content,                                    //内容
					'service_time'          => UtilsHelper::todayTimeFormat($errand->service_time),
					'service_qty_price'     => $errand->service_qty . "小时,共" . sprintf("%.2f", $service_amount) . "元",        //服务时长包含价格
					'service_price'         => $errand->service_price,
					'service_qty'           => $errand->service_qty,
					'service_location'      => UtilsHelper::checkVersionCoord($params, $order->start_location),
					'service_address'       => $order->start_address,
					'starting_distance'     => isset($errand->starting_distance) ? $errand->starting_distance : 0,        //小帮距离订单起点
					'user_mobile'           => $order->user_mobile,
					'total_fee'             => sprintf("%.2f", $total_fee),       //总小费
					'order_amount'          => sprintf("%.2f", $order_amount),    //订单总金额
					'begin_time'            => $errand->begin_time,
					'finish_time'           => $errand->finish_time,
					'errand_status'         => $errand->errand_status,
					'cancel_type'           => $errand->cancel_type,
					"platform_service_time" => Ref::SERVICE_TIME,         //平台服务时间
					"platform_phone"        => Ref::PLATFORM_PHONE,       //平台电话
				];
				if ($errand->begin_time) {
					$result['spend_time'] = time() - $errand->begin_time;
				}

				//用户信息
				$userInfo               = UserHelper::getUserInfo($order->user_id);
				$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "无忧帮帮";
				$result['mobile']       = isset($userInfo['mobile']) ? $userInfo['mobile'] : null;
				$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
				$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_APPLY) {
					$result['cancel_content'] = self::CANCEL_USER_APPLY_MSG;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_AGREE) {
					$result['cancel_content'] = self::CANCEL_USER_AGREE_MSG;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_USER_DISAGREE) {
					$result['cancel_content'] = self::CANCEL_USER_DISAGREE_MSG;
				}

				//如果是客服的消息
				if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY || $errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_NOTIFY) {
					$result['cancel_content'] = $order->order_status == Ref::ORDER_STATUS_CALL_OFF
						? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;
				}

			}
		}

		return $result;
	}


	/**
	 * 小帮的订单明细
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function workerDetail($params)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no'], 'provider_id' => $params['provider_id']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {

				$total_fee      = doubleval($errand->total_fee);
				$service_amount = doubleval($errand->service_price * $errand->service_qty);
				$order_amount   = $service_amount + $total_fee;
				$result         = [
					'order_no'              => $order->order_no,
					'content'               => $errand->errand_content,                                    //内容
					'service_time'          => UtilsHelper::todayTimeFormat($errand->service_time),
					'service_qty_price'     => $errand->service_qty . "小时,共" . sprintf("%.2f", $service_amount) . "元",  //服务时长包含价格
					'service_price'         => $errand->service_price,        //服务价格
					'service_qty'           => $errand->service_qty,            //服务数量
					'service_location'      => UtilsHelper::checkVersionCoord($params, $order->start_location),            //服务地点坐标
					'service_address'       => $order->start_address,                //服务地点位置
					'user_mobile'           => $order->user_mobile,
					'total_fee'             => sprintf("%.2f", $total_fee),                    //总小费
					'order_amount'          => sprintf("%.2f", $order_amount),    //订单总金额
					'order_type'            => "小帮快送-" . self::getErrandType($errand->errand_type),
					'payment_type'          => TransactionHelper::getPaymentType($order->payment_id),
					"order_status"          => OrderHelper::getOrderTypeShow($order->order_status),            //TODO 按照文档显示正确的状态
					"order_time"            => $order->create_time,
					"platform_phone"        => Ref::PLATFORM_PHONE,        //平台电话
					"platform_service_time" => Ref::SERVICE_TIME         //平台服务时间
				];
				//用户信息
				$userInfo               = UserHelper::getUserInfo($order->user_id);
				$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "无忧帮帮";
				$result['mobile']       = isset($userInfo['mobile']) ? $userInfo['mobile'] : null;
				$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
				$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);
			}
		}

		return $result;
	}

	/**
	 * 用户确认
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public static function userConfirm($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $params['order_no'], 'user_id' => $params['user_id'], 'order_status' => Ref::ORDER_STATUS_DOING]);
			if ($order) {
				$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
				$status = $errand->errand_status;

				$discount     = $order->discount;                //总优惠
				$online_money = $order->online_money;            //在线宝扣除的金额

				$total_fee   = self::getTotalFee($order->order_id);    //总小费
				$service_fee = doubleval($errand->service_price) * $errand->service_qty;

				$estimate_amount = $total_fee + $service_fee;           //预计收到金额也是订单总金额 = 总小费 + 服务时长

				$pay_amount = $total_fee + $service_fee - $discount;    //用户实际支付 = 总小费+服务时长 - 折扣

				$orderData       = ArrayHelper::toArray($order);
				$provider_amount = WalletHelper::takeMoney($orderData, $estimate_amount); //小帮金额是已经抽佣后的金额

				if ($status == Ref::ERRAND_STATUS_FINISH) {
					$updateData = [
						'provider_estimate_amount' => $estimate_amount,
						'provider_actual_amount'   => $provider_amount,
						'order_status'             => Ref::ORDER_STATUS_COMPLETED,
						'finish_time'              => time(),
					];

					$order->attributes = $updateData;
					$order->save() ? $result = true : Yii::error("save user confirm:" . json_encode($order->getErrors()));

					//记录业务日志
					$result &= self::saveLogContent($order->order_id, 'user_confirm', $updateData, '用户确认订单');

					//确认后需要把钱转到小帮账号
					//1、用户资金变化
					if ($order->payment_id == Ref::PAYMENT_TYPE_BALANCE && $order->amount_payable > 0) {

						$result &= WalletHelper::handleUserBalance($order->user_id, $pay_amount, $online_money);
					}

					//2、小帮资金收入变化
					$result           &= WalletHelper::handleShopBalance($order->provider_id, $provider_amount);
					$shop             = UserHelper::getShopInfo($order->provider_id);
					$provider_user_id = isset($shop['uid']) ? $shop['uid'] : 0;

					//3、店铺收支明细
					$balance = isset($shop['shops_money']) ? $shop['shops_money'] : $provider_amount;
					$result  &= WalletHelper::handleIncomeShop($order->provider_id, $provider_user_id, $order->order_no, $provider_amount, "小帮快送，收入" . $provider_amount . "元", Ref::PROVIDER_BALANCE_IN, Ref::BALANCE_TYPE_IN, $balance);

					//4、增加保险首扣
					$result &= WalletHelper::takeInsuranceFee($orderData);
				}
				if ($result) {
					$result = [
						'order_no'    => $order->order_no,
						'errand_type' => $errand->errand_type
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


	//1、支付成功更新并推送通知
	public static function addCustomFeeSuccess($transaction_no, $trade_no, $payment_id, $fee, $remark = null, $data = null)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();

		try {

			$data = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);

			if ($data) {
				$fee_id   = $data['ids_ref'];
				$orderFee = OrderFee::findOne(['fee_id' => $fee_id, 'status' => Ref::PAY_STATUS_WAIT, 'type' => Ref::FEE_TYPE_PROD]);

				if ($orderFee) {

					$order_id = $orderFee->ids_ref;
					$order    = Order::findOne(['order_id' => $order_id]);
					$errand   = OrderErrand::findOne(['order_id' => $order_id]);
					if ($order && $orderFee && $errand) {


						$total_fee = self::getTotalFee($order->order_id);
						$total_fee += doubleval($orderFee->amount);

						//更新用户   订单总金额 = 服务单价 + 总小费
						$new_order_amount = doubleval($errand->service_price) * $errand->service_qty + $total_fee;
						$updateData       = [
							'total_fee'    => $total_fee,                //errand
							'status'       => Ref::PAY_STATUS_COMPLETE,    //fee
							'order_amount' => $new_order_amount,        //order
							'payment_id'   => $payment_id,        //order,fee
						];

						$orderFee->attributes = $updateData;
						$orderFee->save() ? $result = true : Yii::error("SmallFeePaySuccess fee save:" . json_encode($orderFee->getErrors()));

						$order->attributes = $updateData;

						if (!$order->save()) {
							Yii::error("SmallFeePaySuccess order save:" . json_encode($order->getErrors()));
							$result &= false;
						}

						$errand->attributes = $updateData;
						if (!$errand->save()) {
							Yii::error("SmallFeePaySuccess errand save:" . json_encode($errand->getErrors()));
							$result &= false;
						}
						$updateData = array_merge($updateData, ['小费表ID' => $orderFee->fee_id, '本次添加小费' => $orderFee->amount]);
						$result     &= self::saveLogContent($order_id, 'small_fee_pay_success', $updateData, '小费支付成功更改记录');//记录业务日志


						//支付成功后，需要冻结这笔费用
						if ($order->payment_id == Ref::PAYMENT_TYPE_BALANCE && $orderFee->amount > 0) {
							//冻结用户金额
							$result &= WalletHelper::frozenMoney($order->user_id, $orderFee->amount);
						}

						//用户收支明细表
						if ($orderFee->amount > 0) {

							$result &= WalletHelper::userIncomePay('2', '5', $order->user_id, $data['transaction_no'], $orderFee->amount, "小帮快送-支付小费");
						}


						if ($result) {

							$result = [
								'transaction_no' => $transaction_no,
								'fee'            => $orderFee->amount,
								'errand_status'  => $errand->errand_status,
							];
							$transaction->commit();
							ErrandDoHelper::pushToProviderNotice($order->order_no, self::PUSH_PROVIDER_SMALL_FEE, $result);

							if ($order->robbed == Ref::ORDER_ROB_NEW)    //加了小费未接单，自动再发单
							{
								QueueHelper::errandSendOrder($order->order_id);//重新派发订单
							}
						}
					}
				}
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}


		return $result;
	}

	//内容推送给用户
	public static function pushToUserNotice($order_no, $type, $params = [])
	{
		//1.小帮已接收订单 快送状态变更通知
		//2.平台自动取消订单通知
		//3.申请取消订单通知
		//4.添加小费
		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$pushData = [];
			$canPush  = true;
			if ($type == self::PUSH_USER_TYPE_TASK_PROGRESS) {//抢单和流程状态变更通知

				$inform = [
					Ref::ERRAND_STATUS_PICKED => '您的订单已经被小帮接受，请查看!',
					Ref::ERRAND_STATUS_DOING  => '您的订单正在进行中',
					Ref::ERRAND_STATUS_FINISH => '您的订单小帮已经完成',
				];

				$errand_status              = $params['errand_status'];
				$pushData['glx']            = Ref::GETUI_TYPE_GRAB_NOTICE;//个推类型
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_DO_WORKER_TASK;
				$pushData['inform_content'] = isset($inform[$errand_status]) ? $inform[$errand_status] : '您的订单已经被小帮接受，请查看!';
				$pushData['errand_status']  = $errand_status;

				isset($inform[$errand_status]) ? null : $canPush = false;    //联系客户不需要推送

				$params['current_page']      = 'task';
				$detail                      = self::userDetail($params);    //TODO 优化不需要多次查询
				$providerInfo                = UserHelper::getShopInfo($order->provider_id);
				$id_image                    = isset($providerInfo['shops_photo']) ? $providerInfo['shops_photo'] : 0;
				$pushData['provider_name']   = isset($providerInfo['shops_name']) ? $providerInfo['shops_name'] : "无忧帮帮";
				$pushData['provider_mobile'] = $order->provider_mobile;
				$pushData['provider_photo']  = ImageHelper::getUserPhoto($id_image);
				$pushData['task_time']       = date("m-d H:i"); //任务更新时间
				$pushData['order_data']      = $detail ? $detail : '';
			}

			if ($type == self::PUSH_USER_TYPE_CANCEL_PROGRESS) {    //申请取消流程

				$inform = [
					Ref::ERRAND_CANCEL_PROVIDER_APPLY    => '小帮申请取消订单',
					Ref::ERRAND_CANCEL_PROVIDER_AGREE    => '小帮同意取消订单!',
					Ref::ERRAND_CANCEL_PROVIDER_DISAGREE => '小帮不同意取消订单!',
					Ref::ERRAND_CANCEL_DEAL_NOTIFY       => isset($params['deal_msg']) ? $params['deal_msg'] : ErrandHelper::CANCEL_DEAL_NOTIFY_MSG,
				];

				$cancel_type  = $params['cancel_type'];
				$current_page = 'task';
				if ($cancel_type == Ref::ERRAND_CANCEL_USER_AGREE || $cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE)
					$current_page = 'cancel';

				$pushData['glx']               = Ref::GETUI_TYPE_CANCEL_NOTICE; //个推类型
				$pushData['to']                = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']               = PushHelper::ERRAND_DO_WORKER_CANCEL;
				$pushData['cancel_type']       = $cancel_type;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!';
				$pushData['current_page']      = $current_page;
				isset($inform[$cancel_type]) ? '' : $canPush = false;
			}

			//订单改派，推送给用户
			if ($type == self::PUSH_USER_TYPE_CHANGE_ORDER) {
				$pushData['glx']            = Ref::GETUI_TYPE_ASSIGN_ORDER_USER;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = self::pushToUserNoticeAssignOrderUrl($order->cate_id);
				$pushData['order_amount']   = $order->cate_id;
				$pushData['inform_content'] = "您好,您的订单已改派给其他商家";
			}

			$pushData['order_no']     = $order->order_no;
			$pushData['provider_id']  = $order->provider_id;
			$pushData['user_id']      = $order->user_id;
			$pushData['push_user_id'] = $order->user_id;
			$pushData['push_role']    = Ref::PUSH_ROLE_USER;
			$pushData['log_time']     = date("Y-m-d H:i:s");
			$canPush ? QueueHelper::toOneTransmissionForUser($order->user_id, $pushData, 'errand_do_to_user') : null;
		}

	}

	/**
	 * 获取订单指派给用户推送-URL
	 * @param $cate_id            订单分类ID
	 * @return bool|string
	 */
	public static function pushToUserNoticeAssignOrderUrl($cate_id)
	{
		$result = false;
		if ($cate_id == Ref::CATE_ID_FOR_BIZ_SEND) {
			$result = PushHelper::BIZ_SEND_ASSIGN_USER_NOTICE;
		}
		if ($cate_id == Ref::CATE_ID_FOR_ERRAND_BUY) {
			$result = PushHelper::ERRAND_BUY_ASSIGN_USER_NOTICE;
		}
		if ($cate_id == Ref::CATE_ID_FOR_ERRAND_SEND) {
			$result = PushHelper::ERRAND_SEND_ASSIGN_USER_NOTICE;
		}
		if ($cate_id == Ref::CATE_ID_FOR_ERRAND_DO) {
			$result = PushHelper::ERRAND_DO_ASSIGN_USER_NOTICE;
		}

		return $result;
	}

	/**
	 * 内容推送给小帮
	 *
	 * @param $order_no
	 * @param $type
	 * @param $params
	 */

	public static function pushToProviderNotice($order_no, $type, $params)
	{
		//1订单确认
		//2.申请取消订单通知
		//3.商品支付通知
		$order = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$pushData = [];
			$canPush  = true;
			if ($type == self::PUSH_PROVIDER_TYPE_CONFIRM) {    //订单确认

				$pushData['glx']            = Ref::GETUI_TYPE_USER_CONFIRM;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_DO_USER_CONFIRM;
				$pushData['inform_content'] = '您的订单已经确认完成';
			}

			if ($type == self::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS) {    //申请取消通知
				$inform = [
					Ref::ERRAND_CANCEL_USER_APPLY    => '用户申请取消订单!',
					Ref::ERRAND_CANCEL_USER_AGREE    => '用户同意取消订单!',
					Ref::ERRAND_CANCEL_USER_DISAGREE => '用户不同意取消订单!',
					Ref::ERRAND_CANCEL_DEAL_NOTIFY   => isset($params['deal_msg']) ? $params['deal_msg'] : self::CANCEL_DEAL_NOTIFY_MSG,
				];

				$cancel_type                   = $params['cancel_type'];
				$pushData['glx']               = Ref::GETUI_TYPE_CANCEL_NOTICE;
				$pushData['to']                = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']               = PushHelper::ERRAND_DO_USER_CANCEL;
				$pushData['request_cancel_id'] = isset($params['request_cancel_id']) ? $params['request_cancel_id'] : null;
				$pushData['inform_content']    = isset($inform[$cancel_type]) ? $inform[$cancel_type] : '您的订单已经被小帮接受，请查看!';
				$pushData['cancel_type']       = $cancel_type;
				isset($inform[$cancel_type]) ? null : $canPush = false;
			}

			if ($type == self::PUSH_PROVIDER_SMALL_FEE) {

				$pushData['glx']            = Ref::GETUI_TYPE_CUSTOM_FEE;
				$pushData['to']             = Ref::GETUI_TO_ERRAND_ORDER;
				$pushData['url']            = PushHelper::ERRAND_DO_FEE_NOTICE;
				$pushData['total_fee']      = self::getTotalFee($order->order_id);
				$pushData['fee']            = $params['fee'];
				$pushData['order_amount']   = $order->order_amount;
				$pushData['inform_content'] = str_replace("{fee}", $params['fee'], self::ADD_CUSTOM_FEE_MSG);
				$pushData['current_page']   = $params['errand_status'] == Ref::ERRAND_STATUS_FINISH ? 'detail' : 'task';
			}

			$pushData['order_no']     = $order->order_no;
			$pushData['provider_id']  = $order->provider_id;
			$pushData['user_id']      = $order->user_id;
			$pushData['push_user_id'] = $order->provider_id;
			$pushData['push_role']    = Ref::PUSH_ROLE_PROVIDER;
			$pushData['log_time']     = date("Y-m-d H:i:s");

			$canPush ? QueueHelper::toOneTransmissionForProvider($order->provider_id, $pushData, 'errand_do_to_provider') : null;

		}
	}
}

















