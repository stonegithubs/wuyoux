<?php

namespace common\models\agent;

use Yii;

/**
 * This is the model class for table "{{%city_price_tmp}}".
 *
 * @property int $id 城市费率ID
 * @property int $cate_id 分类ID
 * @property int $province_id 省份ID
 * @property int $city_id 城市ID
 * @property int $area_id 区域预留ID
 * @property string $day_time 白天开始时间
 * @property int $day_time_init 白天初始时间(分钟)
 * @property string $day_time_init_price 白天初始时间价格
 * @property string $day_time_unit_price 白天超时时间，每60分钟
 * @property int $day_range_init 白天初始距离(米)
 * @property string $day_range_init_price 白天初始距离价格
 * @property string $day_range_unit_price 白天超出距离，每1000米
 * @property string $day_service_fee 白天服务费
 * @property string $day_busy_fee 白天高峰加价
 * @property string $night_time 夜间开始时间
 * @property int $night_time_init 夜间初始时间(分钟)
 * @property string $night_time_init_price 夜间初始时间价格
 * @property string $night_time_unit_price 夜间超时时间0，每60分钟
 * @property int $night_range_init 夜间初始距离(米)
 * @property string $night_range_init_price 夜间初始距离价格
 * @property string $night_range_unit_price 夜间超出距离，每1000米
 * @property string $night_service_fee 夜间服务费
 * @property string $night_busy_fee 夜间高峰加价
 * @property string $online_money_discount 在线宝抵扣
 * @property string $online_payment_discount 在线支付优惠
 * @property string $full_time_take 全职抽成
 * @property string $part_time_take 兼职抽成
 * @property int $status 执行状态 0等待审批 1允许执行 2中途终止
 * @property int $approval_id 审核ID
 * @property int $is_update 是否更新 0待更新 1开始更新 2回落更新
 * @property int $city_price_id 城市费率ID
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $start_time 开始时间
 * @property int $end_time 结束时间
 * @property int $create_user_id 创建人
 * @property int $update_user_id 更新人
 */
class CityPriceTmp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city_price_tmp}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cate_id', 'province_id', 'city_id'], 'required'],
            [['cate_id', 'province_id', 'city_id', 'area_id', 'day_time_init', 'day_range_init', 'night_time_init', 'night_range_init', 'status', 'approval_id', 'is_update', 'city_price_id', 'create_time', 'update_time', 'start_time', 'end_time', 'create_user_id', 'update_user_id'], 'integer'],
            [['day_time_init_price', 'day_time_unit_price', 'day_range_init_price', 'day_range_unit_price', 'day_service_fee', 'day_busy_fee', 'night_time_init_price', 'night_time_unit_price', 'night_range_init_price', 'night_range_unit_price', 'night_service_fee', 'night_busy_fee', 'online_money_discount', 'online_payment_discount', 'full_time_take', 'part_time_take'], 'number'],
            [['day_time', 'night_time'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '城市费率ID'),
            'cate_id' => Yii::t('app', '分类ID'),
            'province_id' => Yii::t('app', '省份ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域预留ID'),
            'day_time' => Yii::t('app', '白天开始时间'),
            'day_time_init' => Yii::t('app', '白天初始时间(分钟)'),
            'day_time_init_price' => Yii::t('app', '白天初始时间价格'),
            'day_time_unit_price' => Yii::t('app', '白天超时时间，每60分钟'),
            'day_range_init' => Yii::t('app', '白天初始距离(米)'),
            'day_range_init_price' => Yii::t('app', '白天初始距离价格'),
            'day_range_unit_price' => Yii::t('app', '白天超出距离，每1000米'),
            'day_service_fee' => Yii::t('app', '白天服务费'),
            'day_busy_fee' => Yii::t('app', '白天高峰加价'),
            'night_time' => Yii::t('app', '夜间开始时间'),
            'night_time_init' => Yii::t('app', '夜间初始时间(分钟)'),
            'night_time_init_price' => Yii::t('app', '夜间初始时间价格'),
            'night_time_unit_price' => Yii::t('app', '夜间超时时间0，每60分钟'),
            'night_range_init' => Yii::t('app', '夜间初始距离(米)'),
            'night_range_init_price' => Yii::t('app', '夜间初始距离价格'),
            'night_range_unit_price' => Yii::t('app', '夜间超出距离，每1000米'),
            'night_service_fee' => Yii::t('app', '夜间服务费'),
            'night_busy_fee' => Yii::t('app', '夜间高峰加价'),
            'online_money_discount' => Yii::t('app', '在线宝抵扣'),
            'online_payment_discount' => Yii::t('app', '在线支付优惠'),
            'full_time_take' => Yii::t('app', '全职抽成'),
            'part_time_take' => Yii::t('app', '兼职抽成'),
            'status' => Yii::t('app', '执行状态 0等待审批 1允许执行 2中途终止'),
            'approval_id' => Yii::t('app', '审核ID'),
            'is_update' => Yii::t('app', '是否更新 0待更新 1开始更新 2回落更新'),
            'city_price_id' => Yii::t('app', '城市费率ID'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'start_time' => Yii::t('app', '开始时间'),
            'end_time' => Yii::t('app', '结束时间'),
            'create_user_id' => Yii::t('app', '创建人'),
            'update_user_id' => Yii::t('app', '更新人'),
        ];
    }
}
