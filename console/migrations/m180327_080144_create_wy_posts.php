<?php

use yii\db\Migration;

/**
 * Class m180327_080144_create_wy_posts
 */
class m180327_080144_create_wy_posts extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="文章管理"';
		}
		//订单自动确认检测
		$this->createTable('{{%posts}}', [
			'id'             => $this->primaryKey()->comment('ID'),
			'title'          => $this->char(80)->comment("标题"),
			'main_pic'       => $this->string(200)->comment("主图"),
			'tags'           => $this->char(40)->comment('唯一标识'),
			'excerpt'        => $this->string(255)->comment('简介'),
			'type'           => $this->smallInteger(2)->comment('类型 1轮播图 2公告 3协议'),
			'sort'           => $this->integer(10)->comment('排序'),
			'province_id'    => $this->integer(10)->comment('省份ID'),
			'city_id'        => $this->integer(10)->comment('城市ID'),
			'area_id'        => $this->integer(10)->comment('区域ID'),
			'content'        => $this->text()->comment('内容'),
			'status'         => $this->smallInteger(2)->comment('状态 0关闭 1开启'),
			'remark'         => $this->string(255)->comment('备注'),
			'is_link'        => $this->smallInteger(2)->comment('是否外链 0否 1是'),
			'link'           => $this->text()->comment('外链'),
			'create_time'    => $this->integer(10)->comment('创建时间'),
			'update_time'    => $this->integer(10)->comment('更新时间'),
			'create_user_id' => $this->integer(10)->comment('创建用户ID'),
			'update_user_id' => $this->integer(10)->comment('更新用户ID')

		], $tableOptions);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%posts}}');
		echo "m180327_080144_create_wy_posts cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180327_080144_create_wy_posts cannot be reverted.\n";

		return false;
	}
	*/
}
