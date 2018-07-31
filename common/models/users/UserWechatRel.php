<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%user_wechat_rel}}".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $app_type 类型：1.企业送小程序 2.快送小程序 3.摩的小程序 4.公众号
 * @property string $access_token 授权token
 * @property string $openid openid
 * @property string $unionid unionid
 * @property string $session_key 小程序session_key
 * @property int $expire 到期时间
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class UserWechatRel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_wechat_rel}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'app_type', 'expire', 'create_time', 'update_time'], 'integer'],
            [['access_token', 'openid', 'unionid', 'session_key'], 'string', 'max' => 64],
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
            'app_type' => Yii::t('app', '类型：1.企业送小程序 2.快送小程序 3.摩的小程序 4.公众号'),
            'access_token' => Yii::t('app', '授权token'),
            'openid' => Yii::t('app', 'openid'),
            'unionid' => Yii::t('app', 'unionid'),
            'session_key' => Yii::t('app', '小程序session_key'),
            'expire' => Yii::t('app', '到期时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
