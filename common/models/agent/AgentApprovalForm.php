<?php

namespace common\models\agent;

use Yii;

/**
 * This is the model class for table "{{%agent_approval_form}}".
 *
 * @property int $approval_id 审批ID
 * @property int $agent_id 代理商ID
 * @property int $province_id 省份ID
 * @property int $city_id 城市ID
 * @property int $area_id 区域ID
 * @property int $approval_type 审批类型(1:修改城市费率;2:临时修改城市费率3:修改订单距离)
 * @property string $agent_remark 代理商审批备注
 * @property int $admin_id 审批人(管理员ID)
 * @property string $approval_opinion 审批意见
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $approval_time 审批时间
 * @property int $status 审批状态 0等待审批 1审批通过 2审批失败 3撤回审批
 */
class AgentApprovalForm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%agent_approval_form}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'province_id', 'city_id', 'area_id', 'approval_type', 'admin_id', 'create_time', 'update_time', 'approval_time', 'status'], 'integer'],
            [['agent_remark', 'approval_opinion'], 'string'],
            [['approval_opinion'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'approval_id' => Yii::t('app', '审批ID'),
            'agent_id' => Yii::t('app', '代理商ID'),
            'province_id' => Yii::t('app', '省份ID'),
            'city_id' => Yii::t('app', '城市ID'),
            'area_id' => Yii::t('app', '区域ID'),
            'approval_type' => Yii::t('app', '审批类型(1:修改城市费率;2:临时修改城市费率3:修改订单距离)'),
            'agent_remark' => Yii::t('app', '代理商审批备注'),
            'admin_id' => Yii::t('app', '审批人(管理员ID)'),
            'approval_opinion' => Yii::t('app', '审批意见'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'approval_time' => Yii::t('app', '审批时间'),
            'status' => Yii::t('app', '审批状态 0等待审批 1审批通过 2审批失败 3撤回审批'),
        ];
    }
}
