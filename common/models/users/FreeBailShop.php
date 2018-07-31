<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%free_bail_shop}}".
 *
 * @property integer $id
 * @property integer $shops_id
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $status
 */
class FreeBailShop extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%free_bail_shop}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shops_id', 'start_time', 'end_time'], 'required'],
            [['shops_id', 'start_time', 'end_time', 'create_time', 'update_time', 'create_user_id', 'update_user_id', 'status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'shops_id' => Yii::t('app', '商家ID'),
            'start_time' => Yii::t('app', '免保证金开始时间'),
            'end_time' => Yii::t('app', '免保证金结束时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_user_id' => Yii::t('app', '创建人'),
            'update_user_id' => Yii::t('app', '更新人'),
            'status' => Yii::t('app', '启用状态'),
        ];
    }
}
