<?php

use yii\db\Migration;

/**
 * Class m180419_070631_create_activity
 */
class m180419_070631_create_activity extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		//活动管理主表
		$this->createTable('{{%activity}}', [
			'id'               => $this->primaryKey()->comment('主表ID'),
			'name'             => $this->string()->null()->comment('活动名称'),
			'activity_target'  => $this->smallInteger(2)->comment('活动对象 1.所有 2.用户 3.小帮'),
			'activity_type'    => $this->integer(2)->comment("活动类型 1.礼包活动"),
			'trigger_position' => $this->smallInteger(2)->comment('触发位置 1.评价分享 2.完成订单'),
			'trigger_business' => $this->string()->defaultValue(0)->comment('触发业务 0为所有业务 其他分类ID逗号隔开'),
			'province_id'      => $this->integer(10)->null()->comment('省份ID'),
			'city_id'          => $this->integer(10)->null()->comment('城市ID'),
			'area_id'          => $this->integer(10)->null()->comment('区域ID'),
			'point_users'      => $this->text()->comment("指定店铺json数据"),
			'is_share'         => $this->integer(2)->defaultValue(0)->comment("是否分享 1.分享 0.不分享)"),
			'share_title'      => $this->string(40)->null()->comment("分享标题"),
			'share_content'    => $this->string(60)->null()->comment("分享内容"),
			'share_image_id'   => $this->integer(10)->null()->comment("分享图片ID"),
			'share_link'       => $this->string(255)->null()->comment("分享链接"),
			'begin_time'       => $this->integer(10)->null()->comment('活动开始时间'),
			'end_time'         => $this->integer(10)->null()->comment('活动结束时间'),
			'status'           => $this->smallInteger(2)->defaultValue(1)->comment('状态 1.启用 2.禁用 3.删除'),
			'create_time'      => $this->integer(10)->comment('创建时间'),
			'update_time'      => $this->integer(10)->comment('更新时间'),
			'create_user_id'   => $this->integer()->null()->comment('创建人'),
			'update_user_id'   => $this->integer()->null()->comment('更新人'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="活动管理主表"');

		//活动礼包列表
		$this->createTable('{{%activity_gift}}', [
			'id'             => $this->primaryKey()->comment('主表ID'),
			'activity_id'    => $this->integer(10)->comment('活动ID'),
			'trigger_rate'   => $this->double()->defaultValue(0)->comment('触发几率(百分比)'),
			'amount_section' => $this->string(30)->null()->comment('金额区域(区间则逗号隔开)'),
			'card_ids'       => $this->string()->null()->comment('卡券ID(红包ID)逗号隔开'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="活动礼包列表"');


		//活动礼包领取表
		$this->createTable('{{%activity_gift_record}}', [
			'id'             => $this->primaryKey()->comment('主表ID'),
			'user_id'        => $this->integer(10)->null()->comment('用户ID'),
			'provider_id'    => $this->integer(10)->null()->comment('小帮ID'),
			'role'           => $this->smallInteger(2)->null()->comment('用户类型 2.用户 3.小帮'),
			'activity_id'    => $this->integer(10)->comment('活动ID'),
			'gift_id'        => $this->integer(10)->notNull()->comment('礼包ID'),
			'order_no'       => $this->string(32)->notNull()->comment('订单号'),
			'package_amount' => $this->money(10, 2)->null()->comment('红包金额'),
			'card_ids'       => $this->string()->null()->comment('卡券ID(红包ID)逗号隔开'),
			'card_amount'    => $this->money(10, 2)->null()->comment('优惠券总金额'),
			'status'         => $this->integer(1)->defaultValue(0)->comment('状态 0.生成 1.领取'),
			'create_time'    => $this->integer(10)->notNull()->comment('创建时间'),
			'update_time'    => $this->integer(10)->null()->comment('更新时间'),
		], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="活动礼包领取表"');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('{{%activity}}');
		$this->dropTable('{{%activity_gift}}');
		$this->dropTable('{{%activity_gift_record}}');

		return true;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m180419_070631_create_activity cannot be reverted.\n";

		return false;
	}
	*/
}
