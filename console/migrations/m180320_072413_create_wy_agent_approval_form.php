<?php

use yii\db\Migration;

class m180320_072413_create_wy_agent_approval_form extends Migration
{
	public function safeUp()
	{
		$sql
			= "DROP TABLE IF EXISTS `wy_agent_approval_form`;
CREATE TABLE `wy_agent_approval_form` (
  `approval_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '审批ID',
  `agent_id` int(10) NOT NULL DEFAULT '0' COMMENT '代理商ID',
  `province_id` int(10) DEFAULT NULL COMMENT '省份ID',
  `city_id` int(10) DEFAULT NULL COMMENT '城市ID',
  `area_id` int(10) DEFAULT NULL COMMENT '区域ID',
  `approval_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审批类型(1:修改城市费率;2:临时修改城市费率3:修改订单距离)',
  `agent_remark` text COMMENT '代理商审批备注',
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '审批人(管理员ID)',
  `approval_opinion` text NOT NULL COMMENT '审批意见', 
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `approval_time` int(10) NOT NULL DEFAULT '0' COMMENT '审批时间',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '审批状态 0等待审批 1审批通过 2审批失败 3撤回审批',
  PRIMARY KEY (`approval_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理商业务审核主表';";

		$this->db->createCommand($sql)->execute();
	}

	public function safeDown()
	{
		$this->dropTable('{{%agent_approval_form}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180320_072413_create_wy_agent_approval_form cannot be reverted.\n";

		return false;
	}
	*/
}
