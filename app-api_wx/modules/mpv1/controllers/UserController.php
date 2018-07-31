<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\helpers\StateCode;
use api_wx\modules\mpv1\helpers\WxUserHelper;
use common\components\Ref;
use common\helpers\payment\WalletHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\UrlHelper;

/**
 * 用户模块控制器
 * Class UserController
 * @package api_wx\modules\mpv1\controllers
 */
class UserController extends ControllerAccess
{
	/**
	 * 企业送用户首页
	 * @return array
	 */
	public function actionBizCenter()
	{
		$data = WxUserHelper::getUserInfo($this->user_id);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}

	/**
	 * 一般用户首页
	 * @return array
	 */
	public function actionUserCenter()
	{
		$data = WxUserHelper::userDataForUser($this->user_id);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();
	}
}