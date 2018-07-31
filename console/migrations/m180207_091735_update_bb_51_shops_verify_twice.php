<?php

use yii\db\Migration;

class m180207_091735_update_bb_51_shops_verify_twice extends Migration
{
	public function safeUp()
	{
		$sql = "ALTER TABLE `bb_51_shops_verify` DROP `shops_id`;";
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
		echo "m180207_091735_update_bb_51_shops_verify_twice cannot be reverted.\n";

		return false;
	}
	*/
}
