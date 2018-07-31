<?php

use yii\db\Migration;

class m170914_083953_update_order_fee extends Migration
{
    public function safeUp()
    {
    	$sql = "ALTER TABLE `wy_order_fee` ADD `payment_id` int(2) NULL COMMENT '支付类型';";

		$this->db->createCommand($sql)->execute();
    }

    public function safeDown()
    {
        echo "m170914_083953_update_order_fee cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170914_083953_update_order_fee cannot be reverted.\n";

        return false;
    }
    */
}
