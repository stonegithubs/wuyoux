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
 * App支付微信配置类
 * Class AppWeChat
 * @package common\components\wechat
 */
class UserAppWeChat extends WeChatBase
{

	public function init()
	{
		parent::init();
		$this->config = Yii::$app->params['USER_APP_WECHAT'];
	}

	public function getApp()
	{
		return parent::getApp();
	}
}