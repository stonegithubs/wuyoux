<?php

use yii\db\Migration;

/**
 * Class m180528_082124_market
 */
class m180528_082124_market extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		//用户营销号表
		$this->createTable('{{%user_market}}', [
			'id'                   => $this->primaryKey()->comment('主表ID'),
			'user_id'              => $this->integer(10)->comment('用户ID'),
			'market_code'          => $this->string(16)->comment('营销号'),
			'earn_amount'          => $this->money(10, 3)->comment('总收益'),
			'freeze_amount'        => $this->money(10, 3)->comment('冻结金额'),
			'transfer_amount'      => $this->money(10, 3)->comment('总转出金额'),
			'transfer_realname'    => $this->string()->comment('提现真实姓名'),
			'transfer_accountname' => $this->string()->comment('提现账号名称'),
			'transfer_account'     => $this->string()->comment('提现账号'),
			'transfer_type'        => $this->smallInteger(2)->comment('提现类型 1余额 2支付宝 3银行'),
			'status'               => $this->smallInteger(2)->comment('状态 0等待审核 1正常 2封号'),
			'ban_time'             => $this->integer(10)->comment('封号时间'),
			'create_time'          => $this->integer(10)->comment('创建时间'),
			'update_time'          => $this->integer(10)->comment('更新时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户营销号表"');

		//营销关系表
		$this->createTable('{{%market_relation}}', [
			'id'             => $this->primaryKey()->comment('主表ID'),
			'user_id'        => $this->integer(10)->comment('用户ID'),
			'provider_id'    => $this->integer(10)->comment('小帮ID'),
			'type'           => $this->smallInteger(2)->comment('类型 1用户 2小帮'),
			'market_user_id' => $this->integer(10)->comment('受益人用户ID'),
			'remark'         => $this->string()->comment('备注'),
			'sum_amount'     => $this->money(10, 3)->comment('总收益'),
			'status'         => $this->smallInteger(2)->defaultValue(1)->comment('状态 1正常 2解除关系'),
			'rel_from'         => $this->smallInteger(3)->comment('推荐来源 1用户 2小帮 3其他'),
			'create_time'    => $this->integer(10)->comment('创建时间'),
			'update_time'    => $this->integer(10)->null()->comment('更新时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="营销关系表"');

		//营销收益表
		$this->createTable('{{%market_profit}}', [
			'id'             => $this->primaryKey()->comment('主表ID'),
			'order_id'       => $this->integer(10)->null()->comment('订单ID'),
			'user_id'        => $this->integer(10)->null()->comment('用户ID'),
			'market_user_id' => $this->integer(10)->null()->comment('收益人ID'),
			'type'           => $this->smallInteger(2)->comment('类型 1用户 2小帮'),
			'amount'         => $this->money(10, 3)->comment('金额'),
			'city_id'        => $this->integer(10)->comment('城市ID'),
			'area_id'        => $this->integer(10)->comment('区域ID'),
			'agent_id'       => $this->integer(10)->comment('代理商ID'),
			'create_time'    => $this->integer(10)->notNull()->comment('创建时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="营销收益表"');

		//营销提现表
		$this->createTable('{{%market_withdraw}}', [
			'id'                   => $this->primaryKey()->comment('主表ID'),
			'user_id'              => $this->integer(10)->null()->comment('用户ID'),
			'transfer_amount'      => $this->money(10, 3)->null()->comment('转出金额'),
			'transfer_realname'    => $this->string()->comment('提现真实姓名'),
			'transfer_accountname' => $this->string()->comment('提现账号名称'),
			'transfer_account'     => $this->string()->comment('提现账号'),
			'flow_number'          => $this->string()->comment('流水号'),
			'remark'               => $this->string()->comment('备注'),
			'transfer_type'        => $this->smallInteger(2)->comment('提现类型 1余额 2支付宝 3银行'),
			'status'               => $this->smallInteger(2)->comment('状态 0待审核 1提现成功 2提现失败'),
			'apply_source'         => $this->smallInteger(2)->comment('申请来源 1用户端 2小帮端'),
			'apply_time'           => $this->integer(10)->notNull()->comment('申请时间'),
			'transfer_time'        => $this->integer(10)->comment('提现时间'),
			'update_time'          => $this->integer(10)->comment('提现时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="营销提现表"');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		echo "m180528_082124_market cannot be reverted.\n";
		$this->dropTable('{{%user_market}}');
		$this->dropTable('{{%market_relation}}');
		$this->dropTable('{{%market_profit}}');
		$this->dropTable('{{%market_withdraw}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180528_082124_market cannot be reverted.\n";

		return false;
	}
	*/
}
