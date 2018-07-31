<?php

use yii\db\Migration;

class m171109_025811_user_token extends Migration
{
	public function safeUp()
	{

		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='App登录token表'";
		}

		$this->createTable('{{%user_token}}', [
			'id'           => $this->primaryKey(),
			'user_id'      => $this->integer()->notNull()->comment("用户ID"),
			'provider_id'  => $this->integer()->notNull()->comment("小帮ID"),
			'role'         => $this->string(32)->comment("角色：user,provider"),
			'app_type'     => $this->smallInteger(2)->comment("APP来源：1.安卓 2.IOS"),        //类型 用户版，小帮版
			'access_token' => $this->string(64)->comment("授权token"),
			'app_version'  => $this->string(64)->comment("APP版本"),
			'client_id'    => $this->string(64)->comment("ClientID"),
			'push_type'    => $this->smallInteger(2)->comment("个推还是极光"),
			'expire'       => $this->integer()->comment("到期时间"),
			'create_time'  => $this->integer(10)->comment("创建时间"),
			'update_time'  => $this->integer(10)->comment("更新时间"),

		], $tableOptions);
	}

	public function safeDown()
	{
		echo "m171109_025811_user_token cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171109_025811_user_token cannot be reverted.\n";

		return false;
	}
	*/
}
