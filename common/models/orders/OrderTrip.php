<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order_trip}}".
 *
 * @property int $trip_id
 * @property int $order_id 订单ID（取决于订单主表order_id）
 * @property int $trip_type 出行类型：1.摩的专车 2.小帮专车 3.电动车
 * @property int $trip_status 出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
 * @property string $estimate_amount 初始预计金额
 * @property string $actual_amount 行程实际金额
 * @property string $amount_ext 加价
 * @property int $estimate_distance 预估订单起点与终点距离（单位：米）
 * @property int $actual_distance 实际订单起点与终点距离（单位：米）
 * @property int $starting_distance 小帮与订单起点距离（单位：米）
 * @property string $license_plate 车牌号码
 * @property string $estimate_location 预估终点坐标
 * @property string $estimate_address 预估终点位置
 * @property int $point_time 到上车点时间
 * @property string $point_location 到上车点坐标经纬度[经度,纬度]
 * @property string $point_address 到上车点地址
 * @property int $pick_time 接客时间
 * @property string $pick_location 接客坐标经纬度[经度,纬度]
 * @property string $pick_address 接客地址
 * @property int $arrive_time 到达时间
 * @property string $arrive_location 到达坐标经纬度[经度,纬度]
 * @property string $arrive_address 到达地址
 * @property string $total_fee 打赏小费
 * @property int $fee_time 打赏时间
 * @property string $cancel_type 取消类型
 */
class OrderTrip extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_trip}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'trip_type', 'trip_status', 'estimate_distance', 'actual_distance', 'starting_distance', 'point_time', 'pick_time', 'arrive_time', 'fee_time'], 'integer'],
            [['estimate_amount', 'actual_amount', 'amount_ext', 'total_fee'], 'number'],
            [['license_plate'], 'string', 'max' => 32],
            [['estimate_location', 'point_location', 'pick_location', 'arrive_location'], 'string', 'max' => 64],
            [['estimate_address', 'point_address', 'pick_address', 'arrive_address'], 'string', 'max' => 255],
            [['cancel_type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'trip_id' => Yii::t('app', 'Trip ID'),
            'order_id' => Yii::t('app', '订单ID（取决于订单主表order_id）'),
            'trip_type' => Yii::t('app', '出行类型：1.摩的专车 2.小帮专车 3.电动车'),
            'trip_status' => Yii::t('app', '出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达'),
            'estimate_amount' => Yii::t('app', '初始预计金额'),
            'actual_amount' => Yii::t('app', '行程实际金额'),
            'amount_ext' => Yii::t('app', '加价'),
            'estimate_distance' => Yii::t('app', '预估订单起点与终点距离（单位：米）'),
            'actual_distance' => Yii::t('app', '实际订单起点与终点距离（单位：米）'),
            'starting_distance' => Yii::t('app', '小帮与订单起点距离（单位：米）'),
            'license_plate' => Yii::t('app', '车牌号码'),
            'estimate_location' => Yii::t('app', '预估终点坐标'),
            'estimate_address' => Yii::t('app', '预估终点位置'),
            'point_time' => Yii::t('app', '到上车点时间'),
            'point_location' => Yii::t('app', '到上车点坐标经纬度[经度,纬度]'),
            'point_address' => Yii::t('app', '到上车点地址'),
            'pick_time' => Yii::t('app', '接客时间'),
            'pick_location' => Yii::t('app', '接客坐标经纬度[经度,纬度]'),
            'pick_address' => Yii::t('app', '接客地址'),
            'arrive_time' => Yii::t('app', '到达时间'),
            'arrive_location' => Yii::t('app', '到达坐标经纬度[经度,纬度]'),
            'arrive_address' => Yii::t('app', '到达地址'),
            'total_fee' => Yii::t('app', '打赏小费'),
            'fee_time' => Yii::t('app', '打赏时间'),
            'cancel_type' => Yii::t('app', '取消类型'),
        ];
    }
}
