<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order_cancel}}".
 *
 * @property int $id
 * @property int $ids_ref 订单ID（取决于订单主表order_id）
 * @property string $content_id 取消ID
 * @property string $content 取消内容
 * @property string $remark 备注
 */
class OrderCancel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_cancel}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids_ref'], 'required'],
            [['ids_ref'], 'integer'],
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
            'content_id' => Yii::t('app', '取消ID'),
            'content' => Yii::t('app', '取消内容'),
            'remark' => Yii::t('app', '备注'),
        ];
    }
}
