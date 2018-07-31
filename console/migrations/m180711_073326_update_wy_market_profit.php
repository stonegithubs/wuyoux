<?php

use yii\db\Migration;

/**
 * Class m180711_073326_update_wy_market_profit
 */
class m180711_073326_update_wy_market_profit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE wy_market_profit add profit_from smallint(2) NOT NULL DEFAULT 0 COMMENT '收益来源 0利润分佣 1红包奖励';";
		$this->db->createCommand($sql)->execute();

		$sql = "ALTER TABLE wy_market_profit add received smallint(2) NOT NULL DEFAULT 0 COMMENT '红包领取状态 0 未领取 1已领取 2活动已过期';";
		$this->db->createCommand($sql)->execute();

		$sql = "ALTER TABLE wy_market_profit add send_time int(10) DEFAULT NULL  COMMENT '红包发放时间';";
		$this->db->createCommand($sql)->execute();

		$sql = "ALTER TABLE wy_market_profit modify column create_time int(10) DEFAULT NULL comment '创建时间';";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m180711_073326_update_wy_market_profit cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_073326_update_wy_market_profit cannot be reverted.\n";

        return false;
    }
    */
}
