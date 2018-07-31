<?php

use yii\db\Migration;

/**
 * Class m180621_020444_update_bb_51_bail_thaw_record
 */
class m180621_020444_update_bb_51_bail_thaw_record extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE bb_51_bail_thaw_record add examine_time int(11) NOT NULL DEFAULT 0 COMMENT '审核时间';";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m180621_020444_update_bb_51_bail_thaw_record cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180621_020444_update_bb_51_bail_thaw_record cannot be reverted.\n";

        return false;
    }
    */
}
