<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/12
 */

namespace m\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\users\UserHelper;
use common\models\users\UserMarket;
use Yii;

class MarketShareHelper extends HelperBase
{
	/**
	 * 获取用户营销数据
	 * @param        $market_code
	 * @param string $select
	 * @return array|bool|null|\yii\db\ActiveRecord
	 */
	public static function getUserMarket($market_code, $select = "*")
	{
		$result     = false;
		$userMarket = UserMarket::find()->select($select)->where(['market_code' => $market_code])->asArray()->one();
		if ($userMarket) {
			$result = $userMarket;
		}

		return $result;
	}

	/**
	 * 小帮入驻
	 * @param $mobile
	 * @param $invite_mobile
	 * @return bool
	 */
	public static function enterShop($mobile, $invite_mobile)
	{
		$result   = false;
		$userInfo = UserHelper::selectUserInfo(['mobile' => $mobile]);
		$params   = [
			'uid'          => $userInfo['uid'],
			'utel'         => $mobile,
			're_mobile'    => $invite_mobile,
			'create_time'  => time(),
			'status'       => Ref::SHOP_STATUS_FAIL,
			'type_second'  => Ref::CATE_ID_FOR_ERRAND,
			'first_cateid' => Ref::CATE_ID_FOR_ERRAND
		];
		Yii::$app->db->createCommand()->insert("bb_51_shops", $params)->execute() ? $result = true : false;

		return $result;
	}
}