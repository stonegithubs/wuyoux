<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/11/29
 */

namespace common\components\wechat;

use yoage\easywechat\WeChatBase;
use Yii;

/**
 *  小帮版 微信APP支付功能配置类
 * Class WorkerAppWeChat
 * @package common\components\wechat
 */
class WorkerAppWeChat extends WeChatBase
{

	public function init()
	{
		parent::init();
		$this->config = Yii::$app->params['WORKER_APP_WECHAT'];
	}

	public function getApp()
	{
		return parent::getApp();
	}
}