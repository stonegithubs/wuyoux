<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order_fee}}".
 *
 * @property integer $fee_id
 * @property integer $ids_ref
 * @property integer $type
 * @property string $amount
 * @property integer $status
 * @property integer $update_time
 * @property integer $create_time
 * @property integer $payment_id
 */
class OrderFee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_fee}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids_ref'], 'required'],
            [['ids_ref', 'type', 'status', 'update_time', 'create_time', 'payment_id'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fee_id' => Yii::t('app', 'Fee ID'),
            'ids_ref' => Yii::t('app', '订单ID（取决于小费类型的表	唯一ID）'),
            'type' => Yii::t('app', '小费类型：1.订单 2.其他'),
            'amount' => Yii::t('app', '小费'),
            'status' => Yii::t('app', '支付状态：1.待支付 2.已支付'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'payment_id' => Yii::t('app', '支付类型'),
        ];
    }

    /**
     * @inheritdoc
     * @return OrderFeeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderFeeQuery(get_called_class());
    }
}
