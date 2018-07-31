<?php

use yii\db\Migration;

class m171025_163020_order_log extends Migration
{


	public function safeUp()
	{

		$sql = "ALTER TABLE `wy_order_log` ADD `create_user_id` int(10) NULL DEFAULT '0' COMMENT '创建记录者(对应: bb_ucenter_member id),' AFTER `remark`, ADD `update_user_id` int(10) NULL DEFAULT '0' COMMENT '更新记录者(对应: bb_ucenter_member id)' AFTER `create_user_id`;";
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
		echo "m170620_101820_order cannot be reverted.\n";

		return false;
	}
	*/
}
