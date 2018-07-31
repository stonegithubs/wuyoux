<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order_complaint}}".
 *
 * @property int $id
 * @property int $ids_ref 订单ID（取决于订单主表order_id）
 * @property string $content_id 投诉ID
 * @property string $content 投诉内容
 * @property int $status 处理状态：1.等待处理 2.已经处理
 * @property string $remark 备注
 */
class OrderComplaint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_complaint}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids_ref'], 'required'],
            [['ids_ref', 'status'], 'integer'],
            [['content_id', 'content', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ids_ref' => Yii::t('app', '订单ID（取决于订单主表order_id）'),
            'content_id' => Yii::t('app', '投诉ID'),
            'content' => Yii::t('app', '投诉内容'),
            'status' => Yii::t('app', '处理状态：1.等待处理 2.已经处理'),
            'remark' => Yii::t('app', '备注'),
        ];
    }
}
