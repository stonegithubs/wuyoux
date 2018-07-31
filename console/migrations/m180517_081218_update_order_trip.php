<?php

use yii\db\Migration;

/**
 * Class m180517_081218_update_order_trip
 */
class m180517_081218_update_order_trip extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    	$sql = "ALTER TABLE `wy_order_trip`
ADD `estimate_location` varchar(64) COLLATE 'utf8_unicode_ci' NULL COMMENT '预估终点坐标' AFTER `license_plate`,
ADD `estimate_address` varchar(255) COLLATE 'utf8_unicode_ci' NULL COMMENT '预估终点位置' AFTER `estimate_location`;";

		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180517_081218_update_order_trip cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180517_081218_update_order_trip cannot be reverted.\n";

        return false;
    }
    */
}
