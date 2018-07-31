<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/25
 */

namespace common\helpers\images;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\utils\UrlHelper;
use dosamigos\qrcode\QrCode;
use Yii;
use yii\db\Query;

class ImageHelper extends HelperBase
{

	const OSS_URL  = "https://img02.281.com.cn/";
	const imageTpl = 'bb_51_image';

	const IMAGE_PATH             = "../../storage";
	const MARKET_CODE_IMAGE_PATH = "../../storage/market_code";

	/**
	 * @param $image_id
	 * @return string
	 */
	public static function getUserPhoto($image_id)
	{
		$address_url = self::OSS_URL;
		$default     = 'Uploads/51bangbang/userphotos/default.png';
		$result      = $address_url . $default;
		if ($image_id > 0) {
			$data = (new Query())->select("url")->from("bb_51_image")->where(['id' => $image_id])->one();
			if (count($data) > 0) {
				if (substr($data['url'], 2) == './') {
					$user_photo_url = substr($data['url'], 2, $data['url']);
				} else {
					$user_photo_url = $data['url'];
				}
				$result = $address_url . $user_photo_url;
			}
		}

		return $result;
	}

	/**
	 * @param $image_id
	 * @return string
	 */
	public static function getCateImageUrl($image_id)
	{
		$address_url = self::OSS_URL;
		$default     = 'Uploads/header.jpg';
		$result      = $address_url . $default;
		if ($image_id > 0) {
			$data = (new Query())->select("url")->from("bb_51_image")->where(['id' => $image_id])->one();
			if (count($data) > 0) {
				if (substr($data['url'], 2) == './') {
					$photo_url = substr($data['url'], 2, $data['url']);
				} else {
					$photo_url = $data['url'];
				}
				$result = $address_url . $photo_url;
			}
		}

		return $result;
	}

	/**
	 * 企业送头像 后续可以自定义
	 * @param $image_id
	 * @return string
	 */
	public static function getBizAvatarUrl($image_id)
	{
		$address_url = self::OSS_URL;
		$default     = 'app/default/enterprise_portrait.png';
		$result      = $address_url . $default;

		//TODO 后续修改

		return $result;
	}

	/**
	 * 获取图片标识书名
	 * @param $identity            图片标识
	 *
	 * @return mixed
	 */
	public static function getPicExplaxin($identity)
	{
		$data = [
			'DRIVER_LICENSE_PIC_POS' => '驾驶证正面照',
			'DRIVER_LICENSE_PIC_OPP' => '驾驶证反面照',
			'TRAVEL_LICENSE_PIC_POS' => '行驶证正面照',
			'TRAVEL_LICENSE_PIC_OPP' => '行驶证反面照',
			'ID_CARD_PIC_POS'        => '身份证正面照',
			'ID_CARD_PIC_OPP'        => '身份证反面照',
			'HAND_ID_CARD_PIC'       => '手持身份证照'
		];

		return $data[strtoupper($identity)];
	}


	/**
	 * 获取快送图片
	 */
	public static function setErrandImage($id, $ids_ref)
	{
		$result = false;
		if ($id > 0) {
			$time = time();
			$sql  = "update  bb_51_image  set ids_ref={$ids_ref},update_time={$time} ,type='" . Ref::IMAGE_TYPE_ERRAND . "' where id=" . $id;
			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("handleCenterDiscount ");
		}

		return $result;
	}

	/**
	 * 获取快送图片
	 */
	public static function getErrandImageUrlByIdRef($ids_ref, $type = Ref::IMAGE_TYPE_ERRAND)
	{

		return false;    //去掉快送拍照功能 2017-11-08

		$result      = false;
		$address_url = self::OSS_URL;
		$data        = (new Query())->from(self::imageTpl)->select("url")->where(['ids_ref' => $ids_ref, 'type' => $type])->orderBy("update_time")->one();

		if ($data) {
			$result = $address_url . $data['url'];
		}

		return $result;
	}

	public static function getFigureUrl($img_id_url)
	{
		$address_url = self::OSS_URL;
		$default     = 'Public/Admin/images/placeholder.png';
		$result      = $address_url . $default;
		if (empty($img_id_url)) return $result;

		if (!is_numeric($img_id_url)) $image_path = $img_id_url;

		if (is_numeric($img_id_url)) {
			$image = (new Query())->select("url")->from("bb_51_image")->where(['id' => $img_id_url])->one();
			if ($image) $image_path = $image['url'];
		}

		if (isset($image_path)) {

			if (substr($image_path, 0, 2) == './') $image_path = substr($image_path, 2, strlen($image_path));

			if (substr($image_path, 0, 1) == '/') $image_path = substr($image_path, 1, strlen($image_path));

			$result = $address_url . $image_path;
		}

		return $result;
	}

