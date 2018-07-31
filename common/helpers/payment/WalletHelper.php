<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/26
 */

namespace common\helpers\payment;

use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\HelperBase;
use common\helpers\orders\CateListHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\models\activity\MarketProfit;
use common\models\activity\MarketRelation;
use common\models\insurance\InsuranceFee;
use common\models\orders\RechargeOrder;
use common\models\users\UserMarket;
use common\models\util\RechargeInfo;
use yii\base\Exception;
use yii\db\Query;
use Yii;
use yii\helpers\ArrayHelper;

class WalletHelper extends HelperBase
{

	const userTbl = 'bb_51_user';

	/**
	 * 扣减在线宝 在线宝业务 不再使用
	 *
	 * @param $user_id
	 * @param $fee
	 * @param $order_no
	 * @return bool
	 */
	public static function decreaseOnlineMoney($user_id, $fee, $order_no)
	{
		$result = false;
		$data   = (new Query())->select(" online_money,online_money_history_output ")->from("bb_51_user")->where(['uid' => $user_id])->one();
		if ($data) {
			$online_money                = $data['online_money'];
			$online_money_history_output = $data['online_money_history_output'];

			$amount = $online_money - $fee;
			if ($amount >= 0) {

				$up_online_money_history_output = $online_money_history_output + $fee;
				$sql                            = "update  " . self::userTbl . " set online_money=" . $amount . " ,";
				$sql                            .= " online_money_history_output=" . $up_online_money_history_output;
				$sql                            .= " where uid=" . $user_id;
				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("decreaseOnlineMoney failed");

				$result &= self::updateUserGain($user_id, $fee, $order_no, $amount);
			}
		}

		return $result;
	}

	/**
	 * 在线宝记录 在线宝业务 不再使用
	 *
	 * @param $uid
	 * @param $price
	 * @param $order_no
	 * @param $balance
	 * @return bool
	 */
	public static function updateUserGain($uid, $price, $order_no, $balance)
	{

		$result = false;
		$time   = time();
		$filed  = '(ou_uid,ou_prices,ou_symbol,ou_orderid,ou_balance_money,ou_create_time,ou_update_time,ou_status)';
		$sql    = "insert into bb_online_user_gain   {$filed} values ";
		$sql    .= "( '{$uid}','{$price}','-','{$order_no}','{$balance}','{$time}','{$time}',1 ) ;";
		$res    = Yii::$app->db->createCommand($sql)->execute();
		$res ? $result = true : Yii::error("插入在线宝明细记录!");

		return $result;
	}

	/**
	 * 用户添加余额
	 *
	 * @param $userId
	 * @param $fee
	 * @return bool
	 */
	public static function increaseUserBalance($userId, $fee)
	{
		$result = false;
		$data   = UserHelper::getUserInfo($userId, ['money']);
		if ($data) {
			$money   = $data['money'];
			$upMoney = bcadd($money, $fee, 3);
			$sql     = "update " . self::userTbl . " set money=" . $upMoney;
			$sql     .= " where uid=" . $userId;

			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("handleUserBalance", $sql);

			if ($upMoney < 0)
				Yii::$app->debug->pay_info("用户余额添加异常" . $userId, $upMoney);
		}

		return $result;
	}

	/**
	 * 扣减用户余额
	 *
	 * @param $userId
	 * @param $fee
	 * @return bool
	 */
	public static function decreaseUserBalance($userId, $fee)
	{
		$result = false;
		$data   = UserHelper::getUserInfo($userId, ['money', 'history_money']);
		if ($data) {
			$historyMoney   = $data['history_money'];
			$money          = $data['money'];
			$upMoney        = bcsub($money, $fee, 3);
			$upHistoryMoney = bcadd($historyMoney, $fee, 3);

			$sql = "update " . self::userTbl . " set money=" . $upMoney . " , history_money=" . $upHistoryMoney;
			$sql .= " where uid=" . $userId;

			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("handleUserBalance", $sql);

			if ($upMoney < 0)
				Yii::$app->debug->pay_info("用户余额扣减异常" . $userId, $upMoney);
		}

		return $result;
	}

