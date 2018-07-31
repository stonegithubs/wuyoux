<?php
/**
 * Created by PhpStorm.
 * User: andywong
 * Date: 2018/3/12
 * Time: 17:41
 */

namespace common\helpers\utils;

use common\helpers\HelperBase;
use Yii;

class OssHelper extends HelperBase
{


	public static function imageCallBack()
	{
		$result = false;
		// 1.获取OSS的签名header和公钥url header
		$authorizationBase64 = "";
		$pubKeyUrlBase64     = "";
		/*
		 * 注意：如果要使用HTTP_AUTHORIZATION头，你需要先在apache或者nginx中设置rewrite，以apache为例，修改
		 * 配置文件/etc/httpd/conf/httpd.conf(以你的apache安装路径为准)，在DirectoryIndex index.php这行下面增加以下两行
			RewriteEngine On
			RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]
		 * */
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
		}
		if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL'])) {
			$pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
		}
		if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '') {
//			header("http/1.1 403 Forbidden");
			return $result;
		}

		// 2.获取OSS的签名
		$authorization = base64_decode($authorizationBase64);

		// 3.获取公钥
		$pubKeyUrl = base64_decode($pubKeyUrlBase64);
		$ch        = curl_init();
		curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$pubKey = curl_exec($ch);
		if ($pubKey == "") {
			//header("http/1.1 403 Forbidden");
			return $result;
		}

		// 4.获取回调body
		$body = file_get_contents('php://input');

		// 5.拼接待签名字符串
		$path = $_SERVER['REQUEST_URI'];
		$pos  = strpos($path, '?');
		if ($pos === false) {
			$authStr = urldecode($path) . "\n" . $body;
		} else {
			$authStr = urldecode(substr($path, 0, $pos)) . substr($path, $pos, strlen($path) - $pos) . "\n" . $body;
		}

		// 6.验证签名
		$ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
		if ($ok == 1) {
			$body      = urldecode($body);
			$imageData = explode('&', $body);
			$imageUrl  = substr($imageData[0], 9);
			$imageSize = substr($imageData[1], 5);

			return $result = self::storeImage($imageUrl, $imageSize);
		} else {
			//header("http/1.1 403 Forbidden");
			return $result;
		}
	}

	public static function storeImage($url, $size)
	{
		$result = false;
		if (empty($url) || empty($size)) {//图片地址为空
			return $result;
		}
		$type = explode('.', $url);
		$data = [
			'name'        => self::cut_str($url, '/', -1),
			'savename'    => self::cut_str($url, '/', -1),
			'savepath'    => substr($url, 0, self::charPosition($url, '/', 2)),
			'ext'         => $type[1],
			'mime'        => 'image/' . substr($url, strpos($url, '.') + 1),
			'size'        => $size,
			'md5'         => md5($url),
			'sha1'        => sha1($url),
			'url'         => $url,
			'create_time' => time(),
		];

		return $result = Yii::$app->db->createCommand()->insert("bb_51_image", $data);
	}

	/**
	 * 按照某个特定符号截取内容
	 * @param $str
	 * @param $sign
	 * @param $number
	 * @return string
	 */
	public static function cut_str($str, $sign, $number)
	{
		$array  = explode($sign, $str);
		$length = count($array);
		if ($number < 0) {
			$new_array  = array_reverse($array);
			$abs_number = abs($number);
			if ($abs_number > $length) {
				return 'error';
			} else {
				return $new_array[$abs_number - 1];
			}
		} else {
			if ($number >= $length) {
				return 'error';
			} else {
				return $array[$number];
			}
		}
	}

	/**
	 * 获取字符出现位置
	 * @param $s
	 * @param $s1
	 * @param $n
	 * @return bool|int
	 */
	public static function charPosition($s, $s1, $n)
	{
		$s = '@' . $s;
		$j = $x = $y = 0;
		for ($i = 0; $i < strlen($s); $i++) {
			if ($index = strpos($s, $s1, $y ? ($y + 1) : $y)) {
				$j++;
				if ($j == $n) {
					$x = $index;
					break;
				} else {
					$y = $index;
				}
			}
		}

		return $x - 1;
	}
}