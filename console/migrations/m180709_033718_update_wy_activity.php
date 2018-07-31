<?php

use yii\db\Migration;

/**
 * Class m180709_033718_update_wy_activity
 */
class m180709_033718_update_wy_activity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE wy_activity modify column activity_type int(2) DEFAULT NULL comment '活动类型 1.礼包活动 2.全民合伙人';";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m180709_033718_update_wy_activity cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180709_033718_update_wy_activity cannot be reverted.\n";

        return false;
    }
    */
}
