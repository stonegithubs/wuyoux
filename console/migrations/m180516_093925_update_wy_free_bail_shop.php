<?php

use yii\db\Migration;

/**
 * Class m180516_093925_update_wy_free_bail_shop
 */
class m180516_093925_update_wy_free_bail_shop extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$sql = "ALTER TABLE  wy_free_bail_shop ADD `type` tinyint(1) NULL DEFAULT 0 COMMENT '添加类型 0平台 1商家'";
		$this->db->createCommand($sql)->execute();

		$sql2 = "ALTER TABLE  bb_action_log ADD `type` tinyint(1) NULL DEFAULT 0 COMMENT '记录类型 0平台 1代理商';";
		$this->db->createCommand($sql2)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m180516_093925_update_wy_free_bail_shop cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180516_093925_update_wy_free_bail_shop cannot be reverted.\n";

        return false;
    }
    */
}
