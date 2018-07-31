<?php

namespace common\models\orders;

use Yii;

/**
 * This is the model class for table "{{%recharge_order}}".
 *
 * @property int $id
 * @property int $user_id 下单用户ID
 * @property string $recharge_no 临时订单号
 * @property int $recharge_from 订单来源：1.android 2.IOS 3.微信
 * @property int $recharge_status 订单状态：1.等待支付 2.支付成功 
 * @property int $recharge_info_id 充值表id
 * @property int $card_id 优惠券id
 * @property string $order_amount 订单金额
 * @property string $amount_payable 实付金额
 * @property string $get_amount 到账金额
 * @property int $payment_id 支付方式：1.余额 2.银行卡 3.支付宝 4.微信
 * @property int $type 类型：1.用户 2.企业送
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class RechargeOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%recharge_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'recharge_no'], 'required'],
            [['user_id', 'recharge_from', 'recharge_status', 'recharge_info_id', 'card_id', 'payment_id', 'type', 'create_time', 'update_time'], 'integer'],
            [['order_amount', 'amount_payable', 'get_amount'], 'number'],
            [['recharge_no'], 'string', 'max' => 32],
            [['recharge_no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '下单用户ID',
            'recharge_no' => '临时订单号',
            'recharge_from' => '订单来源：1.android 2.IOS 3.微信',
            'recharge_status' => '订单状态：1.等待支付 2.支付成功 ',
            'recharge_info_id' => '充值表id',
            'card_id' => '优惠券id',
            'order_amount' => '订单金额',
            'amount_payable' => '实付金额',
            'get_amount' => '到账金额',
            'payment_id' => '支付方式：1.余额 2.银行卡 3.支付宝 4.微信',
            'type' => '类型：1.用户 2.企业送',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }
}
