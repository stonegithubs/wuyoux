<?php

use yii\db\Migration;

/**
 * Class m180703_022536_create_city_opening
 */
class m180703_022536_create_city_opening extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('{{%city_opening}}', [
			'id'             => $this->primaryKey()->comment('主表ID'),
			'province_id'    => $this->integer()->comment('省份ID'),
			'city_id'        => $this->integer()->comment('城市ID'),
			'area_id'        => $this->integer()->comment('区域ID'),
			'status'         => $this->smallInteger(2)->comment('状态 1开启 2暂停'),
			'remark'         => $this->string()->comment("备注"),
			'cate_data'      => $this->string()->comment("开通分类 json数据格式"),
			'open_day'       => $this->integer(10)->comment('开城日期'),
			'create_time'    => $this->integer(10)->comment('创建时间'),
			'update_time'    => $this->integer(10)->comment('更新时间'),
			'create_user_id' => $this->integer()->comment('创建人'),
			'update_user_id' => $this->integer()->comment('创建人'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="城市开通管理"');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		echo "m180703_022536_create_city_opening cannot be reverted.\n";
		$this->dropTable('{{%city_opening}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180703_022536_create_city_opening cannot be reverted.\n";

		return false;
	}
	*/
}
