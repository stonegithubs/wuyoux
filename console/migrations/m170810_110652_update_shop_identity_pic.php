<?php

use yii\db\Migration;

class m170810_110652_update_shop_identity_pic extends Migration
{
	public function safeUp()
	{

		$sql = "ALTER TABLE `bb_51_shop_identity_pic` ADD `file_num` varchar(30) NULL COMMENT '档案编号';";
		$this->db->createCommand($sql)->execute();
	}

	public function safeDown()
	{
		echo "m170810_110652_update_shop_identity_pic cannot be reverted.\n";

		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m170810_110652_update_shop_identity_pic cannot be reverted.\n";

		return false;
	}
	*/
}
