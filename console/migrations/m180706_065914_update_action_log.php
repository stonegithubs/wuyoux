<?php

use yii\db\Migration;

/**
 * Class m180706_065914_update_action_log
 */
class m180706_065914_update_action_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = " ALTER TABLE `bb_action_log`
ADD `data_json` text COLLATE 'utf8_general_ci' NOT NULL COMMENT '修改内容' AFTER `remark`; ";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180706_065914_update_action_log cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180706_065914_update_action_log cannot be reverted.\n";

        return false;
    }
    */
}
