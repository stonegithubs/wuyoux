<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api_worker\modules\v1\api;


use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use yii\BaseYii;
use yii\db\Query;
use Yii;


/**
 * API版本控制
 * Class ErrandBuyAPI
 * @package api_worker\modules\v1\api
 */
class SecurityAPI extends HelperBase
{
	public static function payPasswordCodeV10($mobile)
	{

		return SmsHelper::sendPayPasswordCode($mobile);
	}


	public static function payPasswordV10($user_id)
	{
		$pay_password = SecurityHelper::getBodyParam('pay_password');
		$pay_password = SecurityHelper::md5_encode($pay_password);

		return Yii::$app->db->createCommand()->update("bb_51_user", ['paypassword' => $pay_password], ['uid' => $user_id])->execute();
	}

	public static function loginPasswordV10($user_id)
	{
		$result        = false;
		$past_password = SecurityHelper::encryptPassword(SecurityHelper::getBodyParam('past_password'));
		$new_password  = SecurityHelper::encryptPassword(SecurityHelper::getBodyParam('new_password'));
		$user_info     = UserHelper::getUserInfo($user_id, 'password');
		if ($user_info) {
			if ($user_info['password'] == $past_password) {
				$result = Yii::$app->db->createCommand()->update("bb_51_user", ['password' => $new_password], ['uid' => $user_id])->execute();
			}
		}

		return $result;
	}
}