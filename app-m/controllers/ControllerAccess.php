<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/5
 */

namespace m\controllers;

use common\components\ControllerBase;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use Yii;

class ControllerAccess extends ControllerWeb
{
	public $user_id;        //用户ID
	public $provider_id;    //小帮ID

	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {

			if ($this->whiteList()) {    //不用授权 白名单
				return true;
			}

			//首次获取session_key作为整个过程授权
			$session_key = Yii::$app->request->get("session_key");
			$sessionData = SecurityHelper::getSessionKey($session_key);	//第一次数据来自session
			$secondData  = $sessionData ? $sessionData : UserHelper::getUserToken($session_key); //第二次数据来自APP登录的access_token
			$data        = $secondData ? $secondData : Yii::$app->session->get("m_session_key");

			if ($data) {
				$this->user_id     = $data['user_id'];
				$this->provider_id = $data['provider_id'];
				Yii::$app->session->set("m_session_key", $data);

			} else {

				$targetUrl = Yii::$app->urlManager->createUrl(['/site/error']);
				header('location:' . $targetUrl);

				return false;
			}
		}

		return true;
	}

	//白名单
	public function whiteList()
	{
		$result     = false;
		$actionList = [
			'site/demo',
			'activity/provider-activity-list',
			'activity/user-activity-list',
			'activity/get-activity-list'
		];

		if (in_array($this->action->getUniqueId(), $actionList)) {
			$result = true;
		}

		return $result;
	}
}
