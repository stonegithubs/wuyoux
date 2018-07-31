<?php

use yii\db\Migration;

class m180207_091509_update_bb_shop_remark_log extends Migration
{
	public function safeUp()
	{
		$sql = "ALTER TABLE `bb_shops_remark_log` ADD `admin_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:后台管理员；2：代理商' AFTER `shops_id`;";
		$this->db->createCommand($sql)->execute();
	}

	public function safeDown()
	{
		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180207_091509_update_bb_shop_remark_log cannot be reverted.\n";

		return false;
	}
	*/
}
