<?php

namespace common\models\agent;

use Yii;

/**
 * This is the model class for table "{{%open_platform_account}}".
 *
 * @property integer $id
 * @property string $app_id
 * @property string $app_secret
 * @property string $user_id
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 */
class OpenPlatformAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%open_platform_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'app_secret', 'user_id', 'create_time', 'update_time'], 'required'],
            [['status', 'create_time', 'update_time'], 'integer'],
            [['app_id'], 'string', 'max' => 10],
            [['app_secret', 'user_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'app_id' => Yii::t('app', 'App ID'),
            'app_secret' => Yii::t('app', 'App Secret'),
            'user_id' => Yii::t('app', 'User ID'),
            'status' => Yii::t('app', 'Status'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
    }
}
