<?php

use yii\db\Migration;

class m180411_022437_create_third_part_token extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='开放平台账号'";
		}

		$this->createTable('{{%open_platform_account}}', [
			'id'          => $this->primaryKey(),
			'app_id'      => $this->char(10)->notNull()->comment("APPID"),
			'app_secret'  => $this->char(32)->notNull()->comment("APPSECRET"),
			'user_id'     => $this->integer(10)->notNull()->comment("用户ID"),
			'status'      => $this->Integer(1)->comment("状态(1启用2停用)"),
			'create_time' => $this->Integer(10)->comment("创建时间"),
			'update_time' => $this->Integer(10)->comment("更新时间"),

		], $tableOptions);
	}

	public function safeDown()
	{
		echo "m180411_022437_create_third_part_token cannot be reverted.\n";

		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180411_022437_create_third_part_token cannot be reverted.\n";

		return false;
	}
	*/
}
