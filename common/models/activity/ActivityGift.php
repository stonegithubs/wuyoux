<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%activity_gift}}".
 *
 * @property int $id 主表ID
 * @property int $activity_id 活动ID
 * @property double $trigger_rate 触发几率(百分比)
 * @property string $amount_section 金额区域(区间则逗号隔开)
 * @property string $card_ids 卡券ID(红包ID)逗号隔开
 */
class ActivityGift extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%activity_gift}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id'], 'integer'],
            [['trigger_rate'], 'number'],
            [['amount_section'], 'string', 'max' => 30],
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
            'activity_id' => Yii::t('app', '活动ID'),
            'trigger_rate' => Yii::t('app', '触发几率(百分比)'),
            'amount_section' => Yii::t('app', '金额区域(区间则逗号隔开)'),
            'card_ids' => Yii::t('app', '卡券ID(红包ID)逗号隔开'),
        ];
    }
}
