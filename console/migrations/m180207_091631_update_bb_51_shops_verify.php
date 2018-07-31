<?php

use yii\db\Migration;

class m180207_091631_update_bb_51_shops_verify extends Migration
{
    public function safeUp()
    {
		$sql = "ALTER TABLE `bb_51_shops_verify`
CHANGE `uid` `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID' AFTER `id`,
CHANGE `order_id` `shops_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商家ID' AFTER `uid`,
CHANGE `writer` `admin_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '操作者:(1:总后台;2:代理商3:app)' AFTER `bmob_key`,
ADD `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员ID' AFTER `admin_type`,
CHANGE `verify` `verify` int(4) unsigned NOT NULL DEFAULT '0' COMMENT '类型(1:后台审核通过;2:后台审核不通过;3:一审通过;4:一审不通过;5:二审通过;6:二审不通过;7:重新审核;)' AFTER `addtime`;";
		$this->db->createCommand($sql)->execute();
    }

    public function safeDown()
    {
    	return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180207_091631_update_bb_51_shops_verify cannot be reverted.\n";

        return false;
    }
    */
}
