<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/8/18
 */

namespace api\modules\v1\api;

use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use Yii;


/**
 * API版本控制
 * Class ErrandBuyAPI
 * @package api\modules\v1\api
 */
class SecurityAPI extends HelperBase
{
	/**支付密码验证码
	 * @param $mobile 手机号码
	 * @return bool
	 */
	public static function payPasswordCodeV10($mobile)
	{
		if (empty($mobile)) {
			return false;
		}

		return SmsHelper::sendPayPasswordCode($mobile);
	}

	/**找回密码验证码
	 * @param $mobile
	 * @return bool
	 */
	public static function findPasswordCodeV10($mobile)
	{
		if (empty($mobile)) {
			return false;
		}

		return SmsHelper::sendFindPasswordCode($mobile);
	}

	/**注册验证码
	 * @param $mobile
	 * @return bool
	 */
	public static function signUpCode($mobile)
	{
		if (empty($mobile)) {
			return false;
		}

		return SmsHelper::sendSignUpCode($mobile);
	}

	/**支付密码
	 * @param $user_id
	 * @return int
	 */
	public static function payPasswordV10($user_id)
	{
		$pay_password = SecurityHelper::getBodyParam('pay_password');
		$pay_password = SecurityHelper::md5_encode($pay_password);

		return Yii::$app->db->createCommand()->update("bb_51_user", ['paypassword' => $pay_password], ['uid' => $user_id])->execute();
	}

	/**修改登陆密码(登陆后的)
	 * @param $user_id
	 * @return bool|int
	 */
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

	/**修改登陆密码（登陆前）
	 * @param $params
	 * @return int
	 */
	public static function findPasswordV10($params)
	{
		return UserHelper::findPassword($params);
	}

	/**注册
	 * @param $params
	 * @return int
	 */
	public static function signUpV10($params)
	{

		$insert_data = [
			'nickname'     => '帮帮用户',
			'mobile'       => $params['mobile'],
			'password'     => SecurityHelper::encryptPassword($params['password']),
			'city_id'      => RegionHelper::getRegionId($params['city_name']),
			'n_location'   => $params['user_location'],
			'reg_ip'       => Yii::$app->request->getUserIP(),
			'reg_time'     => time(),
			'status'       => 1,
			'parent_id'    => isset($params['invite_id']) ? $params['invite_id'] : 0,
			'register_src' => $params['register_src'],
		];

		return Yii::$app->db->createCommand()->insert("bb_51_user", $insert_data)->execute()?\Yii::$app->db->getLastInsertID():false;
	}


}