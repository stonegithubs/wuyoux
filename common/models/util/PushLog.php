<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "wy_push_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $provider_id
 * @property string $role
 * @property integer $app_type
 * @property string $app_version
 * @property string $client_id
 * @property string $tag
 * @property string $task_id
 * @property integer $status
 * @property string $data
 * @property integer $create_time
 */
class PushLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wy_push_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'provider_id'], 'required'],
            [['user_id', 'provider_id', 'app_type', 'status', 'create_time'], 'integer'],
            [['data'], 'string'],
            [['role'], 'string', 'max' => 32],
            [['app_version', 'client_id'], 'string', 'max' => 64],
            [['tag', 'task_id'], 'string', 'max' => 255],
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
            'provider_id' => Yii::t('app', '小帮ID'),
            'role' => Yii::t('app', '角色：user,provider'),
            'app_type' => Yii::t('app', 'APP来源：1.安卓 2.IOS'),
            'app_version' => Yii::t('app', 'APP版本'),
            'client_id' => Yii::t('app', 'ClientID'),
            'tag' => Yii::t('app', '标签'),
            'task_id' => Yii::t('app', '推送任务ID'),
            'status' => Yii::t('app', '推送状态：0失败 1成功'),
            'data' => Yii::t('app', '推送内容'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
}