	/**
	 * 阿里云OSS图片上传
	 * @return bool|string
	 */
	public static function AliyunOssCallback()
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
			Yii::$app->debug->log_info("ok_2018_1", 1);

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

			Yii::$app->debug->log_info("ok_2018_2", 'curl');

			return $result;
		}

		// 4.获取回调body
		$body = file_get_contents('php://input');

		Yii::$app->debug->log_info("ok_2018", $body);
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

			$result = self::saveImage($imageUrl, $imageSize);
		}

		return $result;
	}

	/**
	 * @param $url
	 * @param $size
	 * @return bool|string
	 */
	public static function saveImage($url, $size)
	{
		$imageId = false;
		if (empty($url) || empty($size)) {//图片地址为空
			return $imageId;
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

		$insert_res = Yii::$app->db->createCommand()->insert(self::imageTpl, $data)->execute();
		if ($insert_res) {
			$imageId = Yii::$app->db->getLastInsertID();
		}

		return $imageId;
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

	/**
	 * 获取二维码信息
	 * @param string $marketCode 全民营销二维码
	 * @param string $userName 用户名称
	 * @param string $type 二维码类型(2.用户 3.小帮)
	 * @return mixed
	 */
	public static function getMarketCode($marketCode, $userName, $type)
	{
		$userType           = ($type == 2) ? "user" : "provider";
		$entrance           = ($type == 2) ? 1 : 2;
		$size               = 10;
		$showName           = "show_{$userType}_{$marketCode}_{$size}";
		$downloadName       = "download_{$userType}_{$marketCode}";
		$url                = UrlHelper::webLink(['market-share/register', 'market_code' => $marketCode, 'entrance' => $entrance]);
		$show               = self::getMarketShowCode($showName, $url, $size);
		$result['show']     = $show;
		$result['download'] = self::getMarketDownloadCode($show, $downloadName, $userName);

		return $result;
	}

	/**
	 * 获取展示二维码
	 * @param $name
	 * @param $url
	 * @param $size
	 * @return string
	 */
	public static function getMarketShowCode($name, $url, $size)
	{
		$patch = "../../storage/market_code/{$name}.png";
		if (!file_exists($patch)) {
			QrCode::png($url, $patch, 3, $size, 1, true);
			ob_clean();//清除二维码打印内容
		}

		return UrlHelper::localImageLink("/market_code/{$name}.png");
	}

	/**
	 * 获取全民营销下载二维码
	 * @param $codeUrl
	 * @param $downloadName
	 * @param $userName
	 * @return string
	 */
	public static function getMarketDownloadCode($codeUrl, $downloadName, $userName)
	{
		$basePath  = self::MARKET_CODE_IMAGE_PATH;
		$imagePath = self::IMAGE_PATH;
		$template  = "{$imagePath}/common/download_bk.png";
		$font      = "{$imagePath}/common/PingFang Medium.ttf"; //定义字体

		//合成带logo的二维码图片跟 模板图片--------------start
		$path_1  = $template;
		$path_2  = $codeUrl;
		$image_1 = imagecreatefrompng($path_1);

		//定义文字
		$textcolor     = imagecolorallocate($image_1, 255, 236, 216); //设置水印字体颜色
		$userTextColor = imagecolorallocate($image_1, 250, 239, 3); //设置水印字体颜色
		imagettftext($image_1, 28, 0, 110, 150, $textcolor, $font, "扫一扫，赚大钱");
		imagettftext($image_1, 28, 0, 110, 210, $userTextColor, $font, $userName);
		imagettftext($image_1, 28, 0, 270, 210, $textcolor, $font, "邀您加入全民合伙人");

		$image_2 = imagecreatefrompng($path_2);
		$image_3 = imageCreatetruecolor(imagesx($image_1), imagesy($image_1));
		imagecopyresampled($image_3, $image_1, 0, 0, 0, 0, imagesx($image_1), imagesy($image_1), imagesx($image_1), imagesy($image_1));
		//设置二维码位置
		imagecopymerge($image_3, $image_2, 120, 265, 0, 0, imagesx($image_2), imagesy($image_2), 100);
		//合成带logo的二维码图片跟 模板图片--------------end


		//输出到本地文件夹
		$EchoPath = "{$basePath}/{$downloadName}.png";
		imagepng($image_3, $EchoPath);
		imagedestroy($image_3);

		//返回生成的路径

		return UrlHelper::localImageLink("/market_code/{$downloadName}.png");
	}
}