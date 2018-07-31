<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/6
 */

namespace api\modules\v2\helpers;

use common\helpers\HelperBase;
use common\models\agent\OpenPlatformAccount;
use Yii;

class OpenAuthHelper extends HelperBase
{

	const TOKEN_KEY = "open_token_key";

	public static function login($appId, $app_secret)
	{
		$result = false;
		if (empty($appId) || empty($app_secret)) return $result;

		return self::getUserId($appId, $app_secret);
	}

	/**
	 * 获取用户ID
	 * @param $appId
	 * @param $app_secret
	 * @return array|bool|null|\yii\db\ActiveRecord
	 */
	public static function getUserId($appId, $app_secret)
	{
		$userId = OpenPlatformAccount::find()->select(['user_id'])->where(['app_id' => $appId, 'app_secret' => $app_secret])->one();

		return $userId ? $userId->user_id : false;
	}


	/**
	 * 设置token
	 *
	 * @param $data
	 * @return array
	 */
	public static function setTokenData($data)
	{
		$expire = 7200;
		$token  = md5(Yii::$app->security->generateRandomString());
		Yii::$app->cache->set(self::TOKEN_KEY . $token, $data, $expire);

		$result = [
			'access_token' => $token,
			'expire'       => $expire
		];

		return $result;
	}

	/**
	 * 获取token
	 *
	 * @param $token
	 * @return bool|mixed
	 */
	public static function getToken($token)
	{
		$result = false;
		$data   = Yii::$app->cache->get(self::TOKEN_KEY . $token);
		$data ? $result = $data : null;

		return $result;
	}
}

