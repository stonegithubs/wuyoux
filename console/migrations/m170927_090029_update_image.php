<?php

use yii\db\Migration;

class m170927_090029_update_image extends Migration
{
	public function safeUp()
	{

		$sql[] = "ALTER TABLE `bb_51_image`
ADD `ids_ref` int(10) unsigned NULL COMMENT '自定义',
ADD `type` int(2) unsigned NULL DEFAULT '0' COMMENT '图片类型' AFTER `ids_ref`;";

		$sql[]  = "ALTER TABLE `bb_51_image`
ADD `update_time` int(10) unsigned NULL COMMENT '更新时间';";


		$sql_str = implode(' ', $sql);
		$this->db->createCommand($sql_str)->execute();
	}

	public function safeDown()
	{
		echo "m170927_090029_update_image cannot be reverted.\n";

		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m170927_090029_update_image cannot be reverted.\n";

		return false;
	}
	*/
}
