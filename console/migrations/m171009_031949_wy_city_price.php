<?php

use yii\db\Migration;

class m171009_031949_wy_city_price extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='城市费率表'";
		}

		$this->createTable('{{%city_price}}', [
			'id'                      => $this->primaryKey(),
			'cate_id'                 => $this->integer()->notNull()->comment("分类ID"),
			'province_id'             => $this->integer()->notNull()->comment("省份ID"),
			'city_id'                 => $this->integer()->notNull()->comment("城市ID"),
			'area_id'                 => $this->integer()->comment("区域预留ID"),
			'day_time'                => $this->string(10)->comment("白天开始时间"),
			'day_time_init'           => $this->smallInteger(5)->comment("白天初始时间(分钟)"),
			'day_time_init_price'     => $this->money(5, 2)->comment("白天初始时间价格"),
			'day_time_unit_price'     => $this->money(5, 2)->comment("白天超时时间，每60分钟"),
			'day_range_init'          => $this->integer()->comment("白天初始距离(米)"),
			'day_range_init_price'    => $this->money(5, 2)->defaultValue(0)->notNull()->comment("白天初始距离价格"),
			'day_range_unit_price'    => $this->money(5, 2)->comment("白天超出距离，每1000米"),
			'day_service_fee'         => $this->money(5, 2)->comment("白天服务费"),
			'day_busy_fee'            => $this->money(5, 2)->comment("白天高峰加价"),
			'night_time'              => $this->string(10)->comment("夜间开始时间"),
			'night_time_init'         => $this->smallInteger(5)->comment("夜间初始时间(分钟)"),
			'night_time_init_price'   => $this->money(5, 2)->comment("夜间初始时间价格"),
			'night_time_unit_price'   => $this->money(5, 2)->comment("夜间超时时间0，每60分钟"),
			'night_range_init'        => $this->integer()->comment("夜间初始距离(米)"),
			'night_range_init_price'  => $this->money(5, 2)->comment("夜间初始距离价格"),
			'night_range_unit_price'  => $this->money(5, 2)->comment("夜间超出距离，每1000米"),
			'night_service_fee'       => $this->money(5, 2)->comment("夜间服务费"),
			'night_busy_fee'          => $this->money(5, 2)->comment("夜间高峰加价"),
			'online_money_discount'   => $this->decimal(5, 2)->comment("在线宝抵扣"),
			'online_payment_discount' => $this->decimal(5, 2)->comment("在线支付优惠"),
			'full_time_take'          => $this->decimal(10, 2)->comment("全职抽成"),
			'part_time_take'          => $this->decimal(10, 2)->comment("兼职抽成"),
			'status'                  => $this->smallInteger(2)->comment("状态"),
			'create_time'             => $this->integer(10)->comment("创建时间"),
			'update_time'             => $this->integer(10)->comment("更新时间"),
			'create_user_id'          => $this->integer()->comment("创建人"),
			'update_user_id'          => $this->integer()->comment("更新人"),
		], $tableOptions);
	}

	public function safeDown()
	{
		echo "m171009_031949_wy_city_price cannot be reverted.\n";

//		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171009_031949_wy_city_price cannot be reverted.\n";

		return false;
	}
	*/
}
