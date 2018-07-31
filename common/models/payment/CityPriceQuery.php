<?php

namespace common\models\payment;

/**
 * This is the ActiveQuery class for [[CityPrice]].
 *
 * @see CityPrice
 */
class CityPriceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return CityPrice[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CityPrice|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
