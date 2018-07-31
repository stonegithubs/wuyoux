<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/24
 */

namespace common\helpers\security;

use common\helpers\HelperBase;
use common\helpers\users\UserHelper;
use common\models\users\Token;
use Yii;
use yii\db\Exception;

class SecurityHelper extends HelperBase
{

	const SESSION_KEY     = 'session_key';
	const tokenTbl        = 'wy_token';
	const VERIFY_CODE_KEY = 'verify_code_';

	/**
	 * 防注入和XSS攻击过滤
	 *
	 * @param $arr
	 *
	 * @return array|mixed
	 */
	public static function safeFilter($arr)
	{
		$ra = ['/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '/script/', '/javascript/', '/vbscript/', '/expression/', '/applet/', '/meta/', '/xml/', '/blink/', '/link/', '/style/', '/embed/', '/object/', '/frame/', '/layer/', '/title/', '/bgsound/', '/base/', '/onload/', '/onunload/', '/onchange/', '/onsubmit/', '/onreset/', '/onselect/', '/onblur/', '/onfocus/', '/onabort/', '/onkeydown/', '/onkeypress/', '/onkeyup/', '/onclick/', '/ondblclick/', '/onmousedown/', '/onmousemove/', '/onmouseout/', '/onmouseover/', '/onmouseup/', '/onunload/'];
		if (is_array($arr)) {
			foreach ($arr as $key => $value) {
				if (!is_array($value)) {
					if (!get_magic_quotes_gpc())                    //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
					{
						$value = addslashes($value);               //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）加上反斜线转义
					}
					$value     = preg_replace($ra, '', $value);      //删除非打印字符，粗暴式过滤xss可疑字符串
					$arr[$key] = htmlentities(strip_tags($value)); //去除 HTML 和 PHP 标记并转换为 HTML 实体
				} else {
					self::SafeFilter($arr[$key]);
				}
			}
		}

		return $arr;
	}

	/**
	 * @param      $name
	 * @param null $defaultValue
	 * @return array|mixed
	 */
	public static function getBodyParam($name, $defaultValue = null)
	{
		return self::safeFilter(Yii::$app->request->getBodyParam($name, $defaultValue));
	}

	/**
	 * @param      $name
	 * @param null $defaultValue
	 * @return array|mixed
	 */
	public static function getGetParam($name, $defaultValue = null)
	{
		return self::safeFilter(Yii::$app->request->get($name, $defaultValue));
	}

	public static function getUserToken($key)
	{

		$result = false;
		$redis  = Yii::$app->redis;
		$redis->select(0);
		$data = $redis->get("userToken:" . $key);

		if ($data) {
			$data = json_decode($data, true);

			$params['is_shop']     = isset($data['is_shop']) ? $data['is_shop'] : 0;
			$params['user_id']     = isset($data['user_id']) ? $data['user_id'] : 0;
			$params['shop_id']     = isset($data['shop_id']) ? $data['shop_id'] : 0;
			$params['work_status'] = isset($data['work_status']) ? $data['work_status'] : 0;
			$params['client_id']   = isset($data['client_id']) ? $data['client_id'] : 0;

			if ($params['user_id'] > 0)
				$result = $params;
		}

		return $result;
	}

	public static function getWxToken($key)
	{
		$result = false;
		$redis  = Yii::$app->redis;
		$redis->select(0);
		$data = $redis->get("userWxToken:" . $key);

		if ($data) {
			$data              = json_decode($data, true);
			$params['user_id'] = isset($data['uid']) ? $data['uid'] : 0;
			if ($params['user_id'] > 0)
				$result = $params;
		}

		return $result;
	}

	/**
	 * 检查验证码是否有效
	 * @param $mobile
	 * @param $code
	 * @param $type
	 * @return bool
	 */
	public static function checkCode($mobile, $code, $type)
	{
		$result = false;

		$data = Token::find()->where(['keys' => $mobile, 'status' => 1, 'type' => $type])->orderBy(['create_time' => SORT_DESC])->asArray()->limit(1)->one();
		if ($data) {
			$code        = strval($code);
			$expire_time = $data['create_time'] + $data['expire'];
			if ($expire_time > time() && $data['token'] == $code) {

				Yii::$app->db->createCommand()->update(Token::tableName(), ['status' => 2], ['id' => $data['id']])->execute();
				$result = true;
			}
		}

		return $result;
	}

	//支付密码加密
	public static function md5_encode($str)
	{
		$rand      = md5(rand(1, 99999999) . time());
		$rand_code = substr($rand, 18, 8);
		$pass24    = substr(md5($str . $rand_code), 8);

		return $pass24 . $rand_code;
	}

	//密码加密
	public static function encryptPassword($pwd)
	{
		return md5(sha1($pwd));
	}

	//MD5密码验证BY user_id
	public static function verifyPayPwdBuyUserId($user_id, $password)
	{
		$result       = false;
		$user_info    = UserHelper::getUserInfo($user_id, ['paypassword']);
		$pay_password = isset($user_info['paypassword']) ? $user_info['paypassword'] : null;
		if (!empty($pay_password)) {
			$rand_code  = substr($pay_password, -8);
			$pass24_old = substr($pay_password, 0, 24);
			$pass24     = substr(md5($password . $rand_code), 8);
			if ($pass24_old == $pass24) {
				$result = true;
			}
		}

		return $result;
	}

	public static function getCode($code = '')
	{
		$code = $code ? $code : (string)rand(111111, 999999);

		return YII_ENV_PROD ? $code : '000000';
	}

	/**
	 * 存验证码表
	 * @param string $mobile 手机
	 * @param string $code 验证码
	 * @param string $type 验证码类型
	 * @return bool
	 */
	public static function saveToken($mobile, $code, $type)
	{
		$token              = new Token();
		$token->keys        = $mobile;
		$token->token       = $code;
		$token->type        = $type;
		$token->expire      = 60 * 3;
		$token->create_time = time();
		$token->status      = 1;
		$result             = $token->save() ? true : false;

		return $result;
	}

	public static function checkTestMobile($mobile)
	{
		$result    = false;
		$test_user = [
			13428288458, 13631119953, 13420027833, 13511111112,
			13701111111, 13702222222, 13711111111, 15011111114,
			18916087722, 13400805650, 13005671234, 17620151163,
			15920416322, 15011111111, 15011111112, 15011111113,
			13505555555, 13506666666, 13507777777, 13508888888,
			13509999999, 13705555555, 13232915030, 17502336308,
			13703333333, 13704444444, 15914685847, 13706666666,
			13707777777, 15999611404, 13708888888, 13709999999,
			15521131230, 13700111111, 13700222222, 13501111111,
			13502222222, 13503333333, 13504444444, 13085859822,
			13900000001, 13900000002, 13900000003, 13900000004,
			13900000005, 13900000006, 13900000007, 13900000008,
			13900000009, 13900000010, 13900000011, 13900000012,
			13900000013, 13900000014, 13900000015, 13900000016,
			13900000017, 13900000018, 13900000019, 13900000020,
			13900000021, 13900000022, 13900000023, 13900000024,
			13900000025, 13900000026, 13900000027, 13900000028,
			13900000029, 13900000030, 13900000031, 13900000032,
			13900000033, 13900000034, 13900000035, 13900000036,
			13900000037, 13900000038, 13900000039, 13900000040,
			13900000041, 13900000042, 13900000043, 13900000044,
			13900000045, 13900000046, 13900000047, 13900000048,
			13900000049, 13900000050, 15089930919, 15625313748,
			13005558952, 18576030196, 13620374556, 13432986550,
			13631120093, 13631119953, 13428288458, 18023420274,
			13501111111, 18664226125, 15099999999, 15099911641,
			13011111111, 18100000011, 18100000012, 13011111100,
			18100000013, 13078431311, 18664226121, 13700000012,
			13078431311, 18601234567, 18600001234, 18600123456,
			13549912160, 18801234567, 18800001234
		];
		if (in_array($mobile, $test_user)) {
			return true;
		}

		$test = substr($mobile, 0, 3);
		if ($test == '128') {
			return true;
		}

		return $result;
	}

	/**
	 * 设置session key
	 * @param $access_token
	 * @return string
	 */
	public static function setSessionKey($access_token)
	{
		$expire = 1800;
		$data   = UserHelper::getUserToken($access_token);
		$token  = md5(Yii::$app->security->generateRandomString());
		Yii::$app->cache->set(self::SESSION_KEY . $token, $data, $expire);

		return $token;
	}

	/**
	 * 获取session key
	 * @param $key
	 * @return bool|mixed
	 */
	public static function getSessionKey($key)
	{
		$result = false;
		$data   = Yii::$app->cache->get(self::SESSION_KEY . $key);
		$data ? $result = $data : null;

		return $result;
	}

	/**
	 * 预支付验证支付密码
	 *
	 * @param $user_id
	 * @param $pay_password
	 * @return array
	 * @throws \yii\base\Exception
	 */
	public static function payPasswordForPrePay($user_id, $pay_password)
	{
		if (YII_ENV == 'prod') {
			$cacheTime = 600;
		} else {
			$cacheTime = 60;
		}
		$errorKey       = 'error_password_count' . $user_id;
		$errorCache     = Yii::$app->cache->get($errorKey);
		$errorCount     = isset($errorCache['error_count']) ? $errorCache['error_count'] : 0; //获取输入错误次数
		$unlockedMinute = 0; //还剩多少分钟
		if (isset($errorCache['unlocked_time']) && $errorCache['unlocked_time'] > time()) {
			$unlockedTime   = $errorCache['unlocked_time'];
			$unlockedMinute = ceil(($unlockedTime - time()) / 60);  //还剩多少分钟
		}
		$result = [
			'status'          => '0',  //0是验证失败,1是验证成功
			'verify_code'     => '',
			'expire_time'     => 0,
			'error_count'     => $errorCount,
			'unlocked_minute' => $unlockedMinute,
		];
		//输入错误超过5次
		if ($errorCount >= 5) {
			return $result;
		}

		$res = self::verifyPayPwdBuyUserId($user_id, $pay_password);
		if ($res) {
			Yii::$app->cache->delete($errorKey);  //验证成功则清零
			$key                   = self::VERIFY_CODE_KEY . $user_id;
			$expire                = 300;  //有效时间;
			$result['expire_time'] = time() + $expire;
			$verify_code           = md5(Yii::$app->security->generateRandomString());
			Yii::$app->cache->set($key, $verify_code, $expire);
			$result['verify_code'] = $verify_code;
			$result['status']      = '1';
		} else {
			$errorCount++;
			$cache['error_count']  = $errorCount;
			$result['error_count'] = $errorCount;
			if ($cache['error_count'] >= 5) {
				$cache['unlocked_time']    = time() + $cacheTime;  //10分钟后解锁
				$result['unlocked_minute'] = ceil(($cache['unlocked_time'] - time()) / 60);
			}
			Yii::$app->cache->set($errorKey, $cache, $cacheTime);
		}

		return $result;
	}

	//确认支付密码
	//与上一个方法对应
	public static function confirmPayPassword($userId, $verifyCode)
	{
		$result = false;

		$key  = self::VERIFY_CODE_KEY . $userId;
		$code = Yii::$app->cache->get($key);
		if ($code && $code == $verifyCode) {
			Yii::$app->cache->delete($key);

			$result = true;
		}

		return $result;
	}


	//获取号码当天发送的验证码次数
	public static function getCodeFreq($mobile)
	{
		$time  = strtotime(date("Y-m-d"), time());
		$count = Token::find()->where(['keys' => $mobile])->andWhere(['>', 'create_time', $time])->count('*');

		return $count;
	}

	//生成验证码图片
	//返回图片地址和验证码内容
	public static function getGraphCode($mobile, $type)
	{

		$imageUrl = null;
		$code     = '';

		$key       = 'graph_code_' . $mobile . $type;
		$length    = 4;  //长度
		$dir       = 'web/images/auth/';   //存放目录
		$host      = Yii::$app->request->hostInfo; //域名
		$strToDraw = "";
		$chars     = [
			"1", "2", "3", "4",
			"5", "6", "7", "9",
			"a", "c", "d", "e", "f", "g",
			"h", "i", "j", "k", "l", "m", "n",
			"p", "q", "r", "s", "t",
			"u", "v", "w", "x", "y", "z",
			"A", "C", "D", "E", "F", "G",
			"H", "I", "J", "K", "L", "M", "N",
			"P", "Q", "R", "S", "T",
			"U", "V", "W", "X", "Y", "Z"
		];
		$count     = count($chars);
		$imgW      = 80;
		$imgH      = 25;
		$imgRes    = imagecreate($imgW, $imgH);
		$imgColor  = imagecolorallocate($imgRes, 255, 255, 255);
		$color     = imagecolorallocate($imgRes, 0, 0, 0);
		for ($i = 0; $i < $length; $i++) {
			$rand      = rand(0, $count - 1);
			$code      .= $chars[$rand];
			$strToDraw = $strToDraw . " " . $chars[$rand];
		}
		imagestring($imgRes, 5, 0, 5, $strToDraw, $color);
		for ($i = 0; $i < 100; $i++) {
			imagesetpixel($imgRes, rand(0, $imgW), rand(0, $imgH), $color);
		}
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		$name      = Yii::$app->security->generateRandomString();
		$imageName = $dir . $name . '.png';
		imagepng($imgRes, $imageName);
		imagedestroy($imgRes);

		$imageUrl = $host . '/' . $imageName;

		Yii::$app->cache->set($key, strtolower($code), 3600);

		return $imageUrl;
	}

	//确认图形验证码
	public static function confirmGraph($mobile, $type, $graphCode)
	{
		$result = 0;
		$key    = 'graph_code_' . $mobile . $type;
		$code   = Yii::$app->cache->get($key);
		if ($code) {

			if ($code != strtolower($graphCode)) {
				$result = 2; // '图形验证码不正确';
			}

			Yii::$app->cache->delete($key);
		} else {
			$result = 1; //'请获取图形验证码';
		}

		return $result;
	}

}