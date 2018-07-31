<?php

use yii\db\Migration;

class m171020_014045_wy_biz_info extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='企业用户信息表'";
		}

		$this->createTable('{{%biz_info}}', [
			'id'              => $this->primaryKey(),
			'user_id'         => $this->integer()->comment("用户id"),
			'biz_name'        => $this->string(100)->comment("企业名"),
			'biz_mobile'      => $this->string(50)->comment("企业电话"),
			'biz_address'     => $this->string(100)->comment("企业地址"),
			'biz_address_ext' => $this->string(100)->comment("企业补充地址"),
			'biz_location'    => $this->string(100)->comment("企业坐标"),
			'city_id'         => $this->integer()->comment("城市id"),
			'area_id'         => $this->integer()->comment("地区id"),
			'city_name'       => $this->string(50)->comment("城市名"),
			'area_name'       => $this->string(50)->comment("地区名"),
			'pic'             => $this->string(255)->comment("图片多图用，分开"),
			'introduction'    => $this->string(255)->comment("简介"),
			'default_content' => $this->string(255)->comment("默认发单内容"),
			'create_time'     => $this->integer(10)->comment("创建时间"),
			'update_time'     => $this->integer(10)->comment("更新时间"),
			'status'          => $this->smallInteger(2)->comment("0等待审批1通过2退回"),
		], $tableOptions);

	}

	public function safeDown()
	{
		echo "m171020_014045_wy_biz_info cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m171020_014045_wy_biz_info cannot be reverted.\n";

		return false;
	}
	*/
}
