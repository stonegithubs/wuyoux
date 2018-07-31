<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%activity_flag}}".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $type 活动类型：1.货车加油
 * @property int $flag 标记次数
 * @property string $remark 备注
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class ActivityFlag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%activity_flag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'type', 'flag', 'create_time', 'update_time'], 'integer'],
            [['remark'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'type' => Yii::t('app', '活动类型：1.货车加油'),
            'flag' => Yii::t('app', '标记次数'),
            'remark' => Yii::t('app', '备注'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
