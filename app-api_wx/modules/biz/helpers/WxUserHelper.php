<?php

namespace api_wx\modules\biz\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\models\users\BizInfo;
use yii\db\Query;
use Yii;

class WxUserHelper extends HelperBase
{
	public static function getWalletInfo($user_id)
	{
		$balance           = (new Query())->from('bb_51_user')->select('money')->where(['uid' => $user_id])->one();
		$cardCount         = CouponHelper::getCardNum($user_id);
		$result['balance'] = $balance ? $balance['money'] : null;
		$result['count']   = $cardCount['available'] + $cardCount['unavailable'];

		return $result;
	}


	public static function getUserInfo($user_id)
	{
		$result    = false;
		$select    = ['uid', 'nickname', 'userphoto', 'mobile', 'city_id', 'money'];
		$user_data = UserHelper::getUserInfo($user_id, $select);
		$biz_data  = [];
		if (count($user_data) > 0) {
			$biz_data['is_biz']      = false;
			$user_data['user_photo'] = ImageHelper::getUserPhoto($user_data['userphoto']);
			$user_data['city_name']  = RegionHelper::getAddressNameById($user_data['city_id']);
			$biz                     = BizInfo::findOne(['user_id' => $user_id, 'status' => 1]);
			if ($biz) {
				$card_count                  = CouponHelper::getCardCount($user_id, Ref::BELONG_TYPE_BIZ);
				$biz_data['is_biz']          = true;
				$biz_data['biz_name']        = $biz->biz_name;
				$biz_data['biz_mobile']      = $biz->biz_mobile;
				$biz_data['biz_address']     = $biz->biz_address;
				$biz_data['biz_address_ext'] = $biz->biz_address_ext;
				$biz_data['biz_location']    = $biz->biz_location;
				$bix_data['default_content'] = $biz->default_content;
				$biz_data['card_count']      = $card_count;
				$biz_data['money']           = $user_data['money'];
			}
			$result['user_data'] = $user_data;
			$result['biz_data']  = $biz_data;
		}

		return $result;
	}

}