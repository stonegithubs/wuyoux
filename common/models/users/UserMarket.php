<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%user_market}}".
 *
 * @property int $id 主表ID
 * @property int $user_id 用户ID
 * @property string $market_code 营销号
 * @property string $earn_amount 总收益
 * @property string $freeze_amount 冻结金额
 * @property string $transfer_amount 总转出金额
 * @property string $transfer_realname 提现真实姓名
 * @property string $transfer_accountname 提现账号名称
 * @property string $transfer_account 提现账号
 * @property int $transfer_type 提现类型 1余额 2支付宝 3银行
 * @property int $status 状态 0等待审核 1正常 2封号
 * @property int $ban_time 封号时间
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class UserMarket extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_market}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'transfer_type', 'status', 'ban_time', 'create_time', 'update_time'], 'integer'],
            [['earn_amount', 'freeze_amount', 'transfer_amount'], 'number'],
            [['market_code'], 'string', 'max' => 16],
            [['transfer_realname', 'transfer_accountname', 'transfer_account'], 'string', 'max' => 255],
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
            'market_code' => Yii::t('app', '营销号'),
            'earn_amount' => Yii::t('app', '总收益'),
            'freeze_amount' => Yii::t('app', '冻结金额'),
            'transfer_amount' => Yii::t('app', '总转出金额'),
            'transfer_realname' => Yii::t('app', '提现真实姓名'),
            'transfer_accountname' => Yii::t('app', '提现账号名称'),
            'transfer_account' => Yii::t('app', '提现账号'),
            'transfer_type' => Yii::t('app', '提现类型 1余额 2支付宝 3银行'),
            'status' => Yii::t('app', '状态 0等待审核 1正常 2封号'),
            'ban_time' => Yii::t('app', '封号时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
