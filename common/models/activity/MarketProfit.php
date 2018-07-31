<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%market_profit}}".
 *
 * @property int $id 主表ID
 * @property int $order_id 订单ID
 * @property int $user_id 用户ID
 * @property int $market_user_id 收益人ID
 * @property int $type 类型 1用户 2小帮
 * @property string $amount 金额
 * @property int $city_id 城市ID
 * @property int $area_id 区域ID
 * @property int $agent_id 代理商ID
 * @property int $create_time 创建时间
 * @property int $profit_from 收益来源 0利润分佣 1红包奖励
 * @property int $received 红包领取状态 0 未领取 1已领取 2活动已过期
 * @property int $send_time 红包发放时间
 */
class MarketProfit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%market_profit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'market_user_id', 'type', 'city_id', 'area_id', 'agent_id', 'create_time', 'profit_from', 'received', 'send_time'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'order_id' => Yii::t('app', '订单ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'market_user_id' => Yii::t('app', '收益人ID'),
            'type' => Yii::t('app', '类型 1用户 2小帮'),
            'amount' => Yii::t('app', '金额'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域ID'),
            'agent_id' => Yii::t('app', '代理商ID'),
            'create_time' => Yii::t('app', '创建时间'),
            'profit_from' => Yii::t('app', '收益来源 0利润分佣 1红包奖励'),
            'received' => Yii::t('app', '红包领取状态 0 未领取 1已领取 2活动已过期'),
            'send_time' => Yii::t('app', '红包发放时间'),
        ];
    }
}
