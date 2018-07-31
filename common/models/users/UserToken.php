<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%user_token}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $provider_id
 * @property string $role
 * @property integer $app_type
 * @property string $access_token
 * @property string $app_version
 * @property string $client_id
 * @property integer $push_type
 * @property integer $expire
 * @property integer $create_time
 * @property integer $update_time
 */
class UserToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'provider_id'], 'required'],
            [['user_id', 'provider_id', 'app_type', 'push_type', 'expire', 'create_time', 'update_time'], 'integer'],
            [['role'], 'string', 'max' => 32],
            [['access_token', 'app_version', 'client_id'], 'string', 'max' => 64],
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
            'access_token' => Yii::t('app', '授权token'),
            'app_version' => Yii::t('app', 'APP版本'),
            'client_id' => Yii::t('app', 'ClientID'),
            'push_type' => Yii::t('app', '个推还是极光'),
            'expire' => Yii::t('app', '到期时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
