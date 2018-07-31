<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/9/30
 */

namespace common\components\wechat;

use Yii;
use yoage\easywechat\WeChatBase;

/**
 * App支付微信配置类
 * Class AppWeChat
 * @package common\components\wechat
 */
class AppWeChat extends WeChatBase
{
	public function init()
	{
		parent::init(); // TODO: Change the autogenerated stub
		$this->config =  Yii::$app->params['APP_WECHAT'];
	}

	public function getApp()
	{
		return parent::getApp(); // TODO: Change the autogenerated stub
	}
}