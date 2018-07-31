<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace api\modules\v1\controllers;

use common\components\ControllerAPI;
use common\helpers\orders\ErrandHelper;
use common\helpers\utils\PushHelper;
use Yii;

class PushController extends ControllerAPI
{
	public $formalHost = 'http://admin.281.com.cn';  //正式后台


	/**
	 * @旧订单 废弃
	 */
	public function actionOrder()
	{
		$orderId = Yii::$app->request->get("orderid");

		if (empty($orderId)) {
			Yii::warning("orderId 为空,50206");
			exit;
		} else {
			PushHelper::pushOrder($orderId);
		}
		echo "ok";
		exit;
	}


	//平台取消订单发送的通知消息

	/**
	 * @迁移到 ordercontroller
	 */
	public function actionErrandCancel()
	{
		$order_no    = Yii::$app->request->post("order_no");
		$result      = "fail";
		$client_host = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

		if ($client_host == 'http://admin.281.com.cn') {//后台地址	请求该接口必须是后台地址
			ErrandHelper::platformCancel($order_no) ? $result = "success" : "fail";
		}

		if (YII_ENV == "dev" || YII_ENV == "beta" || YII_ENV == "test") {//开发环境
			ErrandHelper::platformCancel($order_no) ? $result = "success" : "fail";
		}
		echo json_encode($result);
		exit;
	}

	/**
	 *
	 * 查找附近的商家
	 * @param $center_location
	 * @param $cate_id
	 *
	 * @迁移到MapController
	 */
	public function actionNearbyShop($center_location, $cate_id)
	{
		$data = PushHelper::nearbyShop($center_location, $cate_id);
		echo $data ? json_encode($data) : 'fail';
		exit;
	}

	/**
	 * 旧摩的小帮端推送接口  可以废弃
	 */
	public function actionModi()
	{
		$orderId = Yii::$app->request->get("orderid");
		$type    = Yii::$app->request->get('type');
		$shop_id = Yii::$app->request->get('shop_id');
		Yii::$app->debug->log_info("actionModi", $orderId);
		if (empty($orderId) && empty($type)) {
			Yii::warning("orderId 为空,50206");
			exit;
		} else {
			PushHelper::pushToOldModi($orderId, $shop_id, $type);
		}
		echo "ok";
		exit;
	}

	/**
	 * 旧摩的用户端推送接口 可以废弃
	 */
	public function actionUserModi()
	{
		$orderId = Yii::$app->request->get("orderid");
		$type    = Yii::$app->request->get('type');
		$user_id = Yii::$app->request->get('user_id');
		Yii::$app->debug->log_info("actionModi", $orderId);
		if (empty($orderId) && empty($type)) {
			Yii::warning("orderId 为空,50206");
			exit;
		} else {
			PushHelper::pushToUserOldModi($orderId, $user_id, $type);
		}
		echo "ok";
		exit;
	}



}