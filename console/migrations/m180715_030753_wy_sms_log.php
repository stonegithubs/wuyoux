<?php

use yii\db\Migration;

/**
 * Class m180715_030753_wy_sms_log
 */
class m180715_030753_wy_sms_log extends Migration
{
	public function safeUp()
	{
		$this->createTable('{{%sms_log}}', [
			'id'          => $this->primaryKey()->comment('主表ID'),
			'mobile'      => $this->string(11)->comment('手机号码'),
			'content'     => $this->text()->comment('内容'),
			'tpl'         => $this->string()->comment('模板'),
			'status'      => $this->smallInteger(2)->comment('状态 1触发成功 2触发失败 3其他'),
			'result'      => $this->string()->comment("触发结果"),
			'create_time' => $this->integer(10)->comment('创建时间'),
			'update_time' => $this->integer(10)->comment('更新时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="短信发送记录"');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		echo "m180715_030753_wy_sms_log cannot be reverted.\n";

		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180715_030753_wy_sms_log cannot be reverted.\n";

		return false;
	}
	*/
}
