<?php

use yii\db\Migration;

/**
 * Class m180710_084045_update_shop
 */
class m180710_084045_update_shop extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE `bb_51_shops`
ADD `pick_order_num` int(11) NOT NULL DEFAULT '0' COMMENT '接单数' AFTER `job_time`,
ADD `is_insurance` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否购买保险 0未购买 1已购买 2已退保' AFTER `pick_order_num`,
ADD `insurance_time` int(10) NOT NULL COMMENT '保险购买时间' AFTER `is_insurance`,
ADD `vehicle_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '交通工具 0未定义 1全部 2摩托车 3电动车' AFTER `insurance_time`,
DROP `belong_agent`;";

		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180710_084045_update_shop cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180710_084045_update_shop cannot be reverted.\n";

        return false;
    }
    */
}
