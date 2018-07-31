<?php

use yii\db\Migration;

/**
 * Class m180227_085736_update_recharge_info
 */
class m180227_085736_update_recharge_info extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$sql[] = "ALTER TABLE `wy_recharge_info` ADD `description` VARCHAR (255) NULL COMMENT '描述';";

		$sql[] = "ALTER TABLE `wy_order_errand`
CHANGE `errand_status` `errand_status` int(11) NULL COMMENT '快送状态：1.等待接单 2.小帮已接单 3.联系客服 4.正在服务 5.服务完成' AFTER `order_id`,
CHANGE `order_distance` `order_distance` int(11) NULL COMMENT '订单起点与终点距离（单位：米）' AFTER `errand_status`;";

		$sql_str = implode(' ', $sql);
		$this->db->createCommand($sql_str)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180227_085736_update_recharge_info cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180227_085736_update_recharge_info cannot be reverted.\n";

        return false;
    }
    */
}
