<?php

use yii\db\Migration;

class m171206_101640_order_log extends Migration
{
	public function safeUp()
	{
		$sql
			= "ALTER TABLE `wy_order_log`
CHANGE `log_value` `log_value` text COLLATE 'utf8_unicode_ci' NULL COMMENT '日志value' AFTER `log_key`,
CHANGE `remark` `remark` text COLLATE 'utf8_unicode_ci' NULL COMMENT '备注' AFTER `log_value`;";
		$this->db->createCommand($sql)->execute();
	}

	public function safeDown()
	{
		echo "m171206_101640_order_log cannot be reverted.\n";

		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171206_101640_order_log cannot be reverted.\n";

		return false;
	}
	*/
}