	/**
	 * 检查余额是否足够支付
	 *
	 * @param $userId
	 * @param $fee
	 * @return bool
	 */
	public static function checkUserMoney($userId, $fee)
	{
		$result = false;
		$data   = UserHelper::getUserInfo($userId, ['money', 'freeze_money']);
		if ($data) {
			$money  = $data['money'];
			$amount = bcsub($money, $fee, 3);
			if ($amount >= 0) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * 冻结用户部分资金
	 *
	 * @param $userId
	 * @param $fee
	 * @return bool
	 */
	public static function frozenMoney($userId, $fee)
	{
		$result = false;
		$data   = UserHelper::getUserInfo($userId, ['money', 'freeze_money']);
		if ($data) {
			$money       = $data['money'];
			$freezeMoney = $data['freeze_money'];
			$amount      = bcsub($money, $fee, 3);
			if ($amount >= 0) {
				$upFreezeMoney = bcadd($freezeMoney, $fee, 3);
				$sql           = "update  " . self::userTbl . " set money=" . $amount . " , freeze_money=" . $upFreezeMoney;
				$sql           .= " where uid=" . $userId;
				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("frozenMoney_Failed", $sql);
			}
		}

		return $result;
	}


	/**
	 * 解冻用户部分资金
	 *
	 * @param $userId
	 * @param $fee
	 * @return bool
	 */
	public static function unFrozenMoney($userId, $fee)
	{
		$result = false;
		$data   = UserHelper::getUserInfo($userId, ['money', 'freeze_money']);
		if ($data) {
			$money       = $data['money'];
			$freezeMoney = $data['freeze_money'];
			$amount      = bcsub($freezeMoney, $fee, 3);
			if ($amount >= 0) {
				$upMoney = bcadd($money, $fee, 3);
				$sql     = "update  " . self::userTbl . " set money=" . $upMoney . " , freeze_money=" . $amount;
				$sql     .= " where uid=" . $userId;

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("unFrozenMoney_Failed", $sql);
			}
		}

		return $result;

	}

	/**
	 * 用户收支明细
	 *
	 * @param int $type
	 * @param int $type2
	 * @param     $uid
	 * @param     $tranasction_no
	 * @param     $amount
	 * @param     $title
	 * @return bool
	 */
	public static function userIncomePay($type = 1, $type2 = 5, $uid, $tranasction_no, $amount, $title)
	{
		$result = false;
		$time   = time();
		$filed  = '(uid,did,type,type2,money,balance,title,create_time,update_time,status)';
		$sql    = "insert into bb_51_income_pay   {$filed} values ";
		$sql    .= "( '{$uid}','{$tranasction_no}','{$type}','{$type2}','{$amount}','{$amount}','{$title}','{$time}','{$time}',1 ) ;";

		Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("userIncomePay_Failed", $sql);

		return $result;
	}

	//用户扣减金额
	public static function userAcceptPackage($user_id, $fee)
	{
		$result  = false;
		$userTbl = self::userTbl;
		$data    = (new Query())->select(" money,history_money")
			->from($userTbl)->where(['uid' => $user_id])->one();
		if ($data) {
			$money        = $data['money'];
			$total_amount = bcadd($money, $fee, 2);

			$sql    = "update  `{$userTbl}` set `money` = '{$total_amount}' where uid = {$user_id}";
			$update = Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("userAcceptPackage");

			if ($update) {
				//生成资金记录
				$result &= self::userIncomePay(1, 1, $user_id, 0, $fee, "活动奖金");
			}
		}

		return $result;
	}

	/**
	 * 冻结金额扣减正式消费
	 *
	 * @param     $userId
	 * @param     $fee
	 * @param int $pay_online_money
	 * @return bool
	 */
	public static function handleUserBalance($userId, $fee, $pay_online_money = 0)
	{
		$result = false;
		$data   = UserHelper::getUserInfo($userId, ['history_money', 'freeze_money']);
		if ($data) {
			$historyMoney   = $data['history_money'];
			$freezeMoney    = $data['freeze_money'];
			$amountFreeze   = bcsub($freezeMoney, $fee, 3);
			$upHistoryMoney = bcadd($historyMoney, $fee, 3);

			$sql = "update  " . self::userTbl . " set freeze_money=" . $amountFreeze . " , history_money=" . $upHistoryMoney;
			$sql .= " where uid=" . $userId;

			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("handleUserBalance_Failed", $sql);

			if ($amountFreeze < 0)
				Yii::$app->debug->pay_info("用户冻结资金异常" . $userId, $amountFreeze);
		}

		return $result;
	}

	/**
	 * 平台资金池扣减折扣
	 *
	 * @param $discount
	 * @param $providerId
	 * @param $userId
	 * @return bool
	 */
	public static function handleCenterDiscount($discount, $providerId, $userId)
	{
		//更新主表记录
		$result = false;
		$data   = (new Query())->select(" admin_money ")
			->from("bb_ucenter_member")->where(['id' => 1])->one();
		if ($data) {
			$adminMoney = $data['admin_money'];
			$adminMoney = bcsub($adminMoney, $discount, 3);
			$sql        = "update  bb_ucenter_member  set admin_money=" . $adminMoney . " where id=1";
			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("handleCenterDiscount_Failed", $sql);

			$time  = time();
			$field = '(adminid,re_lx,`in`,re_uid,re_shopsid,re_val,new_money,re_content,re_time,agent_id)';
			$sql   = "insert into bb_ucenter_member_record   {$field} values ";
			$sql   .= "(1,5,2,'{$userId}','{$providerId}','{$discount}','{$adminMoney}','小帮快送-订单优惠','{$time}',0 ) ;";
			Yii::$app->db->createCommand($sql)->execute() ? $result &= true : Yii::$app->debug->pay_info("handleCenterDiscountDetail_Failed", $sql);
		}

		return $result;
	}

	/**
	 * 小帮添加余额
	 *
	 * @param $providerId
	 * @param $amount
	 * @return bool
	 */
	public static function handleShopBalance($providerId, $amount)
	{
		$result = false;
		$data   = UserHelper::getShopInfo($providerId, ['shops_money', 'shops_historymoney']);
		if ($data) {
			$shopsMoney          = $data['shops_money'];
			$shopsHistoryMoney   = $data['shops_historymoney'];
			$upShopsMoney        = bcadd($shopsMoney, $amount, 3);
			$upShopsHistoryMoney = bcadd($shopsHistoryMoney, $amount, 3);

			$sql = "update  bb_51_shops set shops_money=" . $upShopsMoney . " , shops_historymoney=" . $upShopsHistoryMoney;
			$sql .= " where id=" . $providerId;
			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("handleShopBalance_Failed", $sql);
		}

		return $result;
	}

	/**
	 * 扣小帮余额
	 *
	 * @param $provider_id
	 * @param $amount
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public static function decreaseShopBalance($provider_id, $amount)
	{
		$result = false;
		$data   = (new Query())->select(" shops_money,shops_outmoney ")
			->from("bb_51_shops")->where(['id' => $provider_id])->one();

		if ($data) {
			$shopsMoney   = $data['shops_money'];
			$upShopsMoney = bcsub($shopsMoney, $amount, 2);    //TODO 扣余额 需要有一个累计扣款

			$sql = "update  bb_51_shops set shops_money=" . $upShopsMoney;
			$sql .= " where id=" . $provider_id;
			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("handleUserBalance");

		}

		return $result;
	}

	/**
	 * 小帮收支明细表
	 *
	 * @param     $providerId
	 * @param     $staffuid
	 * @param     $orderNo
	 * @param     $amount
	 * @param     $title
	 * @param int $type
	 * @param int $account_type
	 * @return bool
	 */
	public static function handleIncomeShop($providerId, $staffuid, $orderNo, $amount, $title, $type = 1, $account_type = 1, $balance = null)
	{
		$result = false;
		$time   = time();
		$field  = '(shops_id,staffuid,order_id,type,account_type,money,balance,title,create_time,update_time,status)';
		$sql    = "insert into bb_51_income_shop   {$field} values ";
		if (!$balance) {
			$balance = $amount;    //TODO 之前的调用不会计算统计账号余额
		}
		$sql .= "('{$providerId}','{$staffuid}','{$orderNo}','{$type}','{$account_type}','{$amount}','{$balance}','{$title}','{$time}','{$time}',1 ) ;";

		Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->pay_info("商家流水明细新增失败", $sql);

		return $result;
	}

	/**
	 * 订单抽佣
	 *
	 * @param $orderData
	 * @param $provider_amount
	 * @return string
	 */
	public static function takeMoney($orderData, $provider_amount)
	{
		$provider_id = $orderData['provider_id'];
		$cate_id     = $orderData['cate_id'];
		$city_id     = $orderData['city_id'];
		$area_id     = $orderData['area_id'];

		$city_price = RegionHelper::getTakeMoneyRate($city_id, $area_id, $cate_id);
		if ($city_price) {

			$shopInfo      = UserHelper::getShopInfo($provider_id);
			$platformMoney = 0;
			$agentId       = 0;    //agentID
			if (isset($shopInfo['job_time']) && $shopInfo['job_time'] == 1) {
				$takeRate  = $city_price['full_time_take'];
				$takeMoney = number_format($provider_amount * $takeRate, 2);
			} else {
				$takeRate  = $city_price['part_time_take'];
				$takeMoney = number_format($provider_amount * $takeRate, 2);
			}

			$provider_amount = bcsub($provider_amount, $takeMoney, 2);
			if ($takeMoney > 0) {
				$regionArr = ['city_id' => $city_id, 'area_id' => $area_id];
				$agentFee  = self::getAgentFeeData($regionArr);                     //获取订单地址的费率
				$agentFee ? null : $agentFee = self::getAgentFeeData($shopInfo);    //获取小帮地址的费率
				$platformMoney = $takeMoney;//代理商不设置分佣，默认全部归平台
				if ($agentFee) {

					if ($agentFee['platform_taken'] > 0 || $agentFee['parent_agent_taken'] > 0 || $agentFee['agent_taken'] > 0) {

						$platformMoney = bcmul($takeMoney, $agentFee['platform_taken'], 2);        //平台
						$parentMoney   = bcmul($takeMoney, $agentFee['parent_agent_taken'], 2);    //上级
						$agentMoney    = bcmul($takeMoney, $agentFee['agent_taken'], 2);           //本代理

						if ($parentMoney > 0) {
							$saveParent = self::saveAgentMoney($parentMoney, $orderData, $agentFee['parent_agent_id']);
							$saveParent ? "" : $platformMoney = bcadd($platformMoney, $parentMoney, 2);
						}

						if ($agentMoney > 0) {
							$saveCurrent = self::saveAgentMoney($agentMoney, $orderData, $agentFee['agent_id']);
							$saveCurrent ? "" : $platformMoney = bcadd($platformMoney, $agentMoney, 2);    //失败资金归平台
							$agentId = $agentFee['agent_id'];
						}
					}
				}
			}

			if ($platformMoney > 0) {
				self::savePlatformMoney($platformMoney, $orderData);
			}

			//营销计算 2018年6月20日 启用全民合伙人

			QueueHelper::marketProfit($orderData, $agentId);

		}

		return $provider_amount;
	}

	/**
	 * 获取代理所在城市抽佣比例
	 *
	 * @param $regionArr
	 *
	 * @return array|string
	 */
	public static function getAgentFeeData($regionArr)
	{

		$result  = false;
		$area_id = $regionArr['area_id'];
		$city_id = $regionArr['city_id'];

		$areaAgentFee = (new Query())->from('wy_agent_fee_rel')->select('*')->where(['region_id' => $area_id])->one();
		if ($areaAgentFee) {
			$result = [
				'platform_taken'     => $areaAgentFee['platform_taken'],
				'parent_agent_taken' => $areaAgentFee['parent_agent_taken'],
				'agent_taken'        => $areaAgentFee['agent_taken'],
				'parent_agent_id'    => $areaAgentFee['parent_agent_id'],
				'agent_id'           => $areaAgentFee['agent_id'],
				'region_id'          => $areaAgentFee['region_id']
			];
		} else {
			$cityAgentFee = (new Query())->from('wy_agent_fee_rel')->select('*')->where(['region_id' => $city_id])->one();
			if ($cityAgentFee) {
				$result = [
					'platform_taken'     => $cityAgentFee['platform_taken'],
					'parent_agent_taken' => $cityAgentFee['parent_agent_taken'],
					'agent_taken'        => $cityAgentFee['agent_taken'],
					'parent_agent_id'    => $cityAgentFee['parent_agent_id'],
					'agent_id'           => $cityAgentFee['agent_id'],
					'region_id'          => $cityAgentFee['region_id']
				];
			}
		}

		if ($result) {    //重新检测一次

			$agentData = (new Query())->from('bb_51_agent')->select('agent_money')->where(['agent_id' => $result['agent_id'], 'status' => 1])->one();
			$agentData ? '' : $result = false;
		}

		return $result;
	}

	/**
	 * 保存代理商费率
	 *
	 * @param $money
	 * @param $order
	 * @param $agent_id
	 */
	public static function saveAgentMoney($money, $order, $agent_id)
	{
		$result    = false;
		$agentData = (new Query())->from('bb_51_agent')->select('agent_money')->where(['agent_id' => $agent_id, 'status' => 1])->one();
		if ($agentData) {
			$sql    = "UPDATE bb_51_agent SET agent_money=agent_money+{$money} WHERE agent_id={$agent_id}";
			$result = Yii::$app->db->createCommand($sql)->execute();
			$time   = time();
			$label  = CateListHelper::getWyCateName($order['cate_id']);
			$remark = $label . '订单收入' . $money . '元';
			if ($result) {
				$new_money = $agentData['agent_money'] + $money;
				$field     = "(agent_id,cate_id,shops_id,amount,add_time,remark,new_money)";
				$insertSql = "INSERT INTO bb_51_agent_account " . $field . " VALUES('{$agent_id}','{$order['cate_id']}','{$order['provider_id']}','{$money}','{$time}','{$remark}','{$new_money}')";
				$result    = Yii::$app->db->createCommand($insertSql)->execute();
			}
		}

		return $result;
	}

	public static function savePlatformMoney($money, $order)
	{
		$adminData = (new Query())->from('bb_ucenter_member')->select('admin_money')->where(['id' => 1])->one();
		if ($adminData) {

			$sql    = "UPDATE bb_ucenter_member SET admin_money=admin_money+{$money} WHERE id=1";
			$result = Yii::$app->db->createCommand($sql)->execute();
			if ($result) {
				$label      = CateListHelper::getWyCateName($order['cate_id']);
				$new_money  = $adminData['admin_money'] + $money;
				$re_content = $label . '订单收入' . $money . '元';
				$re_time    = time();
				$field      = "(adminid,re_lx,`in`,re_uid,re_shopsid,re_val,new_money,re_content,re_time)";
				$insertSql  = "INSERT INTO bb_ucenter_member_record " . $field . " VALUE(1,5,1,{$order['user_id']},{$order['provider_id']},{$money},{$new_money},'{$re_content}',{$re_time})";

				Yii::$app->db->createCommand($insertSql)->execute();
			}
		}
	}

	/**
	 * 创建订单
	 *
	 * @param $params
	 * @return array|bool
	 */
	public static function createRechargeOrder($params)
	{

		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$rechargeOrder                  = new RechargeOrder();
			$rechargeOrder->recharge_no     = date('ymdHis') . rand(10, 99);
			$rechargeOrder->create_time     = time();
			$rechargeOrder->recharge_status = Ref::PAY_STATUS_WAIT;
			$rechargeOrder->attributes      = $params;
			$rechargeOrder->save() ? $result = true : Yii::$app->debug->log_info("create_rechargeOrder", $rechargeOrder->getErrors());

			$tradeParams = [
				'payment_id' => $params['payment_id'],
				'type'       => Ref::TRANSACTION_TYPE_RECHARGE,
			];

			$tradeRes = TransactionHelper::createTrade($rechargeOrder->id, $rechargeOrder->amount_payable, $tradeParams);
			$result   &= $tradeRes;

			if ($result) {
				$result = $tradeRes;
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 优惠券充值
	 * @param $params
	 * @return array|bool
	 */
	public static function getRechargeParamsForPayment($params)
	{
		$result       = false;
		$rechargeInfo = RechargeInfo::findOne(['id' => $params['recharge_info_id'], 'status' => 1]);
		if ($rechargeInfo) {

			$order_params = [
				'user_id'          => $params['user_id'],
				'recharge_from'    => $params['recharge_from'],
				'recharge_info_id' => $params['recharge_info_id'],
				'payment_id'       => $params['payment_id'],
				'card_id'          => $rechargeInfo->card_id,
				'order_amount'     => $rechargeInfo->amount_payable,
				'amount_payable'   => $rechargeInfo->amount_payable,
				'get_amount'       => $rechargeInfo->get_amount,
				'type'             => $rechargeInfo->type
			];
			$result       = self::createRechargeOrder($order_params);
			$result ? $result['success_content'] = $rechargeInfo->remark : null;
		}

		return $result;
	}

	/**
	 * 充值首页
	 * @param $user_id
	 * @param $type
	 * @return mixed
	 */
	public static function rechargeIndex($user_id, $type)
	{
		$list         = [];
		$user_info    = UserHelper::getUserInfo($user_id, ['money', 'city_id']);
		$rechargeInfo = RechargeInfo::find()->where(['status' => 1, 'city_id' => $user_info['city_id'], 'type' => $type])->orderBy(['place_no' => SORT_ASC])->asArray()->all();

		if (!$rechargeInfo) {
			$rechargeInfo = RechargeInfo::find()->where(['status' => 1, 'city_id' => 0, 'type' => $type])->orderBy(['place_no' => SORT_ASC])->asArray()->all();//city_id=0 是全国的充值信息
		}
		foreach ($rechargeInfo as $key => $item) {
			$desc    = $item['description'];
			$cardId  = $item['card_id'];
			$content = "充值" . $item['amount_payable'] . "," . $desc;

			if ($cardId > 0) {
				$cardInfo = (new Query())->from("bb_card")->select(['name', 'instruction'])->where(['id' => $cardId, 'status' => 1])->one();
				$cardInfo ? $content = $cardInfo['instruction'] : null;
			}

			$list[$key]['recharge_id'] = $item['id'];
			$list[$key]['amount']      = (int)$item['amount_payable'];
			$list[$key]['discount']    = $desc;
			$list[$key]['content']     = $content;
		}

		$result['user_money'] = $user_info['money'];
		$result['list']       = $list;

		return $result;
	}

	/**
	 * 余额充值支付回调
	 * @param      $transaction_no
	 * @param      $trade_no
	 * @param      $fee
	 * @param null $remark
	 * @param null $data
	 * @param      $payment_id
	 * @return array|bool|int
	 */
	public static function rechargePaySuccess($transaction_no, $trade_no, $fee, $remark = null, $data = null, $payment_id)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$tradeData = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);
			if ($tradeData) {

				$rechargeOrder = RechargeOrder::findOne(['id' => $tradeData['ids_ref']]);
				if ($rechargeOrder) {
					$userId = $rechargeOrder->user_id;
					//1、用户余额
					$sql    = "UPDATE bb_51_user SET money = money+" . $rechargeOrder->get_amount . " WHERE uid = " . $userId;
					$result = Yii::$app->db->createCommand($sql)->execute();

					//2、更新卡券
					if ($rechargeOrder->card_id) {
						$card_info = (new Query())->from("bb_card")->select(['price', 'get_num', 'extend', 'effective_time', 'end_time', 'user_limit'])->where(['id' => $rechargeOrder->card_id, 'status' => 1])->one();
						if ($card_info) {    //使用卡券

							$end_time = $card_info['end_time'];
							if ($card_info['effective_time'] > 0) {
								$end_time = time() + $card_info['effective_time'];
							}
							for ($i = 1; $i <= $card_info['user_limit']; $i++) {
								$card_insert = [
									'uid'      => $userId,
									'c_id'     => $rechargeOrder->card_id,
									'price'    => $card_info['price'],
									'extend'   => $card_info['extend'],
									'get_time' => time(),
									'end_time' => $end_time,
									'way'      => $payment_id,
								];
								$result      &= Yii::$app->db->createCommand()->insert("bb_card_user", $card_insert)->execute();
							}
						}

					}

					//3、更新用户流水
					$result &= WalletHelper::userIncomePay(1, 3, $userId, $transaction_no, $rechargeOrder->get_amount, '余额充值');

					//4、更新自身表
					$rechargeOrder->recharge_status = Ref::PAY_STATUS_COMPLETE;
					$rechargeOrder->update_time     = time();
					$result                         &= $rechargeOrder->save();

				}
			}

			if ($result) {
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 保险首扣业务
	 *
	 * @param $orderData
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public static function takeInsuranceFee($orderData)
	{
		//开发思路
		//1、订单时间和保险扣费表对比时间
		//1.1、若没数据增加一条记录
		//2、扣小帮2块钱
		//2.1 小帮收支明细

		$result     = true;
		$createTime = $orderData['create_time'];
		$providerId = $orderData['provider_id'];
		$orderNo    = $orderData['order_no'];
		$startTime  = strtotime(date("Y-m-d 00:00:00", $createTime));
		$endTime    = $startTime + 86400;

		if ($createTime < 1522684800) {//2018-4-3 00:00:00之前，不扣保险费
			return $result;
		}
		$data = InsuranceFee::find()->where(['provider_id' => $providerId])
			->andFilterWhere(['>', 'order_time', $startTime])
			->andFilterWhere(['<=', 'order_time', $endTime])->one();

		if (!$data) {
			$fee            = Ref::INSURANCE_FEE;
			$shop           = UserHelper::getShopInfo($providerId, ['uid', 'shops_money']);
			$providerUserId = isset($shop['uid']) ? $shop['uid'] : 0;
			$shopsMoney     = isset($shop['shops_money']) ? $shop['shops_money'] : 0;
			$balance        = bcsub($shopsMoney, $fee, 3);
			if ($balance < 0) {        //账户余额不够抵扣保险费用，本次不扣

				return $result;
			}

			$model              = new InsuranceFee();
			$model->provider_id = $providerId;
			$model->order_time  = $createTime;
			$model->create_time = time();
			$model->fee         = $fee;
			$result             = $model->save();
			if ($result) {

				//2、扣小帮2块钱
				$result &= WalletHelper::decreaseShopBalance($providerId, $fee);

				//2.1 小帮收支明细 抵扣2块钱
				$result &= WalletHelper::handleIncomeShop($providerId, $providerUserId, $orderNo, Ref::INSURANCE_FEE,
					date("Y-m-d", $createTime) . "缴纳保险" . $fee . "元", Ref::PROVIDER_BALANCE_INSURANCE, Ref::BALANCE_TYPE_OUT, $balance);

				//TODO 后续再计入平台收入
			}
		}

		return $result;
	}

	//获取账户余额
	public static function getBalance($user_id)
	{
		$balance = UserHelper::getUserInfo($user_id, 'money as balance');

		return $balance;
	}

	/**
	 * 队列中处理营销数据的方法
	 * @param $orderData
	 * @param $agentId
	 * @return bool|int
	 * @throws \yii\db\Exception
	 */
	public static function marketProfit($orderData, $agentId)
	{
		//开发思路
		//1、查找用户的邀请人
		//1.1、插入用户的收益记录
		//1.2、插入用户的红包收益记录
		//2、查找小帮的邀请人
		//2.1、插入小帮的收益记录
		//2.2、插入小帮的红包收益记录
		//3、总费用更新到代理商营销费用表
		$result     = true;
		$rate       = 0.014;    //统一营销率是1.4%
		$market_fee = 0;        //营销费用
		$amount     = $orderData['order_amount'];
		$insertData = [
			'profit_from' => Ref::PROFIT_FROM_MAID,
			'received'	  => Ref::GIFT_UNRECEIVED,
			'city_id'     => $orderData['city_id'],
			'area_id'     => $orderData['area_id'],
			'agent_id'    => $agentId,
			'create_time' => time(),
			'send_time' => null,
			'order_id'    => $orderData['order_id']
		];
		$transaction = Yii::$app->db->beginTransaction();
		try {

			$userParams['user_id'] = $orderData['user_id'];
			$userParams['type']    = Ref::SYSTEM_USER;
			$userMarkerData        = self::getMarketRelationData($userParams);
			if ($userMarkerData) {
				$user_amount                  = bcmul($amount, $rate, 3);
				$insertData['user_id']        = $orderData['user_id'];
				$insertData['market_user_id'] = $userMarkerData['market_user_id'];
				$insertData['amount']         = $user_amount;
				$insertData['type']           = Ref::SYSTEM_USER;

				if ($user_amount > 0) {
					$result     = self::saveMarketProfit($insertData, $userMarkerData['id']);
					$market_fee += $user_amount;

					$checkActivity = ActivityHelper::checkActivityForMarket($orderData);
					if ($checkActivity) {
						$insertData['create_time'] = null;
						$insertData['send_time']   = time();
						$insertData['profit_from'] = Ref::PROFIT_FROM_GIFT;
						$insertData['amount']      = 0;
						$insertData['agent_id']    = 0;
						$result                    &= self::saveGiftMarketProfit($insertData);
					}
				}
			}

			$providerParams['provider_id'] = $orderData['provider_id'];
			$providerParams['type']        = Ref::SYSTEM_PROVIDER;
			$providerMarkerData            = self::getMarketRelationData($providerParams);
			if ($providerMarkerData) {
				$provider_amount              = bcmul($amount, $rate, 3);
				$insertData['create_time']    = time();
				$insertData['send_time']      = null;
				$insertData['profit_from']    = Ref::PROFIT_FROM_MAID;
				$insertData['agent_id']       = $agentId;
				$insertData['user_id']        = $providerMarkerData['user_id'];
				$insertData['market_user_id'] = $providerMarkerData['market_user_id'];
				$insertData['amount']         = $provider_amount;
				$insertData['type']           = Ref::SYSTEM_PROVIDER;

				if ($provider_amount > 0) {
					$result     = self::saveMarketProfit($insertData, $providerMarkerData['id']);
					$market_fee += $provider_amount;

					$checkActivity = ActivityHelper::checkActivityForMarket($orderData);
					if ($checkActivity) {
						$insertData['create_time'] = null;
						$insertData['send_time']   = time();
						$insertData['profit_from'] = Ref::PROFIT_FROM_GIFT;
						$insertData['amount']      = 0;
						$insertData['agent_id']    = 0;
						$result                    &= self::saveGiftMarketProfit($insertData);
					}
				}
			}

			//更新代理费用
			if ($market_fee > 0 && $agentId > 0) {
				$agentData = (new Query())->from('bb_51_agent')->select('agent_money')->where(['agent_id' => $agentId])->one();
				if ($agentData) {
					$sql    = "UPDATE bb_51_agent SET market_fee=market_fee+{$market_fee} WHERE agent_id={$agentId}";
					$result = Yii::$app->db->createCommand($sql)->execute();
				}
			}

			if ($result) {
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}


	/**
	 * 获取关系数据
	 * @param $where
	 * @return array|bool
	 */
	public static function getMarketRelationData($where)
	{
		$result          = false;
		$where['status'] = 1;    //正常可用
		$model           = MarketRelation::findOne($where);
		if ($model) {
			$result = ArrayHelper::toArray($model);
		}

		return $result;
	}

	/**
	 * 保存收益记录
	 * @param $params
	 * @param $relationId
	 * @return bool
	 */
	public static function saveMarketProfit($params, $relationId)
	{
		$result = true;
		//营销号需要在启用中
		$userMarket = UserMarket::findOne(['user_id' => $params['market_user_id'], 'status' => 1]);    //正常情况下才能收入
		if ($userMarket) {
			$mp             = new MarketProfit();
			$mp->attributes = $params;
			$mp->save();
			$result        = $mp->save();
			$relationModel = MarketRelation::findOne(['id' => $relationId, 'type' => $params['type']]);
			if ($relationModel) {

				$relationModel->sum_amount  += $params['amount'];
				$relationModel->update_time = time();
				$result                     &= $relationModel->save();
			}

			$userMarket->earn_amount += $params['amount'];
			$userMarket->update_time = time();
			$result                  &= $userMarket->save();
		}

		return $result;
	}

	/**
	 * 保存全民合伙人收益
	 * @param $params
	 * @param $relationId
	 * @return bool
	 */
	public static function saveMarketAmount($params)
	{
		$result     = true;
		$userMarket = UserMarket::findOne(['user_id' => $params['market_user_id'], 'status' => 1]);
		if ($userMarket) {
			$userMarket->earn_amount += $params['amount'];
			$userMarket->update_time = time();
			$result                  &= $userMarket->save();

			$relationModel = MarketRelation::findOne(['user_id' => $params['user_id'], 'market_user_id' => $params['market_user_id'], 'type' => $params['type']]);
			if ($relationModel) {
				$relationModel->sum_amount  += $params['amount'];
				$relationModel->update_time = time();
				$result                     &= $relationModel->save();
			}
		}

		return $result;
	}


	/**
	 * 保存红包收益记录
	 * @param $params
	 * @return bool
	 */
	public static function saveGiftMarketProfit($params)
	{
		$result = true;
		//营销号需要在启用中
		$userMarket = UserMarket::findOne(['user_id' => $params['market_user_id'], 'status' => 1]);    //正常情况下才能收入
		if ($userMarket) {
			$mp             = new MarketProfit();
			$mp->attributes = $params;
			$mp->save();
			$result = $mp->save();
		}

		return $result;
	}

	/**
	 * 获取小帮获得的抽佣金额
	 *
	 * @param $orderData
	 * @param $provider_amount
	 * @return string
	 */
	public static function getProviderTakeMoney($orderData, $provider_amount)
	{
		$provider_id = $orderData['provider_id'];
		$cate_id     = $orderData['cate_id'];
		$city_id     = $orderData['city_id'];
		$area_id     = $orderData['area_id'];

		$city_price = RegionHelper::getTakeMoneyRate($city_id, $area_id, $cate_id);
		if ($city_price) {
			$shopInfo      = UserHelper::getShopInfo($provider_id);
			if (isset($shopInfo['job_time']) && $shopInfo['job_time'] == 1) {
				$takeRate  = $city_price['full_time_take'];
				$takeMoney = bcmul($provider_amount,$takeRate,2);
			} else {
				$takeRate  = $city_price['part_time_take'];
				$takeMoney = bcmul($provider_amount,$takeRate,2);
			}
			$provider_amount = bcsub($provider_amount, $takeMoney, 2);
		}

		return $provider_amount;
	}
}
