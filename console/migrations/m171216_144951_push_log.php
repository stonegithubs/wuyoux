<?php

use yii\db\Migration;

/**
 * Class m171216_144951_push_log
 */
class m171216_144951_push_log extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="推送记录日志"';
		}
		//推送日志
		$this->createTable('{{%push_log}}', [
			'id'          => $this->primaryKey(),
			'user_id'     => $this->integer()->notNull()->comment("用户ID"),
			'provider_id' => $this->integer()->notNull()->comment("小帮ID"),
			'role'        => $this->string(32)->comment("角色：user,provider"),
			'app_type'    => $this->smallInteger(2)->comment("APP来源：1.安卓 2.IOS"),        //类型 用户版，小帮版
			'app_version' => $this->string(64)->comment("APP版本"),
			'client_id'   => $this->string(64)->comment("ClientID"),
			'tag'         => $this->string()->comment("标签"),
			'task_id'     => $this->string()->comment("推送任务ID"),
			'status'      => $this->smallInteger(2)->comment("推送状态：0失败 1成功"),
			'data'        => $this->text()->comment("推送内容"),
			'create_time' => $this->integer(10)->comment("创建时间"),
		], $tableOptions);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m171216_144951_push_log cannot be reverted.\n";
		$this->dropTable('{{%push_log}}');

	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171216_144951_push_log cannot be reverted.\n";

		return false;
	}
	*/
}
