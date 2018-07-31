<?php

namespace common\models\school;

use Yii;

/**
 * This is the model class for table "{{%school}}".
 *
 * @property int $id ID
 * @property int $uid 用户id
 * @property string $status 小帮已阅读内容 base新手入门 trip摩的专车 errand小帮快送 biz企业送
 */
class School extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%school}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'integer'],
            [['status'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uid' => Yii::t('app', '用户id'),
            'status' => Yii::t('app', '小帮已阅读内容 base新手入门 trip摩的专车 errand小帮快送 biz企业送'),
        ];
    }
}
