<?php

use yii\db\Migration;

/**
 * Class m171212_023056_bizSend
 */
class m171212_023056_bizSend extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}


		//企业送临时发单表
		$this->createTable('{{%biz_tmp_order}}', [
			'tmp_id'              => $this->primaryKey(),
			'user_id'             => $this->integer()->notNull()->comment("下单用户ID"),
			'tmp_no'              => $this->string(32)->notNull()->unique()->comment("临时订单号"),
			'cate_id'             => $this->smallInteger()->comment("分类ID"),
			'city_id'             => $this->smallInteger()->comment("城市ID"),
			'area_id'             => $this->smallInteger()->comment("镇区ID"),
			'region_id'           => $this->smallInteger()->comment("地区ID"),
			'tmp_from'            => $this->smallInteger(2)->comment("订单来源：1.android 2.IOS 3.微信"),
			'tmp_status'          => $this->smallInteger(2)->comment("订单状态：1.等待接单 2.已接单 3.配送中 4.用户取消 5.小帮取消 6.平台取消"),
			'robbed'              => $this->smallInteger(2)->defaultValue(0)->notNull()->comment("是否被抢：0.未抢 1已抢"),
			'tmp_qty'             => $this->integer()->comment("订单数量"),
			'provider_id'         => $this->integer()->comment("服务提供者ID（小帮ID）"),
			'appoint_provider_id' => $this->integer()->comment("指定服务提供者ID（小帮ID）接单"),
			'user_mobile'         => $this->string(24)->comment("用户电话"),
			'provider_mobile'     => $this->string(24)->comment("小帮电话"),
			'user_location'       => $this->string(64)->comment("用户下单坐标经纬度[经度,纬度]"),
			'user_address'        => $this->string()->comment("用户下单地址"),
			'provider_location'   => $this->string(64)->comment("小帮接单坐标经纬度[经度,纬度]"),
			'provider_address'    => $this->string()->comment("小帮接单地址"),
			'start_location'      => $this->string(64)->comment("下单起点坐标经纬度[经度,纬度]"),
			'start_address'       => $this->string()->comment("下单起点地址"),
			'order_ids'           => $this->string()->comment("订单ID 以逗号隔开"),
			'starting_distance'   => $this->integer()->comment("小帮与订单起点距离（单位：米）"),
			'content'             => $this->string()->comment("发单内容"),
			'batch_no'            => $this->string()->comment("内置批次"),
			'order_num'           => $this->integer()->comment("下单数量"),
			'create_time'         => $this->integer()->comment("创建时间"),
			'update_time'         => $this->integer()->comment("更新时间"),
			'cancel_time'         => $this->integer()->comment("取消时间"),
			'robbed_time'         => $this->integer()->comment("抢单时间"),
			'cancel_type'         => $this->string()->comment("取消类型"),
		], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='企业送临时发单'");
//
//		//企业送标签表
		$this->createTable('{{%biz_tag}}', [
			'id'             => $this->primaryKey(),
			'orders'         => $this->smallInteger()->comment("序号"),
			'tag_name'       => $this->string(255)->comment("标签"),
			'tag_key'        => $this->smallInteger()->comment("所属行业"),
			'biz_count'      => $this->integer()->comment("入驻数量"),
			'status'         => $this->smallInteger()->defaultValue(1)->comment("状态 1.启用 2.禁用"),
			'remark'         => $this->string(255)->comment("备注"),
			'create_time'    => $this->integer()->comment("创建时间"),
			'update_time'    => $this->integer()->comment("更新时间"),
			'create_user_id' => $this->integer()->comment("创建人"),
			'update_user_id' => $this->integer()->comment("更新人"),
		], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='企业送标签'");

		//充值管理表
		$this->createTable('{{%recharge_info}}', [
			'id'             => $this->primaryKey(),
			'province_id'    => $this->smallInteger()->comment("省份ID"),
			'city_id'        => $this->smallInteger()->comment("城市ID"),
			'place_no'       => $this->smallInteger()->comment("序号"),
			'type'           => $this->smallInteger()->comment("类型 1.用户端 2.企业送"),
			'status'         => $this->smallInteger()->defaultValue(1)->comment("状态 1.启用 2.禁用"),
			'card_id'        => $this->integer(11)->comment("优惠券id"),
			'amount_payable' => $this->money(10, 2)->comment("实付金额"),
			'get_amount'     => $this->money(10, 2)->comment("到账金额"),
			'remark'         => $this->string(255)->comment("备注"),
			'create_time'    => $this->integer()->comment("创建时间"),
			'update_time'    => $this->integer()->comment("更新时间"),
			'create_user_id' => $this->integer()->comment("创建人"),
			'update_user_id' => $this->integer()->comment("更新人"),
		], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='充值管理'");

		//企业送日志
		$this->createTable('{{%biz_info_log}}', [
			'log_id'         => $this->primaryKey(),
			'biz_id'         => $this->integer()->notNull()->comment("企业送ID"),
			'log_key'        => $this->string()->comment("日志key"),
			'log_value'      => $this->string()->comment("日志value"),
			'remark'         => $this->text()->comment("备注"),
			'create_time'    => $this->integer(10)->comment("创建时间"),
			'create_user_id' => $this->integer()->comment("创建人"),
		], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='企业送日志'");

		$this->addColumn('{{%biz_info}}', 'tag_id', "integer COMMENT '标签ID'");

		//充值订单表
		$this->createTable('{{%recharge_order}}', [
			'id'               => $this->primaryKey(),
			'user_id'          => $this->integer()->notNull()->comment("下单用户ID"),
			'recharge_no'      => $this->string(32)->notNull()->unique()->comment("充值订单号"),
			'recharge_from'    => $this->smallInteger(2)->comment("订单来源：1.android 2.IOS 3.微信"),
			'recharge_status'  => $this->smallInteger(2)->comment("订单状态：1.等待支付 2.支付成功 "),
			'recharge_info_id' => $this->integer()->comment("充值表id"),
			'card_id'          => $this->integer()->comment("优惠券id"),
			'order_amount'     => $this->money(10, 2)->comment("订单金额"),
			'amount_payable'   => $this->money(10, 2)->comment("实付金额"),
			'get_amount'       => $this->money(10, 2)->comment("到账金额"),
			'create_time'      => $this->integer()->comment("创建时间"),
			'update_time'      => $this->integer()->comment("更新时间"),
			'payment_id'       => $this->smallInteger(2)->comment('	支付方式：1.余额 2.银行卡 3.支付宝 4.微信'),
		], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='充值订单表'");

		$sql = "ALTER TABLE `bb_card` ADD `belong_type` smallint(2) NOT NULL DEFAULT '1' COMMENT '所属类型: 1.普通用户 2.企业送';";
		$this->db->createCommand($sql)->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%biz_tmp_order}}');
		$this->dropTable('{{%biz_tag}}');
		$this->dropTable('{{%recharge_info}}');
		$this->dropTable('{{%biz_info_log}}');
		$this->dropTable('{{%recharge_order}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171212_023056_bizSend cannot be reverted.\n";

		return false;
	}
	*/


}
