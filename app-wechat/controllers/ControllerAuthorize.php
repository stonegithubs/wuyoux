<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/11
 */

namespace wechat\controllers;

use common\components\ControllerBase;
use wechat\helpers\WxClientHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * ControllerAuthorize
 */
class ControllerAuthorize extends ControllerBase
{
	/**
	 * @var array 微信信息
	 */
	public $_wxInfo;

	/**
	 * @var string 微信Open ID
	 */
	public $_wxOpenId;

	/**
	 * @var string 微信Union ID
	 */
	public $_wxUnionId;

	/**
	 * @var int 用户ID
	 */
	public $_userId;

	public function init()
	{
		parent::init(); // TODO: Change the autogenerated stub


		if (!Yii::$app->mp_wechat->isAuthorized()) {
			return Yii::$app->mp_wechat->authorizeRequired()->send();    //跳转到微信授权认证
		}

		if (Yii::$app->mp_wechat->getUser()) {

			$this->_wxInfo    = ArrayHelper::toArray(Yii::$app->mp_wechat->getUser());
			$this->_wxOpenId  = Yii::$app->mp_wechat->getUser()->openId;
			$this->_wxUnionId = Yii::$app->mp_wechat->getUser()->unionId;

			$this->_userId = WxClientHelper::getUserIdByOpenId($this->_wxOpenId);
		}

		if (empty($this->_wxInfo)) {
			die("请使用微信访问或网络异常");
		}
	}
}