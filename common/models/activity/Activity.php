<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%activity}}".
 *
 * @property int $id 主表ID
 * @property string $name 活动名称
 * @property int $activity_target 活动对象 1.所有 2.用户 3.小帮
 * @property int $activity_type 活动类型 1.礼包活动
 * @property int $trigger_position 触发位置 1.评价分享 2.完成订单
 * @property string $trigger_business 触发业务 0为所有业务 其他分类ID逗号隔开
 * @property int $province_id 省份ID
 * @property int $city_id 城市ID
 * @property int $area_id 区域ID
 * @property string $point_users 指定店铺json数据
 * @property int $is_share 是否分享 1.分享 0.不分享)
 * @property string $share_title 分享标题
 * @property string $share_content 分享内容
 * @property int $share_image_id 分享图片ID
 * @property string $share_link 分享链接
 * @property int $begin_time 活动开始时间
 * @property int $end_time 活动结束时间
 * @property int $status 状态 1.启用 2.禁用 3.删除
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $create_user_id 创建人
 * @property int $update_user_id 更新人
 */
class Activity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%activity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_target', 'activity_type', 'trigger_position', 'province_id', 'city_id', 'area_id', 'is_share', 'share_image_id', 'begin_time', 'end_time', 'status', 'create_time', 'update_time', 'create_user_id', 'update_user_id'], 'integer'],
            [['point_users'], 'string'],
            [['name', 'trigger_business', 'share_link'], 'string', 'max' => 255],
            [['share_title'], 'string', 'max' => 40],
            [['share_content'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'name' => Yii::t('app', '活动名称'),
            'activity_target' => Yii::t('app', '活动对象 1.所有 2.用户 3.小帮'),
            'activity_type' => Yii::t('app', '活动类型 1.礼包活动'),
            'trigger_position' => Yii::t('app', '触发位置 1.评价分享 2.完成订单'),
            'trigger_business' => Yii::t('app', '触发业务 0为所有业务 其他分类ID逗号隔开'),
            'province_id' => Yii::t('app', '省份ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域ID'),
            'point_users' => Yii::t('app', '指定店铺json数据'),
            'is_share' => Yii::t('app', '是否分享 1.分享 0.不分享)'),
            'share_title' => Yii::t('app', '分享标题'),
            'share_content' => Yii::t('app', '分享内容'),
            'share_image_id' => Yii::t('app', '分享图片ID'),
            'share_link' => Yii::t('app', '分享链接'),
            'begin_time' => Yii::t('app', '活动开始时间'),
            'end_time' => Yii::t('app', '活动结束时间'),
            'status' => Yii::t('app', '状态 1.启用 2.禁用 3.删除'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_user_id' => Yii::t('app', '创建人'),
            'update_user_id' => Yii::t('app', '更新人'),
        ];
    }
}
