<?php

use yii\db\Migration;

/**
 * Class m180626_032823_update_wy_token
 */
class m180626_032823_update_wy_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE wy_token modify column type int(4) DEFAULT NULL comment '1:注册 2:找回密码 3:支付密码 4:快捷登录';";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m180626_032823_update_wy_token cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180626_032823_update_wy_token cannot be reverted.\n";

        return false;
    }
    */
}
