<?php

use yii\db\Migration;

class m180124_055214_update_bb_51_appupdate20180124 extends Migration
{
    public function safeUp()
    {
		$sql = "ALTER TABLE `bb_51_appupdate`
ADD `path_id` varchar(50) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '0' COMMENT '版本位置ID(image_id)' AFTER `versionname`,
CHANGE `url` `url` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT '更新地址' AFTER `content`;";
		$this->db->createCommand($sql)->execute();
    }

    public function safeDown()
    {
        echo "m180124_055214_update_bb_51_appupdate20180124 cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_055214_update_bb_51_appupdate20180124 cannot be reverted.\n";

        return false;
    }
    */
}
