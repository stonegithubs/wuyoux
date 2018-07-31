<?php

use yii\db\Migration;

class m171113_061008_trip extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="订单交通出行"';
		}
		//订单交通出行
		$this->createTable('{{%order_trip}}', [
			'trip_id'           => $this->primaryKey(),
			'order_id'          => $this->integer()->notNull()->comment("订单ID（取决于订单主表order_id）"),
			'trip_type'         => $this->smallInteger(2)->comment("出行类型：1.摩的专车 2.小帮专车 3.小帮货车"),
			'trip_status'       => $this->smallInteger(2)->comment("摩的状态：1.等待接单 2.接到乘客 3.安全到达"),
			'estimate_amount'   => $this->money(10, 2)->comment("初始预计金额"),
			'order_distance'    => $this->integer(10)->comment("订单起点与终点距离（单位：米）"),
			'starting_distance' => $this->integer(10)->comment("小帮与订单起点距离（单位：米）"),
			'ending_distance'   => $this->integer(10)->comment("小帮与订单终点距离（单位：米）"),
			'license_plate'     => $this->string(32)->comment("车牌号码"),
			'pick_time'         => $this->integer(10)->comment("接客时间"),
			'pick_location'     => $this->string(64)->comment("接客坐标经纬度[经度,纬度]"),
			'pick_address'      => $this->string()->comment("接客地址"),
			'arrive_time'       => $this->integer(10)->comment("到达时间"),
			'arrive_location'   => $this->string(64)->comment("到达坐标经纬度[经度,纬度]"),
			'arrive_address'    => $this->string()->comment("到达地址")

		], $tableOptions);
	}

	public function safeDown()
	{
		$this->dropTable('{{%order_trip}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171113_061008_trip cannot be reverted.\n";

		return false;
	}
	*/
}
