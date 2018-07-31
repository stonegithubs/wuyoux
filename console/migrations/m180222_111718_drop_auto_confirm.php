<?php

use yii\db\Migration;

/**
 * Class m180222_111718_drop_auto_confirm
 */
class m180222_111718_drop_auto_confirm extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
    	$sql = "DROP TABLE IF EXISTS `wy_order_delay_confirm`; ";
		$this->db->createCommand($sql)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180222_111718_drop_auto_confirm cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180222_111718_drop_auto_confirm cannot be reverted.\n";

        return false;
    }
    */
}
