<?php

namespace common\models\activity;

use Yii;

/**
 * This is the model class for table "{{%market_relation}}".
 *
 * @property int $id 主表ID
 * @property int $user_id 用户ID
 * @property int $provider_id 小帮ID
 * @property int $type 类型 1用户 2小帮
 * @property int $market_user_id 受益人用户ID
 * @property string $remark 备注
 * @property string $sum_amount 总收益
 * @property int $status 状态 1正常 2解除关系
 * @property int $rel_from 关系来源 1用户 2小帮 3其他
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class MarketRelation extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%market_relation}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['user_id', 'provider_id', 'type', 'market_user_id', 'status', 'rel_from', 'create_time', 'update_time'], 'integer'],
			[['sum_amount'], 'number'],
			[['remark'], 'string', 'max' => 255],
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
			'provider_id' => Yii::t('app', '小帮ID'),
			'type' => Yii::t('app', '类型 1用户 2小帮'),
			'market_user_id' => Yii::t('app', '受益人用户ID'),
			'remark' => Yii::t('app', '备注'),
			'sum_amount' => Yii::t('app', '总收益'),
			'status' => Yii::t('app', '状态 1正常 2解除关系'),
			'rel_from' => Yii::t('app', '推荐来源 1用户 2小帮 3其他'),
			'create_time' => Yii::t('app', '创建时间'),
			'update_time' => Yii::t('app', '更新时间'),
		];
	}
}