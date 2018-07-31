<?php

use yii\db\Migration;

class m170803_085129_create_shop_photo extends Migration
{
	public function safeUp()
	{
		$sql = [];
		$sql[]
			 = "ALTER TABLE `bb_51_shops`
					ADD `identity_pic` varchar(255) NULL DEFAULT '0' COMMENT '商家证件信息IDS' AFTER `brankid`;";
		$sql[]
			 = "DROP TABLE IF EXISTS `bb_51_shop_identity_pic`;
CREATE TABLE `bb_51_shop_identity_pic` (
  `idpic_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '证件照ID',
  `shop_id` int(10) NOT NULL DEFAULT '0' COMMENT '小帮ID',
  `identity` char(30) NOT NULL DEFAULT '0' COMMENT '证件标识',
  `license_pos` int(10) NOT NULL DEFAULT '0' COMMENT '证件照正面',
  `license_opp` int(10) NOT NULL DEFAULT '0' COMMENT '证件照反面',
  `license_hand` int(10) NOT NULL DEFAULT '0' COMMENT '手持证件照',
  `detail` text NOT NULL COMMENT '证件照详情',
  `remark` varchar(255) NOT NULL COMMENT '说明',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `check` tinyint(2) NOT NULL DEFAULT '0' COMMENT '查询次数',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '状态(0未审核；1审核通过；2：审核失败)',
  PRIMARY KEY (`idpic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家证件图片存放表';";

		$sql_str = implode(' ', $sql);
		$this->db->createCommand($sql_str)->execute();
	}

	public function safeDown()
	{
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m170803_085129_create_shop_photo cannot be reverted.\n";

		return false;
	}
	*/
}
