<?php

namespace common\models\push;

use Yii;

/**
 * This is the model class for table "{{%backer_push_log}}".
 *
 * @property int $id ID
 * @property int $type 推送类型1:短信2:消息推送3:活动推送
 * @property string $push_role 推送用户类型(1:商家2:用户)
 * @property string $title 标题
 * @property string $content 内容
 * @property int $push_num 推送人数
 * @property int $success_num 推送成功数
 * @property int $admin_id 操作人员
 * @property string $push_data 推送数据
 * @property int $create_time 创建时间
 * @property int $push_time 推送时间
 * @property int $finish_time 推送完成时间
 * @property int $status 状态0:等待推送;1:推送成功;2:推送失败
 */
class BackerPushLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%backer_push_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'push_num', 'success_num', 'admin_id', 'create_time', 'push_time', 'finish_time', 'status'], 'integer'],
            [['title', 'content', 'create_time'], 'required'],
            [['push_data'], 'string'],
            [['push_role'], 'string', 'max' => 30],
            [['title', 'content'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'push_role' => Yii::t('app', 'Push Role'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'push_num' => Yii::t('app', 'Push Num'),
            'success_num' => Yii::t('app', 'Success Num'),
            'admin_id' => Yii::t('app', 'Admin ID'),
            'push_data' => Yii::t('app', 'Push Data'),
            'create_time' => Yii::t('app', 'Create Time'),
            'push_time' => Yii::t('app', 'Push Time'),
            'finish_time' => Yii::t('app', 'Finish Time'),
            'status' => Yii::t('app', 'Status'),
        ];
    }
}
