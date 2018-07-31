<?php

use yii\db\Migration;

/**
 * Class m180621_065510_biz_order_area
 */
class m180621_065510_biz_order_area extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('{{%biz_user_district}}', [
			'id'          => $this->primaryKey()->comment('主表ID'),
			'user_id'     => $this->integer(10)->comment('用户ID'),
			'district'    => $this->string(16)->comment('地区'),
			'create_time' => $this->integer(10)->comment('创建时间'),
			'update_time' => $this->integer(10)->comment('更新时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业送用户自定义地区"');

		$this->createTable('{{%biz_agent_district}}', [
			'id'            => $this->primaryKey()->comment('主表ID'),
			'city_id'       => $this->integer(10)->comment('城市ID'),
			'area_id'       => $this->integer(10)->comment('区域ID'),
			'district_list' => $this->string(255)->comment('地区列表,用逗号隔开'),
			'agent_id'      => $this->integer(10)->comment('代理商ID'),
			'create_time'   => $this->integer(10)->comment('创建时间'),
			'update_time'   => $this->integer(10)->comment('更新时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业送代理商地区"');

		$this->addColumn('{{%biz_tmp_order}}', 'delivery_area', "varchar(255) DEFAULT NULL COMMENT '配送区域'");
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		echo "m180621_065510_biz_order_area cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180621_065510_biz_order_area cannot be reverted.\n";

		return false;
	}
	*/
}
