<?php

use yii\db\Migration;

class m171019_120937_agent_fee_rel extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='代理商抽佣分成关系表'";
		}

		$this->createTable('{{%agent_fee_rel}}', [
			'id'                 => $this->primaryKey(),
			'platform_taken'     => $this->money(10, 2)->comment("平台抽成"),
			'parent_agent_taken' => $this->money(10, 2)->comment("上级抽成"),
			'parent_agent_id'    => $this->integer(10)->comment("上级代理ID"),
			'agent_taken'        => $this->money(10, 2)->comment("代理抽成"),
			'agent_id'           => $this->integer(10)->comment("代理ID"),
			'region_id'          => $this->smallInteger()->comment("地区ID"),
			'create_time'        => $this->integer(10)->comment("创建时间"),
			'update_time'        => $this->integer(10)->comment("更新时间"),
		], $tableOptions);

	}

	public function safeDown()
	{
		echo "m171019_120937_agent_fee_rel cannot be reverted.\n";

		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171019_120937_agent_fee_rel cannot be reverted.\n";

		return false;
	}
	*/
}
