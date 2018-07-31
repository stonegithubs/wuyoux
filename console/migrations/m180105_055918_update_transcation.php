<?php

use yii\db\Migration;

/**
 * Class m180105_055918_update_transcation
 */
class m180105_055918_update_transcation extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$sql
			= "ALTER TABLE `wy_transaction`
CHANGE `ids_ref` `ids_ref` text COLLATE 'utf8_unicode_ci' NULL COMMENT '订单ID（创建支付订单的ID）' AFTER `id`;";
		$this->db->createCommand($sql)->execute();

	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m180105_055918_update_transcation cannot be reverted.\n";

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180105_055918_update_transcation cannot be reverted.\n";

		return false;
	}
	*/
}
