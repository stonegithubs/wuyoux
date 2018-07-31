<?php

use yii\db\Migration;

/**
 * Class m180605_075411_update_bb_51_income_pay
 */
class m180605_075411_update_bb_51_income_pay extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$sql = "ALTER TABLE bb_51_income_pay modify column type2 int(2) comment '属性类型 0未知 1红包收入 2话费充值 3余额充值 4待定 5订单支付 6旧订单退款 7新订单退款 8平台回收 9营销收入 10捐款 11优惠券';";
		$this->db->createCommand($sql)->execute();


		$sql = "ALTER TABLE bb_51_income_shop modify column type int(2) comment '属性类型 0未知 1收款 2提现 3充值 4后台回收 5红包 6红包退回 7解冻保证金 8缴纳保证金 9在线宝转余额 10余额支付保证金 11保险扣费 12营销';";
		$this->db->createCommand($sql)->execute();

		$sql
			= "ALTER TABLE `bb_51_agent`
ADD `market_fee` decimal(10,3) NULL DEFAULT '0' COMMENT '营销费用';";
		$this->db->createCommand($sql)->execute();


	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		//echo "m180605_075411_update_bb_51_income_pay cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180605_075411_update_bb_51_income_pay cannot be reverted.\n";

		return false;
	}
	*/
}
