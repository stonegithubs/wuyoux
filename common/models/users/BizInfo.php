<?php

namespace common\models\users;

use Yii;

/**
 * This is the model class for table "{{%biz_info}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $biz_name
 * @property string $biz_mobile
 * @property string $biz_address
 * @property string $biz_address_ext
 * @property string $biz_location
 * @property integer $city_id
 * @property integer $area_id
 * @property string $city_name
 * @property string $area_name
 * @property string $pic
 * @property string $introduction
 * @property string $default_content
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status
 * @property integer $tag_id
 */
class BizInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%biz_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'city_id', 'area_id', 'create_time', 'update_time', 'status', 'tag_id'], 'integer'],
            [['biz_name', 'biz_address', 'biz_address_ext', 'biz_location'], 'string', 'max' => 100],
            [['biz_mobile', 'city_name', 'area_name'], 'string', 'max' => 50],
            [['pic', 'introduction', 'default_content'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户id'),
            'biz_name' => Yii::t('app', '企业名'),
            'biz_mobile' => Yii::t('app', '企业电话'),
            'biz_address' => Yii::t('app', '企业地址'),
            'biz_address_ext' => Yii::t('app', '企业补充地址'),
            'biz_location' => Yii::t('app', '企业坐标'),
            'city_id' => Yii::t('app', '城市id'),
            'area_id' => Yii::t('app', '地区id'),
            'city_name' => Yii::t('app', '城市名'),
            'area_name' => Yii::t('app', '地区名'),
            'pic' => Yii::t('app', '图片多图用，分开'),
            'introduction' => Yii::t('app', '简介'),
            'default_content' => Yii::t('app', '默认发单内容'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '0等待审批1通过2退回'),
            'tag_id' => Yii::t('app', '标签ID'),
        ];
    }
}
