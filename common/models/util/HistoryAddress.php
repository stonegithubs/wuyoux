<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "wy_history_address".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $type 类型：1.小帮出行 2.小帮快送
 * @property int $status 状态: 0不可用 1可用
 * @property string $end_location 终点坐标
 * @property string $end_address 终点地方名
 * @property string $end_address_detail 终点地址
 * @property int $create_time 创建时间
 */
class HistoryAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wy_history_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'status', 'create_time'], 'integer'],
            [['end_location'], 'string', 'max' => 64],
            [['end_address', 'end_address_detail'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'status' => 'Status',
            'end_location' => 'End Location',
            'end_address' => 'End Address',
            'end_address_detail' => 'End Address Detail',
            'create_time' => 'Create Time',
        ];
    }
}
