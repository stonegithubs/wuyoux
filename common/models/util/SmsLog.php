<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%sms_log}}".
 *
 * @property int $id 主表ID
 * @property string $mobile 手机号码
 * @property string $content 内容
 * @property string $tpl 模板
 * @property int $status 状态 1触发成功 2触发失败 3其他
 * @property string $result 触发结果
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class SmsLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['status', 'create_time', 'update_time'], 'integer'],
            [['mobile'], 'string', 'max' => 11],
            [['tpl', 'result'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'mobile' => Yii::t('app', '手机号码'),
            'content' => Yii::t('app', '内容'),
            'tpl' => Yii::t('app', '模板'),
            'status' => Yii::t('app', '状态 1触发成功 2触发失败 3其他'),
            'result' => Yii::t('app', '触发结果'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
