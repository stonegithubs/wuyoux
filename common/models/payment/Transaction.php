<?php

namespace common\models\payment;

use Yii;

/**
 * This is the model class for table "{{%transaction}}".
 *
 * @property int $id
 * @property string $ids_ref 订单ID（创建支付订单的ID）
 * @property int $payment_id 支付方式：1.余额 2.现金 3.支付宝 4.微信
 * @property string $fee 费用
 * @property string $transaction_no 支付流水号
 * @property string $trade_no 第三方平台交易流水号
 * @property int $status 状态：1.未支付 2.已支付
 * @property int $type 类型：1.订单 2.充值 3.退款 4.小费 5.话费 6.捐款
 * @property string $remark 备注
 * @property string $data 数据json格式化
 * @property string $app_id 第三方App ID
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class Transaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids_ref', 'data'], 'string'],
            [['payment_id', 'status', 'type', 'create_time', 'update_time'], 'integer'],
            [['fee'], 'number'],
            [['transaction_no', 'trade_no'], 'string', 'max' => 128],
            [['remark'], 'string', 'max' => 255],
            [['app_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ids_ref' => Yii::t('app', '订单ID（创建支付订单的ID）'),
            'payment_id' => Yii::t('app', '支付方式：1.余额 2.现金 3.支付宝 4.微信'),
            'fee' => Yii::t('app', '费用'),
            'transaction_no' => Yii::t('app', '支付流水号'),
            'trade_no' => Yii::t('app', '第三方平台交易流水号'),
            'status' => Yii::t('app', '状态：1.未支付 2.已支付'),
            'type' => Yii::t('app', '类型：1.订单 2.充值 3.退款 4.小费 5.话费 6.捐款'),
            'remark' => Yii::t('app', '备注'),
            'data' => Yii::t('app', '数据json格式化'),
            'app_id' => Yii::t('app', '第三方App ID'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return TransactionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransactionQuery(get_called_class());
    }
}
