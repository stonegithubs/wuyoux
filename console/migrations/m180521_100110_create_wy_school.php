<?php

use yii\db\Migration;

/**
 * Class m180521_100110_create_wy_school
 */
class m180521_100110_create_wy_school extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="小帮学堂表"';
		}
		//订单自动确认检测
		$this->createTable('{{%school}}', [
			'id'     => $this->primaryKey()->comment('ID'),
			'uid'    => $this->integer(10)->comment('用户id'),
			'status' => $this->text()->comment('小帮已阅读内容 base新手入门 trip摩的专车 errand小帮快送 biz企业送'),
		], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('{{%shcool}}');
        echo "m180521_100110_create_wy_school cannot be reverted.\n";

		return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180521_100110_create_wy_school cannot be reverted.\n";

        return false;
    }
    */
}
