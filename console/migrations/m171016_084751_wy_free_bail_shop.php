<?php

use yii\db\Migration;

class m171016_084751_wy_free_bail_shop extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='免保证金商家表'";
		}

		$this->createTable('{{%free_bail_shop}}', [
			'id'             => $this->primaryKey(),
			'shops_id'       => $this->Integer(10)->notNull()->comment("商家ID"),
			'start_time'     => $this->Integer(10)->notNull()->comment("免保证金开始时间"),
			'end_time'       => $this->Integer(10)->notNull()->comment("免保证金结束时间"),
			'create_time'    => $this->Integer(10)->comment("创建时间"),
			'update_time'    => $this->Integer(10)->comment("更新时间"),
			'create_user_id' => $this->integer()->comment("创建人"),
			'update_user_id' => $this->integer()->comment("更新人"),
			'status'         => $this->smallInteger(2)->comment("启用状态"),
		], $tableOptions);
	}

	public function safeDown()
	{
		echo "m171016_084751_wy_free_bail_shop cannot be reverted.\n";

//		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171016_084751_wy_free_bail_shop cannot be reverted.\n";

		return false;
	}
	*/
}
