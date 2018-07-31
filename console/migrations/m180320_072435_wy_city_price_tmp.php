<?php

use yii\db\Migration;

class m180320_072435_wy_city_price_tmp extends Migration
{
	public function safeUp()
	{
		$sql
			= "DROP TABLE IF EXISTS `wy_city_price_tmp`;
CREATE TABLE `wy_city_price_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '城市费率ID',
  `cate_id` int(11) NOT NULL COMMENT '分类ID',
  `province_id` int(11) NOT NULL COMMENT '省份ID',
  `city_id` int(11) NOT NULL COMMENT '城市ID',
  `area_id` int(11) DEFAULT NULL COMMENT '区域预留ID',
  `day_time` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '白天开始时间',
  `day_time_init` smallint(5) DEFAULT NULL COMMENT '白天初始时间(分钟)',
  `day_time_init_price` decimal(5,2) DEFAULT NULL COMMENT '白天初始时间价格',
  `day_time_unit_price` decimal(5,2) DEFAULT NULL COMMENT '白天超时时间，每60分钟',
  `day_range_init` int(11) DEFAULT NULL COMMENT '白天初始距离(米)',
  `day_range_init_price` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '白天初始距离价格',
  `day_range_unit_price` decimal(5,2) DEFAULT NULL COMMENT '白天超出距离，每1000米',
  `day_service_fee` decimal(5,2) DEFAULT NULL COMMENT '白天服务费',
  `day_busy_fee` decimal(5,2) DEFAULT NULL COMMENT '白天高峰加价',
  `night_time` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '夜间开始时间',
  `night_time_init` smallint(5) DEFAULT NULL COMMENT '夜间初始时间(分钟)',
  `night_time_init_price` decimal(5,2) DEFAULT NULL COMMENT '夜间初始时间价格',
  `night_time_unit_price` decimal(5,2) DEFAULT NULL COMMENT '夜间超时时间0，每60分钟',
  `night_range_init` int(11) DEFAULT NULL COMMENT '夜间初始距离(米)',
  `night_range_init_price` decimal(5,2) DEFAULT NULL COMMENT '夜间初始距离价格',
  `night_range_unit_price` decimal(5,2) DEFAULT NULL COMMENT '夜间超出距离，每1000米',
  `night_service_fee` decimal(5,2) DEFAULT NULL COMMENT '夜间服务费',
  `night_busy_fee` decimal(5,2) DEFAULT NULL COMMENT '夜间高峰加价',
  `online_money_discount` decimal(5,2) DEFAULT NULL COMMENT '在线宝抵扣',
  `online_payment_discount` decimal(5,2) DEFAULT NULL COMMENT '在线支付优惠',
  `full_time_take` decimal(10,2) DEFAULT NULL COMMENT '全职抽成',
  `part_time_take` decimal(10,2) DEFAULT NULL COMMENT '兼职抽成',
  `status` smallint(2) DEFAULT '0' COMMENT '执行状态 0等待审批 1允许执行 2中途终止',
  `approval_id` int(10) DEFAULT NULL COMMENT '审核ID',
  `is_update` int(10) DEFAULT '0' COMMENT '是否更新 0待更新 1开始更新 2回落更新',
  `city_price_id` int(10) DEFAULT NULL COMMENT '城市费率ID',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `start_time` int(10) DEFAULT NULL COMMENT '开始时间',
  `end_time` int(10) DEFAULT NULL COMMENT '结束时间',
  `create_user_id` int(11) DEFAULT NULL COMMENT '创建人',
  `update_user_id` int(11) DEFAULT NULL COMMENT '更新人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='城市费率-临时调价表';";

		$this->db->createCommand($sql)->execute();
	}

	public function safeDown()
	{
		$this->dropTable('{{%city_price_tmp}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180320_072435_wy_city_price_tmp cannot be reverted.\n";

		return false;
	}
	*/
}
