<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%biz_agent_district}}".
 *
 * @property int $id 主表ID
 * @property int $city_id 城市ID
 * @property int $area_id 区域ID
 * @property string $district_list 地区列表,用逗号隔开
 * @property int $agent_id 代理商ID
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class BizAgentDistrict extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%biz_agent_district}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['city_id', 'area_id', 'agent_id', 'create_time', 'update_time'], 'integer'],
            [['district_list'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主表ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域ID'),
            'district_list' => Yii::t('app', '地区列表,用逗号隔开'),
            'agent_id' => Yii::t('app', '代理商ID'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
