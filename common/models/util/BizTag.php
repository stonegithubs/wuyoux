<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%biz_tag}}".
 *
 * @property integer $id
 * @property integer $orders
 * @property string $tag_name
 * @property integer $tag_key
 * @property integer $biz_count
 * @property integer $status
 * @property string $remark
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $create_user_id
 * @property integer $update_user_id
 */
class BizTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%biz_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders', 'tag_key', 'biz_count', 'status', 'create_time', 'update_time', 'create_user_id', 'update_user_id'], 'integer'],
            [['tag_name', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orders' => 'Orders',
            'tag_name' => 'Tag Name',
            'tag_key' => 'Tag Key',
            'biz_count' => 'Biz Count',
            'status' => 'Status',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'create_user_id' => 'Create User ID',
            'update_user_id' => 'Update User ID',
        ];
    }
}
