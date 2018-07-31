<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%activity_gift_record}}".
 *
 * @property int $id 主表ID
 * @property int $user_id 用户ID
 * @property int $provider_id 小帮ID
 * @property int $role 用户类型 2.用户 3.小帮
 * @property int $activity_id 活动ID
 * @property int $gift_id 礼包ID
 * @property string $order_no 订单号
 * @property string $package_amount 红包金额
 * @property string $card_ids 卡券ID(红包ID)逗号隔开
 * @property string $card_amount 优惠券总金额
 * @property int $status 状态 0.生成 1.领取
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class ActivityGiftRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%activity_gift_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'provider_id', 'role', 'activity_id', 'gift_id', 'status', 'create_time', 'update_time'], 'integer'],
            [['gift_id', 'order_no', 'create_time'], 'required'],
            [['package_amount', 'card_amount'], 'number'],
            [['order_no'], 'string', 'max' => 32],
            [['card_ids'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'provider_id' => Yii::t('app', '小帮ID'),
            'role' => Yii::t('app', '用户类型 2.用户 3.小帮'),
            'activity_id' => Yii::t('app', '活动ID'),
            'gift_id' => Yii::t('app', '礼包ID'),
            'order_no' => Yii::t('app', '订单号'),
            'package_amount' => Yii::t('app', '红包金额'),
            'card_ids' => Yii::t('app', '卡券ID(红包ID)逗号隔开'),
            'card_amount' => Yii::t('app', '优惠券总金额'),
            'status' => Yii::t('app', '状态 0.生成 1.领取'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
