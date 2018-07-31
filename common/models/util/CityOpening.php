<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%city_opening}}".
 *
 * @property int $id 主表ID
 * @property int $province_id 省份ID
 * @property int $city_id 城市ID
 * @property int $area_id 区域ID
 * @property int $status 状态 1开启 2暂停
 * @property string $remark 备注
 * @property int $open_day 开城日期
 * @property int $create_time 创建时间
 * @property int $create_user_id 创建人
 * @property int $update_time 更新时间
 * @property int $update_user_id 创建人
 */
class CityOpening extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city_opening}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['province_id', 'city_id', 'area_id', 'status', 'open_day', 'create_time', 'create_user_id', 'update_time', 'update_user_id'], 'integer'],
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
            'province_id' => Yii::t('app', '省份ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域ID'),
            'status' => Yii::t('app', '状态 1开启 2暂停'),
            'remark' => Yii::t('app', '备注'),
            'open_day' => Yii::t('app', '开城日期'),
            'create_time' => Yii::t('app', '创建时间'),
            'create_user_id' => Yii::t('app', '创建人'),
            'update_time' => Yii::t('app', '更新时间'),
            'update_user_id' => Yii::t('app', '创建人'),
        ];
    }
}
