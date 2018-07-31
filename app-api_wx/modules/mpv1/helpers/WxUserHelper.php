<?php

namespace api_wx\modules\mpv1\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\models\users\BizInfo;
use yii\data\Pagination;
use yii\db\Query;
use Yii;

class WxUserHelper extends HelperBase
{
	const userWeiXinTbl = 'bb_51_userweixin';
	const userTbl       = 'bb_51_user';

	public static function getWalletInfo($userId)
	{
		$balance           = (new Query())->from(self::userTbl)->select('money')->where(['uid' => $userId])->one();
		$cardCount         = CouponHelper::getCardNum($userId);
		$result['balance'] = $balance ? $balance['money'] : null;
		$result['count']   = $cardCount['available'] + $cardCount['unavailable'];

		return $result;
	}

	public static function getUserInfo($userId)
	{
		$result    = false;
		$select    = ['uid', 'nickname', 'userphoto', 'mobile', 'city_id', 'money'];
		$user_data = UserHelper::getUserInfo($userId, $select);
		$biz_data  = [];
		if (count($user_data) > 0) {
			$biz_data['is_biz']      = false;
			$user_data['user_photo'] = ImageHelper::getUserPhoto($user_data['userphoto']);
			$user_data['city_name']  = RegionHelper::getAddressNameById($user_data['city_id']);
			$biz                     = BizInfo::findOne(['user_id' => $userId, 'status' => 1]);
			if ($biz) {
				$card_count                  = CouponHelper::getCardCount($userId);
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

	/**
	 * 获取用户数据 适用快送 出行
	 *
	 * @param $userId
	 * @return bool
	 */
	public static function userDataForUser($userId)
	{
		$result    = false;
		$select    = ['uid', 'nickname', 'userphoto', 'mobile', 'city_id', 'money'];
		$user_data = UserHelper::getUserInfo($userId, $select);
		if (count($user_data) > 0) {
			$user_data['user_photo'] = ImageHelper::getUserPhoto($user_data['userphoto']);
			$user_data['city_name']  = RegionHelper::getAddressNameById($user_data['city_id']);
			$result['user_data']     = $user_data;
			$result['card_count']    = CouponHelper::getCardCount($userId);
		}

		return $result;
	}

}