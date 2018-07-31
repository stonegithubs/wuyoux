<?php

namespace common\models\orders;

/**
 * This is the ActiveQuery class for [[OrderFee]].
 *
 * @see OrderFee
 */
class OrderFeeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return OrderFee[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderFee|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
