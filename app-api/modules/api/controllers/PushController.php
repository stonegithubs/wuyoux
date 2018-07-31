<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/23
 */

namespace api\modules\api\controllers;

use api\modules\api\blocks\PushBlock;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\wechat\MiniAppOauth;


class PushController extends ControllerAccess
{
	/**
	 * 链接推送(仅能推送给安卓)
	 */
	public function actionLink()
	{
		$params = PushBlock::getLinkParam();
		if (empty($params)) {
			$this->_code    = -200;
			$this->_message = "参数有误！";

			return $this->response();
		}

		$result = PushBlock::pushLinkToUser($params);
		if (!$result) {
			$this->_code    = -200;
			$this->_message = "推送失败！";
		} else {
			$this->_message = "推送成功！";
		}

		return $this->response();
	}


	/**
	 * 活动推送
	 */
	public function actionActivity()
	{
		$params = PushBlock::_getActivityParam();
		if (empty($params)) {
			$this->_code    = -200;
			$this->_message = "参数有误！";

			return $this->response();
		}

		$result = PushBlock::pushActivityToUserWorkFlow($params);
		if (!$result) {
			$this->_code    = -200;
			$this->_message = "推送失败！";
		} else {
			$this->_message = "推送成功！";
		}

		return $this->response();
	}

	/**
	 * 发送微信公众号订单信息模板给用户
	 */
	public function actionSendWechatDispatchMessageToUser()
	{
		$params = [
			'order_id' => SecurityHelper::getBodyParam("order_id", ""),
			'cate_id'  => SecurityHelper::getBodyParam("cate_id", ""),
			'option'   => SecurityHelper::getBodyParam("option", false),
		];
		if ($params['order_id'] && $params['cate_id']) {
			QueueHelper::sendWechatDispatchMessageToUser($params['order_id'],$params['cate_id'],$params['option']);
			$this->_message = "发送成功！";
		} else {
			$this->_code    = -200;
			$this->_message = "发送失败！";
		}

		return $this->response();
	}

	/**
	 * 发送微信公众号订单信息模板给小帮
	 */
	public function actionSendWechatDispatchMessageToProvider()
	{
		$params = [
			'order_id' => SecurityHelper::getBodyParam("order_id"),
			'cate_id'  => SecurityHelper::getBodyParam("cate_id"),
			'option'   => SecurityHelper::getBodyParam("option", false),
		];
		\Yii::$app->debug->log_info("key_params",$params);
		if ($params['order_id'] && $params['cate_id']) {
			if($params['option'])	$params['option'] = json_decode($params['option'],true);

			QueueHelper::sendWechatDispatchMessageToProvider($params['order_id'],$params['cate_id'],$params['option']);
			$this->_message = "发送成功！";
		} else {
			$this->_code    = -200;
			$this->_message = "发送失败！";
		}

		return $this->response();
	}
}