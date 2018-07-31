<?php

namespace common\models\posts;

use Yii;

/**
 * This is the model class for table "{{%posts}}".
 *
 * @property int $id ID
 * @property string $title 标题
 * @property string $main_pic 主图
 * @property string $tags 唯一标识
 * @property string $excerpt 简介
 * @property int $type 类型 1轮播图 2公告 3协议
 * @property int $sort 排序
 * @property int $province_id 省份ID
 * @property int $city_id 城市ID
 * @property int $area_id 区域ID
 * @property string $content 内容
 * @property int $status 状态 0关闭 1开启
 * @property string $remark 备注
 * @property int $is_link 是否外链 0否 1是
 * @property string $link 外链
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $create_user_id 创建用户ID (对应: bb_ucenter_member id)
 * @property int $update_user_id 更新用户ID (对应: bb_ucenter_member id)
 */
class Posts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%posts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'sort', 'province_id', 'city_id', 'area_id', 'status', 'is_link', 'create_time', 'update_time', 'create_user_id', 'update_user_id'], 'integer'],
            [['content', 'link'], 'string'],
            [['title'], 'string', 'max' => 80],
            [['main_pic'], 'string', 'max' => 200],
            [['tags'], 'string', 'max' => 40],
            [['excerpt', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', '标题'),
            'main_pic' => Yii::t('app', '主图'),
            'tags' => Yii::t('app', '唯一标识'),
            'excerpt' => Yii::t('app', '简介'),
            'type' => Yii::t('app', '类型 1轮播图 2公告 3协议'),
            'sort' => Yii::t('app', '排序'),
            'province_id' => Yii::t('app', '省份ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域ID'),
            'content' => Yii::t('app', '内容'),
            'status' => Yii::t('app', '状态 0关闭 1开启'),
            'remark' => Yii::t('app', '备注'),
            'is_link' => Yii::t('app', '是否外链 0否 1是'),
            'link' => Yii::t('app', '外链'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_user_id' => Yii::t('app', '创建用户ID (对应: bb_ucenter_member id)'),
            'update_user_id' => Yii::t('app', '更新用户ID (对应: bb_ucenter_member id)'),
        ];
    }
}
