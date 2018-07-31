<?php

use yii\db\Migration;

/**
 * Class m180413_093404_wy_user_wechat_rel
 */
class m180413_093404_wy_user_wechat_rel extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户和微信的关系表"';
		}
		//订单自动确认检测
		$this->createTable('{{%user_wechat_rel}}', [
			'id'           => $this->primaryKey(),
			'user_id'      => $this->integer()->notNull()->comment("用户ID"),
			'app_type'     => $this->smallInteger(2)->comment("类型：1.企业送小程序 2.快送小程序 3.摩的小程序 4.公众号"),
			'access_token' => $this->string(64)->comment("授权token"),
			'openid'       => $this->string(64)->comment("openid"),
			'unionid'      => $this->string(64)->comment("unionid"),
			'session_key'  => $this->string(64)->comment("小程序session_key"),
			'expire'       => $this->integer()->comment("到期时间"),
			'create_time'  => $this->integer()->comment("创建时间"),
			'update_time'  => $this->integer()->comment("更新时间"),

		], $tableOptions);

	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m180413_093404_wy_user_wechat_rel cannot be reverted.\n";
		$this->dropTable('{{%user_wechat_rel}}');

		return true;
	}
}
