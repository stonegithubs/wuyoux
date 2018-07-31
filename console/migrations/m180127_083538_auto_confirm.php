<?php

use yii\db\Migration;

/**
 * Class m180127_083538_auto_confirm
 */
class m180127_083538_auto_confirm extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="订单延迟确认-临时方案"';
		}
		//订单自动确认检测
		$this->createTable('{{%order_delay_confirm}}', [
			'id'          => $this->primaryKey(),
			'user_id'     => $this->integer()->notNull()->comment("用户ID"),
			'order_no'    => $this->string(32)->comment("订单号"),
			'errand_type' => $this->smallInteger(2)->comment("快送类型"),
			'status'      => $this->smallInteger(2)->comment("状态：1初始,2完成,3失败"),
			'expire'      => $this->integer(10)->comment("到期时间"),
			'remark'      => $this->string()->comment("备注"),
			'create_time' => $this->integer(10)->comment("创建时间"),
			'update_time' => $this->integer(10)->comment("更新时间"),
		], $tableOptions);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m180127_083538_auto_confirm cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180127_083538_auto_confirm cannot be reverted.\n";

		return false;
	}
	*/
}
