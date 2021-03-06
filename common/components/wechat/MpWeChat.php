<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/9/30
 */

namespace common\components\wechat;

use yoage\easywechat\WeChatBase;
use Yii;

/**
 * 微信公众号支付功能配置类
 * Class MpWeChat
 * @package common\components\wechat
 */
class MpWeChat extends WeChatBase
{
	/**
	 * @var WechatUser
	 */
//	private static $_user;

	public function init()
	{
		parent::init(); // TODO: Change the autogenerated stub
		$this->config =  Yii::$app->params['MP_WECHAT'];
	}

	public function getApp()
	{
		return parent::getApp();
	}

//	public function getUser()
//	{
//		if (!$this->isAuthorized()) {
//			return new WechatUser();
//		}
//		if (!self::$_user instanceof WechatUser) {
//			$userInfo    = Yii::$app->session->get($this->sessionParam);
//			$config      = $userInfo ? json_decode($userInfo, true) : [];
//			self::$_user = new WechatUser($config);
//		}
//
//		return self::$_user;
//	}
}