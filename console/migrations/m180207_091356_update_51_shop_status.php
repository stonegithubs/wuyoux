<?php

use yii\db\Migration;

class m180207_091356_update_51_shop_status extends Migration
{
	public function safeUp()
	{
		$sql = "ALTER TABLE `bb_51_shops` CHANGE `status` `status` int(2) NOT NULL DEFAULT '0' COMMENT '审核状态：0等待审核，1二审通过，2审核失败，3待补充资料,4已删除,5一审通过' AFTER `stage`;";
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
		echo "m180207_091356_update_51_shop_status cannot be reverted.\n";

		return false;
	}
	*/
}
