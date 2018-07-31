<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/30
 */

namespace common\helpers\utils;


use common\helpers\HelperBase;
use Yii;

class UrlHelper extends HelperBase
{

	/**
	 * 支付回调URL
	 * @param $params
	 * @return string
	 */
	public static function payNotify($params)
	{
		return Yii::$app->params['pay_domain'] . Yii::$app->urlManager->createUrl($params);
	}

	/**
	 * m站 webview功能网址
	 * @param $params
	 * @return string
	 */
	public static function webLink($params)
	{
		return Yii::$app->params['web_view_domain'] . Yii::$app->urlManager->createUrl($params);
	}

	/**
	 * 后台网址
	 */
	public static function adminDomain()
	{

		$url = 'http://admin.dev.281.com.cn';
		if (YII_ENV == "prod") {
			$url = 'http://admin.281.com.cn';
		}


		if (YII_ENV == "dev") {
			$url = 'http://admin.dev.281.com.cn';
		}

		if (YII_ENV == "beta") {
			$url = 'http://admin.beta.281.com.cn';
		}

		return $url;
	}

	public static function wxLink($params)
	{
		return Yii::$app->params['wx_domain'] . Yii::$app->urlManager->createUrl($params);
	}

	public static function localImageLink($params){
		return Yii::$app->params['local_image_domain'].$params;
	}
}