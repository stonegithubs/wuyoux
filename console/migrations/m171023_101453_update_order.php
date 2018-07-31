<?php

use yii\db\Migration;

class m171023_101453_update_order extends Migration
{
	public function safeUp()
	{
		$sql = "ALTER TABLE wy_order ADD `city_id` INT (11) NULL COMMENT '城市id' AFTER cate_id;";
		$this->db->createCommand($sql)->execute();

		$sql2 = "ALTER TABLE wy_order ADD `area_id` INT (11) NULL COMMENT '镇区id' AFTER city_id;";
		$this->db->createCommand($sql2)->execute();
	}


	public function safeDown()
	{
		echo "m171023_101453_update_order cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171023_101453_update_order cannot be reverted.\n";

		return false;
	}
	*/
}
