<?php

namespace common\models\insurance;

use Yii;

/**
 * This is the model class for table "{{%insurance_fee}}".
 *
 * @property int $id 主表ID
 * @property int $provider_id 小帮ID
 * @property string $fee 费用
 * @property int $order_time 下单时间
 * @property int $create_time 创建时间
 */
class InsuranceFee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%insurance_fee}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_id', 'order_time', 'create_time'], 'integer'],
            [['fee'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'provider_id' => Yii::t('app', '小帮ID'),
            'fee' => Yii::t('app', '费用'),
            'order_time' => Yii::t('app', '下单时间'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
}
