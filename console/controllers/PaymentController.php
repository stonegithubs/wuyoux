<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/10/02 13:01
 */

namespace console\controllers;

use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\WxpayHelper;
use common\models\payment\Transaction;
use yii\console\Controller;
use yii\db\Query;
use Yii;

class PaymentController extends Controller
{

	//检查退款问题
	public function actionCheckRefundAll()
	{
		//1、查出所有未处理的订单记录
		$nowTime = time() - 1800;    //半小时内退款
		$trade   = (new Query())->from(Transaction::tableName())->where(['status' => Ref::PAY_STATUS_WAIT]);

		$trade->andWhere(['in', 'type', [Ref::TRANSACTION_TYPE_REFUND, Ref::TRANSACTION_TYPE_TIPS_REFUND]])
			->andWhere(['>', 'create_time', $nowTime])->orderBy(['create_time' => SORT_DESC])->limit(10);

		$tradeData = $trade->all();
		if ($tradeData) {
			$params['trade'] = $tradeData;
			OrderHelper::preRefund($params);
		}
	}

	//冻结金额是负数的更改
	public function actionFixFreezeMoney()
	{
		$data = (new Query())->select("mobile, uid, freeze_money,history_money")
			->from("bb_51_user")->where(['<', 'freeze_money', 0])->all();

		if ($data) {
			$i = 0;
			foreach ($data as $item) {
				$amount_freeze = abs($item['freeze_money']);
				$user_id       = $item['uid'];
				$sql           = "update bb_51_user  set freeze_money=" . $amount_freeze;
				$sql           .= " where uid=" . $user_id;

				if (Yii::$app->db->createCommand($sql)->execute()) {

					echo "号码" . $item['mobile'] . "冻结金额由" . $item['freeze_money'] . "更新为" . $amount_freeze . "\r\n";

				} else {
					echo "号码" . $item['mobile'] . "冻结金额更新失败\r\n";
				}
				$i++;
			}

			echo "成功更新" . $i . "条记录\r\n";
		}
	}

	//检查异常的财务数据
	public function actionCheckAbnormal()
	{
		$result     = false;
		$end_time   = time();
		$start_time = $end_time - 60 * 60 * 12;
		$sql        = "SELECT sum(money) as sum_money,money ,count('id') AS order_count,order_id FROM bb_51_income_shop WHERE type = 1 AND account_type =1 AND LENGTH(order_id) = 14 AND create_time > " . $start_time . " AND create_time < " . $end_time . " and title not like '活动奖金' group by order_id HAVING order_count > 1";
		$abnormal   = Yii::$app->db->createCommand($sql)->queryAll();
		if ($abnormal) {
			$content = '<table><tr><th>总额</th><th>金额</th><th>数量</th><th>订单号</th></tr>';
			foreach ($abnormal as $key => $value) {
				$content .= "<tr><td>" . $value['sum_money'] . "</td>" . "<td>" . $value['money'] . "</td>" . "<td>" . $value['order_count'] . "</td>" . "<td>" . $value['order_id'] . "</td></tr>";
			}
			$content .= "</table>";

			$message = [];
			$users   = [ 'hyd@51bangbang.com.cn', 'lth@51bangbang.com.cn'];
			foreach ($users as $user) {
				$message[] = Yii::$app->mailer->compose()
					->setTo($user)
					->setSubject("财务异常")
					->setHtmlBody($content);
			}

			$result = Yii::$app->mailer->sendMultiple($message);
		}
		var_dump($result);
	}
}