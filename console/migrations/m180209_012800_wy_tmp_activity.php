<?php

use yii\db\Migration;

/**
 * Class m180209_012800_wy_tmp_activity
 */
class m180209_012800_wy_tmp_activity extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="小程序对应的token"';
		}
		//订单自动确认检测
		$this->createTable('{{%activity_flag}}', [
			'id'           => $this->primaryKey(),
			'user_id'      => $this->integer()->notNull()->comment("用户ID"),
			'type'    => $this->smallInteger(2)->comment("活动类型：1.货车加油"),
			'flag' => $this->integer(10)->comment("标记次数"),
			'remark'       => $this->string(64)->comment("备注"),
			'create_time'  => $this->integer(10)->comment("创建时间"),
			'update_time'  => $this->integer(10)->comment("更新时间"),

		], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180209_012800_wy_tmp_activity cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180209_012800_wy_tmp_activity cannot be reverted.\n";

        return false;
    }
    */
}
