<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%biz_info_log}}".
 *
 * @property integer $log_id
 * @property integer $biz_id
 * @property string $log_key
 * @property string $log_value
 * @property string $remark
 * @property integer $create_time
 * @property integer $create_user_id
 */
class BizInfoLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%biz_info_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['biz_id'], 'required'],
            [['biz_id', 'create_time', 'create_user_id'], 'integer'],
            [['remark'], 'string'],
            [['log_key', 'log_value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'log_id' => Yii::t('app', 'Log ID'),
            'biz_id' => Yii::t('app', '企业送ID'),
            'log_key' => Yii::t('app', '日志key'),
            'log_value' => Yii::t('app', '日志value'),
            'remark' => Yii::t('app', '备注'),
            'create_time' => Yii::t('app', '创建时间'),
            'create_user_id' => Yii::t('app', '创建人'),
        ];
    }
}
