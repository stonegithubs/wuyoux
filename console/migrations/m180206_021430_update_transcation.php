<?php

use yii\db\Migration;

/**
 * Class m180206_021430_update_transcation
 */
class m180206_021430_update_transcation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE `wy_transaction`
ADD `app_id` varchar(32) COLLATE 'utf8_unicode_ci' NULL COMMENT '第三方App ID' AFTER `data`;";
		$this->db->createCommand($sql)->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180206_021430_update_transcation cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180206_021430_update_transcation cannot be reverted.\n";

        return false;
    }
    */
}
