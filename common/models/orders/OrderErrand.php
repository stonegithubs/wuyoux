<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order_errand}}".
 *
 * @property int $errand_id
 * @property int $order_id 订单ID（取决于订单主表order_id）
 * @property int $errand_status 快送状态：1.等待接单 2.小帮已接单 3.联系客服 4.正在服务 5.服务完成
 * @property int $order_distance 订单起点与终点距离（单位：米）
 * @property int $starting_distance 小帮与订单起点距离（单位：米）
 * @property int $begin_time 开始时间
 * @property string $begin_location 开始坐标经纬度[经度,纬度]
 * @property string $begin_address 开始地址
 * @property int $finish_time 完成时间
 * @property string $finish_location 完成坐标经纬度[经度,纬度]
 * @property string $finish_address 完成地址
 * @property string $total_fee 小费总额
 * @property string $first_fee 首次小费
 * @property string $service_price 服务单价
 * @property int $service_time 服务时间
 * @property int $service_qty 服务时长（单位：小时）
 * @property int $errand_type 快送类型：1.帮我买 2.帮我送 3.帮我办
 * @property string $errand_content 快送内容
 * @property string $mobile 联系电话（收货人或者其他人）
 * @property int $maybe_time 预约收货时间
 * @property int $actual_time 实际收货时间
 * @property string $cancel_type 取消类型
 */
class OrderErrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_errand}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'errand_status', 'order_distance', 'starting_distance', 'begin_time', 'finish_time', 'service_time', 'service_qty', 'errand_type', 'maybe_time', 'actual_time'], 'integer'],
            [['total_fee', 'first_fee', 'service_price'], 'number'],
            [['begin_location', 'finish_location'], 'string', 'max' => 64],
            [['begin_address', 'finish_address', 'errand_content'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 24],
            [['cancel_type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'errand_id' => Yii::t('app', 'Errand ID'),
            'order_id' => Yii::t('app', '订单ID（取决于订单主表order_id）'),
            'errand_status' => Yii::t('app', '快送状态：1.等待接单 2.小帮已接单 3.联系客服 4.正在服务 5.服务完成'),
            'order_distance' => Yii::t('app', '订单起点与终点距离（单位：米）'),
            'starting_distance' => Yii::t('app', '小帮与订单起点距离（单位：米）'),
            'begin_time' => Yii::t('app', '开始时间'),
            'begin_location' => Yii::t('app', '开始坐标经纬度[经度,纬度]'),
            'begin_address' => Yii::t('app', '开始地址'),
            'finish_time' => Yii::t('app', '完成时间'),
            'finish_location' => Yii::t('app', '完成坐标经纬度[经度,纬度]'),
            'finish_address' => Yii::t('app', '完成地址'),
            'total_fee' => Yii::t('app', '小费总额'),
            'first_fee' => Yii::t('app', '首次小费'),
            'service_price' => Yii::t('app', '服务单价'),
            'service_time' => Yii::t('app', '服务时间'),
            'service_qty' => Yii::t('app', '服务时长（单位：小时）'),
            'errand_type' => Yii::t('app', '快送类型：1.帮我买 2.帮我送 3.帮我办'),
            'errand_content' => Yii::t('app', '快送内容'),
            'mobile' => Yii::t('app', '联系电话（收货人或者其他人）'),
            'maybe_time' => Yii::t('app', '预约收货时间'),
            'actual_time' => Yii::t('app', '实际收货时间'),
            'cancel_type' => Yii::t('app', '取消类型'),
        ];
    }

    /**
     * @inheritdoc
     * @return OrderErrandQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderErrandQuery(get_called_class());
    }
}
