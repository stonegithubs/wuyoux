<?php

use yii\db\Migration;

class m171107_023319_wy_token extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='验证码表'";
		}

		$this->createTable('{{%token}}', [
			'id'          => $this->primaryKey(),
			'keys'        => $this->string(100)->comment("手机"),
			'token'       => $this->string(64)->comment("验证码"),
			'type'        => $this->integer(4)->comment("1:注册 2:找回密码 3:支付密码 "),
			'expire'      => $this->integer(11)->comment("存在时间"),
			'create_time' => $this->integer(11)->comment("创建时间"),
			'status'      => $this->integer(1)->comment("1:成功 2:失败"),
		], $tableOptions);
	}

	public function safeDown()
	{
		echo "m171107_023319_wy_token cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171107_023319_wy_token cannot be reverted.\n";

		return false;
	}
	*/
}
