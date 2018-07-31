<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%biz_tmp_order}}".
 *
 * @property int $tmp_id
 * @property int $user_id 下单用户ID
 * @property string $tmp_no 临时订单号
 * @property int $cate_id 分类ID
 * @property int $city_id 城市ID
 * @property int $area_id 镇区ID
 * @property int $region_id 地区ID
 * @property int $tmp_from 订单来源：1.android 2.IOS 3.微信
 * @property int $tmp_status 订单状态：1.等待接单 2.已接单 3.配送中 4.用户取消 5.小帮取消 6.平台取消
 * @property int $robbed 是否被抢：0.未抢 1已抢
 * @property int $tmp_qty 订单数量
 * @property int $provider_id 服务提供者ID（小帮ID）
 * @property int $appoint_provider_id 指定服务提供者ID（小帮ID）接单
 * @property string $user_mobile 用户电话
 * @property string $provider_mobile 小帮电话
 * @property string $user_location 用户下单坐标经纬度[经度,纬度]
 * @property string $user_address 用户下单地址
 * @property string $provider_location 小帮接单坐标经纬度[经度,纬度]
 * @property string $provider_address 小帮接单地址
 * @property string $start_location 下单起点坐标经纬度[经度,纬度]
 * @property string $start_address 下单起点地址
 * @property string $order_ids 订单ID 以逗号隔开
 * @property int $starting_distance 小帮与订单起点距离（单位：米）
 * @property string $content 发单内容
 * @property string $batch_no 内置批次
 * @property int $order_num 下单数量
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $cancel_time 取消时间
 * @property int $robbed_time 抢单时间
 * @property string $cancel_type 取消类型
 * @property string $delivery_area 配送区域
 */
class BizTmpOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%biz_tmp_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'tmp_no'], 'required'],
            [['user_id', 'cate_id', 'city_id', 'area_id', 'region_id', 'tmp_from', 'tmp_status', 'robbed', 'tmp_qty', 'provider_id', 'appoint_provider_id', 'starting_distance', 'order_num', 'create_time', 'update_time', 'cancel_time', 'robbed_time'], 'integer'],
            [['tmp_no'], 'string', 'max' => 32],
            [['user_mobile', 'provider_mobile'], 'string', 'max' => 24],
            [['user_location', 'provider_location', 'start_location'], 'string', 'max' => 64],
            [['user_address', 'provider_address', 'start_address', 'order_ids', 'content', 'batch_no', 'cancel_type', 'delivery_area'], 'string', 'max' => 255],
            [['tmp_no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tmp_id' => Yii::t('app', 'Tmp ID'),
            'user_id' => Yii::t('app', '下单用户ID'),
            'tmp_no' => Yii::t('app', '临时订单号'),
            'cate_id' => Yii::t('app', '分类ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '镇区ID'),
            'region_id' => Yii::t('app', '地区ID'),
            'tmp_from' => Yii::t('app', '订单来源：1.android 2.IOS 3.微信'),
            'tmp_status' => Yii::t('app', '订单状态：1.等待接单 2.已接单 3.配送中 4.用户取消 5.小帮取消 6.平台取消'),
            'robbed' => Yii::t('app', '是否被抢：0.未抢 1已抢'),
            'tmp_qty' => Yii::t('app', '订单数量'),
            'provider_id' => Yii::t('app', '服务提供者ID（小帮ID）'),
            'appoint_provider_id' => Yii::t('app', '指定服务提供者ID（小帮ID）接单'),
            'user_mobile' => Yii::t('app', '用户电话'),
            'provider_mobile' => Yii::t('app', '小帮电话'),
            'user_location' => Yii::t('app', '用户下单坐标经纬度[经度,纬度]'),
            'user_address' => Yii::t('app', '用户下单地址'),
            'provider_location' => Yii::t('app', '小帮接单坐标经纬度[经度,纬度]'),
            'provider_address' => Yii::t('app', '小帮接单地址'),
            'start_location' => Yii::t('app', '下单起点坐标经纬度[经度,纬度]'),
            'start_address' => Yii::t('app', '下单起点地址'),
            'order_ids' => Yii::t('app', '订单ID 以逗号隔开'),
            'starting_distance' => Yii::t('app', '小帮与订单起点距离（单位：米）'),
            'content' => Yii::t('app', '发单内容'),
            'batch_no' => Yii::t('app', '内置批次'),
            'order_num' => Yii::t('app', '下单数量'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'cancel_time' => Yii::t('app', '取消时间'),
            'robbed_time' => Yii::t('app', '抢单时间'),
            'cancel_type' => Yii::t('app', '取消类型'),
            'delivery_area' => Yii::t('app', '配送区域'),
        ];
    }
}
