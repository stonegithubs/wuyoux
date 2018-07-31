<?php

use yii\db\Migration;

/**
 * Class m180411_105649_update_recharge_order
 */
class m180411_105649_update_recharge_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    	$sql = "ALTER TABLE `wy_recharge_order`
CHANGE `payment_id` `payment_id` smallint(2) NULL COMMENT '支付方式：1.余额 2.银行卡 3.支付宝 4.微信' AFTER `get_amount`,
ADD `type` smallint(2) NULL COMMENT '类型：1.用户 2.企业送' AFTER `payment_id`,
CHANGE `create_time` `create_time` int(11) NULL COMMENT '创建时间' AFTER `type`,
CHANGE `update_time` `update_time` int(11) NULL COMMENT '更新时间' AFTER `create_time`;";

		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180411_105649_update_recharge_order cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180411_105649_update_recharge_order cannot be reverted.\n";

        return false;
    }
    */
}
