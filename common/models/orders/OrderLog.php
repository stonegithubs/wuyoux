<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order_log}}".
 *
 * @property integer $log_id
 * @property integer $order_id
 * @property string $log_key
 * @property string $log_value
 * @property string $remark
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $create_time
 */
class OrderLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'create_user_id', 'update_user_id', 'create_time'], 'integer'],
            [['log_value', 'remark'], 'string'],
            [['log_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'log_id' => Yii::t('app', 'Log ID'),
            'order_id' => Yii::t('app', '订单ID（取决于订单主表order_id）'),
            'log_key' => Yii::t('app', '日志key'),
            'log_value' => Yii::t('app', '日志key'),
            'remark' => Yii::t('app', '日志value'),
            'create_user_id' => Yii::t('app', '创建记录者(对应: bb_ucenter_member id),'),
            'update_user_id' => Yii::t('app', '更新记录者(对应: bb_ucenter_member id)'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
}
