<?php

namespace common\models\util;

use Yii;

/**
 * This is the model class for table "{{%biz_user_district}}".
 *
 * @property int $id 主表ID
 * @property int $user_id 用户ID
 * @property string $district 地区
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class BizUserDistrict extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%biz_user_district}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_time', 'update_time'], 'integer'],
            [['district'], 'string', 'max' => 16],
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
            'district' => Yii::t('app', '地区'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
