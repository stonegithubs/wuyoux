<?php

use yii\db\Migration;

/**
 * Class m180129_080031_user_minitoken
 */
class m180129_080031_user_minitoken extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="小程序对应的token"';
		}
		//订单自动确认检测
		$this->createTable('{{%user_mini_token}}', [
			'id'           => $this->primaryKey(),
			'user_id'      => $this->integer()->notNull()->comment("用户ID"),
			'mini_type'    => $this->smallInteger(2)->comment("小程序类型：1.企业送 2.快送 3.摩的"),
			'access_token' => $this->string(64)->comment("授权token"),
			'openid'       => $this->string(64)->comment("openid"),
			'unionid'      => $this->string(64)->comment("unionid"),
			'session_key'  => $this->string(64)->comment("session_key"),
			'expire'       => $this->integer()->comment("到期时间"),
			'create_time'  => $this->integer(10)->comment("创建时间"),
			'update_time'  => $this->integer(10)->comment("更新时间"),

		], $tableOptions);

	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m180129_080031_user_minitoken cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180129_080031_user_minitoken cannot be reverted.\n";

		return false;
	}
	*/
}
