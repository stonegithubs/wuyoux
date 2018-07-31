<?php

namespace common\models\users;

/**
 * This is the ActiveQuery class for [[BizInfo]].
 *
 * @see BizInfo
 */
class BizInfoQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return BizInfo[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BizInfo|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
