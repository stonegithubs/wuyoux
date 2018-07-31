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
 * 小程序企业送
 * Class MiniBizWechat
 * @package common\components\wechat
 */
class MiniBizWeChat extends WeChatBase
{
	public function init()
	{
		parent::init();
		$this->config = Yii::$app->params['MINI_BIZ_WECHAT'];
	}
}