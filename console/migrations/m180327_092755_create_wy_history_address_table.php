<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wy_history_address`.
 */
class m180327_092755_create_wy_history_address_table extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function up()
	{
		//$this->dropTable('{{%history_address}}');
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户历史地址"';
		}
		$this->createTable('{{%history_address}}', [
			'id'                 => $this->primaryKey(),
			'user_id'            => $this->integer(11)->notNull()->defaultValue(0)->comment('用户id'),
			'type'               => $this->smallInteger(2)->notNull()->defaultValue(0)->comment('类型：1.小帮出行 2.小帮快送'),
			'status'             => $this->smallInteger(2)->notNull()->defaultValue(1)->comment('状态: 0不可用 1可用'),
			'end_location'       => $this->string(64)->notNull()->defaultValue('')->comment('终点坐标'),
			'end_address'        => $this->string(255)->notNull()->defaultValue('')->comment('终点地方名'),
			'end_address_detail' => $this->string(255)->notNull()->defaultValue('')->comment('终点地址'),
			'create_time'        => $this->integer(10)->notNull()->defaultValue(0)->comment('创建时间'),
		], $tableOptions);
		$this->createIndex('idx_user_id', '{{%history_address}}', 'user_id');
		$this->createIndex('idx_end_address', '{{%history_address}}', 'end_address');
	}

	/**
	 * @inheritdoc
	 */
	public function down()
	{
		$this->dropTable('{{%history_address}}');
	}
}
