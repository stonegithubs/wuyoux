<?php

use yii\db\Migration;

/**
 * Class m180331_061436_create_wy_insurance_fee
 */
class m180331_061436_create_wy_insurance_fee extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="保险费用记录表"';
		}
		//订单自动确认检测
		$this->createTable('{{%insurance_fee}}', [
			'id'          => $this->primaryKey()->comment('主表ID'),
			'provider_id' => $this->integer(10)->comment('小帮ID'),
			'fee'         => $this->money(10, 2)->comment("费用"),
			'order_time'  => $this->integer(10)->comment('下单时间'),
			'create_time' => $this->integer(10)->comment('创建时间'),
		], $tableOptions);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%insurance_fee}}');
		echo "m180331_061436_create_wy_insurance_fee cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180331_061436_create_wy_insurance_fee cannot be reverted.\n";

		return false;
	}
	*/
}
