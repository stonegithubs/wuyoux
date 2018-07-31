<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property integer $order_id
 * @property integer $user_id
 * @property string $order_no
 * @property integer $cate_id
 * @property integer $city_id
 * @property integer $area_id
 * @property integer $region_id
 * @property integer $order_type
 * @property integer $order_from
 * @property integer $order_status
 * @property integer $payment_id
 * @property integer $robbed
 * @property integer $payment_status
 * @property string $order_amount
 * @property string $amount_payable
 * @property string $discount
 * @property string $provider_estimate_amount
 * @property string $provider_actual_amount
 * @property integer $card_id
 * @property string $online_money
 * @property integer $provider_id
 * @property integer $appoint_provider_id
 * @property string $user_mobile
 * @property string $provider_mobile
 * @property string $user_location
 * @property string $user_address
 * @property string $provider_location
 * @property string $provider_address
 * @property string $start_location
 * @property string $start_address
 * @property string $end_location
 * @property string $end_address
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $cancel_time
 * @property integer $robbed_time
 * @property integer $payment_time
 * @property integer $finish_time
 * @property integer $request_cancel_id
 * @property integer $request_cancel_time
 * @property integer $user_deleted
 * @property integer $provider_deleted
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_no'], 'required'],
            [['user_id', 'cate_id', 'city_id', 'area_id', 'region_id', 'order_type', 'order_from', 'order_status', 'payment_id', 'robbed', 'payment_status', 'card_id', 'provider_id', 'appoint_provider_id', 'create_time', 'update_time', 'cancel_time', 'robbed_time', 'payment_time', 'finish_time', 'request_cancel_id', 'request_cancel_time', 'user_deleted', 'provider_deleted'], 'integer'],
            [['order_amount', 'amount_payable', 'discount', 'provider_estimate_amount', 'provider_actual_amount', 'online_money'], 'number'],
            [['order_no'], 'string', 'max' => 32],
            [['user_mobile', 'provider_mobile'], 'string', 'max' => 24],
            [['user_location', 'provider_location', 'start_location', 'end_location'], 'string', 'max' => 64],
            [['user_address', 'provider_address', 'start_address', 'end_address'], 'string', 'max' => 255],
            [['order_no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'user_id' => Yii::t('app', '下单用户ID'),
            'order_no' => Yii::t('app', '订单号'),
            'cate_id' => Yii::t('app', '分类ID'),
            'city_id' => Yii::t('app', '城市id'),
            'area_id' => Yii::t('app', '镇区id'),
            'region_id' => Yii::t('app', '地区ID'),
            'order_type' => Yii::t('app', '订单类型：1.摩的专车 2.小帮快送'),
            'order_from' => Yii::t('app', '订单来源：1.android 2.IOS  3.微信'),
            'order_status' => Yii::t('app', '订单状态：1.等待支付 2.等待网关确认支付 3.用户取消 4.小帮取消 5.进行中 6.已完成 7.已评价 8.客服处理 9.平台取消'),
            'payment_id' => Yii::t('app', '支付方式：1.余额 2.银行卡 3.支付宝 4.微信'),
            'robbed' => Yii::t('app', '是否被抢：0.未抢 1已抢'),
            'payment_status' => Yii::t('app', '支付状态：1.待支付 2.已支付 3.部分退款 4.全部退款 5.待退款'),
            'order_amount' => Yii::t('app', '订单金额'),
            'amount_payable' => Yii::t('app', '实付金额'),
            'discount' => Yii::t('app', '优惠金额=卡券金额+在线宝金额+其他优惠'),
            'provider_estimate_amount' => Yii::t('app', '小帮预计收到金额'),
            'provider_actual_amount' => Yii::t('app', '小帮实际收到金额'),
            'card_id' => Yii::t('app', '用户卡券ID'),
            'online_money' => Yii::t('app', '在线宝金额'),
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
            'end_location' => Yii::t('app', '下单终点坐标经纬度[经度,纬度]'),
            'end_address' => Yii::t('app', '下单终点地址'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'cancel_time' => Yii::t('app', '取消时间'),
            'robbed_time' => Yii::t('app', '抢单时间'),
            'payment_time' => Yii::t('app', '支付时间'),
            'finish_time' => Yii::t('app', '完成时间'),
            'request_cancel_id' => Yii::t('app', '发起取消人'),
            'request_cancel_time' => Yii::t('app', '发起取消时间'),
            'user_deleted' => Yii::t('app', '用户删除：0.正常 1.删除'),
            'provider_deleted' => Yii::t('app', '小帮删除：0.正常 1.删除'),
        ];
    }

    /**
     * @inheritdoc
     * @return OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }
}
