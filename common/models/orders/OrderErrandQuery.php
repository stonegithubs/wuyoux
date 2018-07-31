<?php

namespace common\models\orders;

/**
 * This is the ActiveQuery class for [[OrderErrand]].
 *
 * @see OrderErrand
 */
class OrderErrandQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return OrderErrand[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderErrand|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
