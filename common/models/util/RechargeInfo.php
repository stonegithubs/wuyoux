<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%recharge_info}}".
 *
 * @property integer $id
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $place_no
 * @property integer $type
 * @property integer $status
 * @property integer $card_id
 * @property string  $amount_payable
 * @property string  $get_amount
 * @property string  $remark
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string  $description
 */
class RechargeInfo extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%recharge_info}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['province_id', 'city_id', 'place_no', 'type', 'status', 'card_id', 'create_time', 'update_time', 'create_user_id', 'update_user_id'], 'integer'],
			[['amount_payable', 'get_amount'], 'number'],
			[['remark', 'description'], 'string', 'max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'             => 'ID',
			'province_id'    => 'Province ID',
			'city_id'        => 'City ID',
			'place_no'       => 'Place No',
			'type'           => 'Type',
			'status'         => 'Status',
			'card_id'        => 'Card ID',
			'amount_payable' => 'Amount Payable',
			'get_amount'     => 'Get Amount',
			'remark'         => 'Remark',
			'create_time'    => 'Create Time',
			'update_time'    => 'Update Time',
			'create_user_id' => 'Create User ID',
			'update_user_id' => 'Update User ID',
			'description'    => 'Description',
		];
	}
}
