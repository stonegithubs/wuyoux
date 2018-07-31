<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%region}}".
 *
 * @property integer $region_id
 * @property integer $parent_id
 * @property string $region_name
 * @property string $spelling_name
 * @property integer $level
 * @property integer $agency_id
 * @property integer $city_num
 * @property string $path_name
 * @property string $path
 * @property string $code
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%region}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'level', 'agency_id', 'city_num'], 'integer'],
            [['city_num'], 'required'],
            [['region_name'], 'string', 'max' => 120],
            [['spelling_name'], 'string', 'max' => 40],
            [['path_name'], 'string', 'max' => 100],
            [['path'], 'string', 'max' => 60],
            [['code'], 'string', 'max' => 12],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'region_id' => Yii::t('app', '城市_ID'),
            'parent_id' => Yii::t('app', '父类ID'),
            'region_name' => Yii::t('app', '城市_中文名'),
            'spelling_name' => Yii::t('app', '城市_拼音名'),
            'level' => Yii::t('app', '城市级别(1:一级,2:二级;3:三级)'),
            'agency_id' => Yii::t('app', '地区代理ID'),
            'city_num' => Yii::t('app', '城市编码'),
            'path_name' => Yii::t('app', 'Path Name'),
            'path' => Yii::t('app', 'Path'),
            'code' => Yii::t('app', '行政编码'),
        ];
    }

    /**
     * @inheritdoc
     * @return RegionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RegionQuery(get_called_class());
    }
}
