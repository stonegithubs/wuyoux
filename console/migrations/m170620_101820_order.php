<?php

use yii\db\Migration;

class m170620_101820_order extends Migration
{


	public function safeUp()
	{

		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%order}}', [
			'order_id'                 => $this->primaryKey(),
			'user_id'                  => $this->integer()->notNull()->comment("下单用户ID"),
			'order_no'                 => $this->string(32)->notNull()->unique()->comment("订单号"),
			'cate_id'                  => $this->smallInteger()->comment("分类ID"),
			'region_id'                => $this->smallInteger()->comment("地区ID"),
			'order_type'               => $this->smallInteger(2)->comment("订单类型：1.摩的专车 2.小帮快送"),
			'order_from'               => $this->smallInteger(2)->comment("订单来源：1.android 2.IOS  3.微信"),
			'order_status'             => $this->smallInteger(2)->comment("订单状态：1.等待支付 2.等待网关确认支付 3.用户取消 4.小帮取消 5.进行中 6.已完成 7.已评价 8.客服处理 9.平台取消"),
			'payment_id'               => $this->smallInteger(2)->comment("支付方式：1.余额 2.银行卡 3.支付宝 4.微信"),
			'robbed'                   => $this->smallInteger(2)->defaultValue(0)->notNull()->comment("是否被抢：0.未抢 1已抢"),
			'payment_status'           => $this->smallInteger(2)->comment("支付状态：1.待支付 2.已支付 3.部分退款 4.全部退款 5.待退款"),
			'order_amount'             => $this->money(10, 2)->comment("订单金额"),        //跑腿 单价*数量 + 小费
			'amount_payable'           => $this->money(10, 2)->comment("实付金额"),
			'discount'                 => $this->money(10, 2)->comment("优惠金额=卡券金额+在线宝金额+其他优惠"),
			'provider_estimate_amount' => $this->money(10, 2)->comment("小帮预计收到金额"),
			'provider_actual_amount'   => $this->money(10, 2)->comment("小帮实际收到金额"),
			'card_id'                  => $this->integer()->comment("用户卡券ID"),
			'online_money'             => $this->money(10, 2)->comment("在线宝金额"),
			'provider_id'              => $this->integer()->comment("服务提供者ID（小帮ID）"),
			'appoint_provider_id'      => $this->integer()->comment("指定服务提供者ID（小帮ID）接单"),
			'user_mobile'              => $this->string(24)->comment("用户电话"),
			'provider_mobile'          => $this->string(24)->comment("小帮电话"),
			'user_location'            => $this->string(64)->comment("用户下单坐标经纬度[经度,纬度]"),
			'user_address'             => $this->string()->comment("用户下单地址"),
			'provider_location'        => $this->string(64)->comment("小帮接单坐标经纬度[经度,纬度]"),
			'provider_address'         => $this->string()->comment("小帮接单地址"),
			'start_location'           => $this->string(64)->comment("下单起点坐标经纬度[经度,纬度]"),
			'start_address'            => $this->string()->comment("下单起点地址"),
			'end_location'             => $this->string(64)->comment("下单终点坐标经纬度[经度,纬度]"),
			'end_address'              => $this->string()->comment("下单终点地址"),
			'create_time'              => $this->integer(10)->comment("创建时间"),
			'update_time'              => $this->integer(10)->comment("更新时间"),
			'cancel_time'              => $this->integer(10)->comment("取消时间"),
			'robbed_time'              => $this->integer(10)->comment("抢单时间"),
			'payment_time'             => $this->integer(10)->comment("支付时间"),
			'finish_time'              => $this->integer(10)->comment("完成时间"),
			'request_cancel_id'        => $this->integer(11)->comment("发起取消人"),
			'request_cancel_time'      => $this->integer(10)->comment("发起取消时间"),
			'user_deleted'             => $this->smallInteger(1)->defaultValue(0)->comment("用户删除：0.正常 1.删除"),
			'provider_deleted'         => $this->smallInteger(1)->defaultValue(0)->comment("小帮删除：0.正常 1.删除"),

		], $tableOptions);

		//订单跑腿
		$this->createTable('{{%order_errand}}', [
			'errand_id'         => $this->primaryKey(),
			'order_id'          => $this->integer()->notNull()->comment("订单ID（取决于订单主表order_id）"),
			'errand_status'     => $this->smallInteger(2)->comment("快送状态：1.等待接单 2.小帮已接单 3.联系客服 4.正在服务 5.服务完成"),
			'order_distance'    => $this->smallInteger()->comment("订单起点与终点距离（单位：米）"),
			'starting_distance' => $this->smallInteger()->comment("小帮与订单起点距离（单位：米）"),
			'begin_time'        => $this->integer(10)->comment("开始时间"),
			'begin_location'    => $this->string(64)->comment("开始坐标经纬度[经度,纬度]"),
			'begin_address'     => $this->string()->comment("开始地址"),
			'finish_time'       => $this->integer(10)->comment("完成时间"),
			'finish_location'   => $this->string(64)->comment("完成坐标经纬度[经度,纬度]"),
			'finish_address'    => $this->string()->comment("完成地址"),
			'total_fee'         => $this->money(10, 2)->comment("小费总额"), //sum (小费表)
			'first_fee'         => $this->money(10, 2)->comment("首次小费"),
			'service_price'     => $this->money(10, 2)->comment("服务单价"), //20/小时
			'service_time'      => $this->integer(10)->comment("服务时间"),
			'service_qty'       => $this->smallInteger()->comment("服务时长（单位：小时）"),
			'errand_type'       => $this->smallInteger(2)->comment("快送类型：1.帮我买 2.帮我送 3.帮我办"),
			'errand_content'    => $this->string()->comment("快送内容"),
			'mobile'            => $this->string(24)->comment("联系电话（收货人或者其他人）"),
			'maybe_time'        => $this->integer(10)->comment("预约收货时间"),
			'actual_time'       => $this->integer(10)->comment("实际收货时间"),
			'cancel_type'       => $this->string(20)->comment("取消类型"),

		], $tableOptions);

		//小费表
		$this->createTable('{{%order_fee}}', [
			'fee_id'      => $this->primaryKey(),
			'ids_ref'     => $this->integer()->notNull()->comment("订单ID（取决于小费类型的表	唯一ID）"),
			'type'        => $this->smallInteger(2)->comment("小费类型：1.订单 2.其他"),
			'amount'      => $this->money(10, 2)->comment("小费"),
			'status'      => $this->smallInteger(2)->comment("支付状态：1.待支付 2.已支付"),
			'update_time' => $this->integer(10)->comment("更新时间"),
			'create_time' => $this->integer(10)->comment("创建时间")
		], $tableOptions);


		$this->createTable('{{%order_log}}', [
			'log_id'      => $this->primaryKey(),
			'order_id'    => $this->integer()->notNull()->comment("订单ID（取决于订单主表order_id）"),
			'log_key'     => $this->string()->comment("日志key"),
			'log_value'   => $this->string()->comment("日志value"),
			'remark'      => $this->text()->comment("备注"),
			'create_time' => $this->integer(10)->comment("创建时间")
		], $tableOptions);


		$this->createTable('{{%transaction}}', [
			'id'             => $this->primaryKey(),
			'ids_ref'        => $this->string(128)->comment("订单ID（创建支付订单的ID）"),
			'payment_id'     => $this->smallInteger(2)->comment("支付方式：1.余额 2.现金 3.支付宝 4.微信"),
			'fee'            => $this->money(10, 2)->comment("费用"),
			'transaction_no' => $this->string(128)->comment("支付流水号"),
			'trade_no'       => $this->string(128)->comment("第三方平台交易流水号"),
			'status'         => $this->smallInteger(2)->comment("状态：1.未支付 2.已支付"),
			'type'           => $this->smallInteger(2)->comment("类型：1.订单 2.充值 3.退款 4.小费 5.话费 6.捐款"),
			'remark'         => $this->string()->comment("备注"),
			'data'           => $this->text()->comment("数据json格式化"),
			'create_time'    => $this->integer(10)->comment("创建时间"),
			'update_time'    => $this->integer(10)->comment("更新时间")
		], $tableOptions);
	}

	public function safeDown()
	{
		$this->dropTable('{{%order}}');
		$this->dropTable('{{%order_errand}}');
		$this->dropTable('{{%order_fee}}');
		$this->dropTable('{{%order_log}}');
		$this->dropTable('{{%transaction}}');
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m170620_101820_order cannot be reverted.\n";

		return false;
	}
	*/
}
