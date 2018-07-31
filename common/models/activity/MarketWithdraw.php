<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%market_withdraw}}".
 *
 * @property int $id 主表ID
 * @property int $user_id 用户ID
 * @property string $transfer_amount 转出金额
 * @property string $transfer_realname 提现真实姓名
 * @property string $transfer_accountname 提现账号名称
 * @property string $transfer_account 提现账号
 * @property string $flow_number 流水号
 * @property string $remark 备注
 * @property int $transfer_type 提现类型(1余额 2支付宝 3银行)
 * @property int $status 状态(1正常 2已处理)
 * @property int $apply_source 申请来源（1用户端 2小帮端）
 * @property int $apply_time 申请时间
 * @property int $transfer_time 提现时间
 * @property int $update_time 提现时间
 */
class MarketWithdraw extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%market_withdraw}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'transfer_type', 'status', 'apply_source', 'apply_time', 'transfer_time', 'update_time'], 'integer'],
            [['transfer_amount'], 'number'],
            [['apply_time'], 'required'],
            [['transfer_realname', 'transfer_accountname', 'transfer_account', 'flow_number', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'transfer_amount' => Yii::t('app', '转出金额'),
            'transfer_realname' => Yii::t('app', '提现真实姓名'),
            'transfer_accountname' => Yii::t('app', '提现账号名称'),
            'transfer_account' => Yii::t('app', '提现账号'),
            'flow_number' => Yii::t('app', '流水号'),
            'remark' => Yii::t('app', '备注'),
            'transfer_type' => Yii::t('app', '提现类型(1余额 2支付宝 3银行)'),
            'status' => Yii::t('app', '状态(1正常 2已处理)'),
            'apply_source' => Yii::t('app', '申请来源（1用户端 2小帮端）'),
            'apply_time' => Yii::t('app', '申请时间'),
            'transfer_time' => Yii::t('app', '提现时间'),
            'update_time' => Yii::t('app', '提现时间'),
        ];
    }
}
