<?php

use yii\db\Migration;

/**
 * Class m171123_073115_bb_card
 */
class m171123_073115_bb_card extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE `bb_card` ADD `deduct_money` decimal(10,2) DEFAULT 0.00 NOT NULL COMMENT '每张扣除金额';";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171123_073115_bb_card cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171123_073115_bb_card cannot be reverted.\n";

        return false;
    }
    */
}
