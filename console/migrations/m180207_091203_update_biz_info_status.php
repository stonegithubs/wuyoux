<?php

use yii\db\Migration;

class m180207_091203_update_biz_info_status extends Migration
{
	public function safeUp()
	{
		$sql
			= "ALTER TABLE `wy_biz_info`
ADD `enter_time` int(10) NULL COMMENT '入驻时间' AFTER `create_time`,
CHANGE `status` `status` smallint(2) NULL COMMENT '审核状态:0等待审批1二审通过2退回3一审通过' AFTER `update_time`;";
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
		echo "m180207_091203_update_biz_info_status cannot be reverted.\n";

		return false;
	}
	*/
}
