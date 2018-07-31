<?php

use yii\db\Migration;

/**
 * Class m180306_013541_update_user
 */
class m180306_013541_update_user extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{

		$sql
			= "ALTER TABLE `bb_51_user`
ADD `update_time` int NULL COMMENT '更新时间';";

		$this->db->createCommand($sql)->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m180306_013541_update_user cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180306_013541_update_user cannot be reverted.\n";

		return false;
	}
	*/
}
