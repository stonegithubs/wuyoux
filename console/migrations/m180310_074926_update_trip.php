<?php

use yii\db\Migration;

/**
 * Class m180310_074926_update_trip
 */
class m180310_074926_update_trip extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->dropTable('{{%order_trip}}');
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="订单出行"';
		}
		//订单交通出行
		$this->createTable('{{%order_trip}}', [
			'trip_id'           => $this->primaryKey(),
			'order_id'          => $this->integer()->notNull()->comment("订单ID（取决于订单主表order_id）"),
			'trip_type'         => $this->smallInteger(2)->comment("出行类型：1.摩的专车 2.小帮专车 3.电动车"),
			'trip_status'       => $this->smallInteger(2)->comment("出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达"),
			'estimate_amount'   => $this->money(10, 2)->comment("初始预计金额"),
			'actual_amount'     => $this->money(10, 2)->comment("行程实际金额"),
			'amount_ext'        => $this->money(10, 2)->comment("加价"),
			'estimate_distance' => $this->integer(10)->comment("预估订单起点与终点距离（单位：米）"),
			'actual_distance'   => $this->integer(10)->comment("实际订单起点与终点距离（单位：米）"),
			'starting_distance' => $this->integer(10)->comment("小帮与订单起点距离（单位：米）"),
			'license_plate'     => $this->string(32)->comment("车牌号码"),

			//到上车点
			'point_time'        => $this->integer(10)->comment("到上车点时间"),
			'point_location'    => $this->string(64)->comment("到上车点坐标经纬度[经度,纬度]"),
			'point_address'     => $this->string()->comment("到上车点地址"),

			//接到乘客
			'pick_time'         => $this->integer(10)->comment("接客时间"),
			'pick_location'     => $this->string(64)->comment("接客坐标经纬度[经度,纬度]"),
			'pick_address'      => $this->string()->comment("接客地址"),

			//安全到达
			'arrive_time'       => $this->integer(10)->comment("到达时间"),
			'arrive_location'   => $this->string(64)->comment("到达坐标经纬度[经度,纬度]"),
			'arrive_address'    => $this->string()->comment("到达地址"),
			//小费
			'total_fee'         => $this->money(10, 2)->comment("打赏小费"),
			'fee_time'          => $this->integer(10)->comment("打赏时间"),
			'cancel_type'       => $this->string(20)->comment("取消类型"),

		], $tableOptions);

		//订单投诉
		$this->createTable('{{%order_complaint}}', [
			'id'         => $this->primaryKey(),
			'ids_ref'    => $this->integer()->notNull()->comment("订单ID（取决于订单主表order_id）"),
			'content_id' => $this->string()->comment("投诉ID"),
			'content'    => $this->string()->comment("投诉内容"),
			'status'     => $this->smallInteger(2)->comment("处理状态：1.等待处理 2.已经处理"),
			'remark'     => $this->string()->comment("备注"),

		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="订单投诉"');


		//订单取消
		$this->createTable('{{%order_cancel}}', [
			'id'         => $this->primaryKey(),
			'ids_ref'    => $this->integer()->notNull()->comment("订单ID（取决于订单主表order_id）"),
			'content_id' => $this->string()->comment("取消ID"),
			'content'    => $this->string()->comment("取消内容"),
			'remark'     => $this->string()->comment("备注"),

		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="订单取消"');

	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%order_trip}}');
		$this->dropTable('{{%order_complaint}}');
		$this->dropTable('{{%order_cancel}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180310_074926_update_trip cannot be reverted.\n";

		return false;
	}
	*/
}
