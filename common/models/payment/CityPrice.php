<?php

namespace common\models\payment;

use Yii;

/**
 * This is the model class for table "{{%city_price}}".
 *
 * @property integer $id
 * @property integer $cate_id
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $area_id
 * @property string $day_time
 * @property integer $day_time_init
 * @property string $day_time_init_price
 * @property string $day_time_unit_price
 * @property string $day_range_init
 * @property string $day_range_init_price
 * @property string $day_range_unit_price
 * @property string $day_service_fee
 * @property string $day_busy_fee
 * @property string $night_time
 * @property integer $night_time_init
 * @property string $night_time_init_price
 * @property string $night_time_unit_price
 * @property string $night_range_init
 * @property string $night_range_init_price
 * @property string $night_range_unit_price
 * @property string $night_service_fee
 * @property string $night_busy_fee
 * @property string $online_money_discount
 * @property string $online_payment_discount
 * @property string $full_time_take
 * @property string $part_time_take
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $create_user_id
 * @property integer $update_user_id
 */
class CityPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cate_id', 'province_id', 'city_id'], 'required'],
            [['cate_id', 'province_id', 'city_id', 'area_id', 'day_time_init', 'night_time_init', 'status', 'create_time', 'update_time', 'create_user_id', 'update_user_id'], 'integer'],
            [['day_time_init_price', 'day_time_unit_price', 'day_range_init', 'day_range_init_price', 'day_range_unit_price', 'day_service_fee', 'day_busy_fee', 'night_time_init_price', 'night_time_unit_price', 'night_range_init', 'night_range_init_price', 'night_range_unit_price', 'night_service_fee', 'night_busy_fee', 'online_money_discount', 'online_payment_discount', 'full_time_take', 'part_time_take'], 'number'],
            [['day_time', 'night_time'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
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
            'status' => Yii::t('app', '状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_user_id' => Yii::t('app', '创建人'),
            'update_user_id' => Yii::t('app', '更新人'),
        ];
    }

    /**
     * @inheritdoc
     * @return CityPriceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CityPriceQuery(get_called_class());
    }
}
