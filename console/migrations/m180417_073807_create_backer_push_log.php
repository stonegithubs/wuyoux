<?php

use yii\db\Migration;

/**
 * Class m180417_073807_create_backer_push_log
 */
class m180417_073807_create_backer_push_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="管理后台-推送记录"';
		}
		//订单自动确认检测
		$this->createTable('{{%backer_push_log}}', [
			'id'          	=> $this->primaryKey()->comment('ID'),
			'type' 			=> $this->integer(10)->comment('推送类型1:短信2:消息推送'),
			'push_role'     => $this->char(30)->comment("推送用户类型(1:商家2:用户)"),
			'title'			=> $this->char(255)->comment("标题"),
			'content'		=> $this->char(255)->comment("内容"),
			'push_num'		=> $this->integer(10)->comment("应推送人数"),
			'success_num'	=> $this->integer(10)->comment("成功推送人数"),
			'admin_id'		=> $this->integer(10)->comment("操作人员"),
			'push_data'		=> $this->text()->comment("推送数据"),
			'create_time'	=> $this->integer(10)->comment("创建时间"),
			'push_time'		=> $this->integer(10)->comment("推送时间"),
			'finish_time'	=> $this->integer(10)->comment("推送完成时间"),
			'status'  		=> $this->integer(1)->comment('状态0:等待推送;1:推送成功;2:推送失败')
		], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('{{%backer_push_log}}');
        echo "m180417_073807_create_backer_push_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180417_073807_create_backer_push_log cannot be reverted.\n";

        return false;
    }
    */
}
