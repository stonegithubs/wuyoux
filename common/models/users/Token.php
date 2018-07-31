<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%token}}".
 *
 * @property int $id
 * @property string $keys 手机
 * @property string $token 验证码
 * @property int $type 1:注册 2:找回密码 3:支付密码 4:快捷登录
 * @property int $expire 存在时间
 * @property int $create_time 创建时间
 * @property int $status 1:成功 2:失败
 */
class Token extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'expire', 'create_time', 'status'], 'integer'],
            [['keys'], 'string', 'max' => 100],
            [['token'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'keys' => Yii::t('app', '手机'),
            'token' => Yii::t('app', '验证码'),
            'type' => Yii::t('app', '1:注册 2:找回密码 3:支付密码 4:快捷登录'),
            'expire' => Yii::t('app', '存在时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '1:成功 2:失败'),
        ];
    }
}
