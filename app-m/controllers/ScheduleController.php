<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/18
 */

namespace m\controllers;

use common\components\Ref;
use common\helpers\shop\ShopHelper;
use common\models\orders\Order;
use m\helpers\ScheduleHelper;
use Yii;

class ScheduleController extends ControllerWeb
{
	/**
	 * 行程首页
	 * @return string|\yii\web\Response
	 */
	public function actionIndex()
	{
		$order_no = Yii::$app->request->get('order_no');
		$order    = Order::findOne(['order_no' => $order_no]);

		if (!$order) {
			return $this->redirect(['error']);
		}

		if ($order->cate_id == Ref::CATE_ID_FOR_ERRAND_BUY || $order->cate_id == Ref::CATE_ID_FOR_ERRAND_SEND || $order->cate_id == Ref::CATE_ID_FOR_ERRAND_DO) {
			if ($order->order_status == Ref::ORDER_STATUS_COMPLETED || $order->order_status == Ref::ORDER_STATUS_EVALUATE) {
				return $this->redirect(['finish']);
			}

			if ($order->order_status == Ref::ORDER_STATUS_DOING) {
				//小帮当前位置坐标
				$shopInfo          = ShopHelper::shopDetailByMobile($order->provider_mobile);
				$provider_lng      = $shopInfo['shops_location_lng'];
				$provider_lat      = $shopInfo['shops_location_lat'];
				$provider_location = '[' . $provider_lng . ',' . $provider_lat . ']';

				//预计小帮行程
				$route = ScheduleHelper::providerToEndLocation($provider_location, $order->end_location);

				//小帮信息
				$providerInfo                      = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->start_address);
				$providerInfo['provider_lng']      = $provider_lng;
				$providerInfo['provider_lat']      = $provider_lat;
				$providerInfo['provider_location'] = $provider_location;
				$end_location                      = json_decode($order->end_location, true);
				$providerInfo['end_location_lng']  = current($end_location);
				$providerInfo['end_location_lat']  = end($end_location);
				$providerInfo['end_location']      = $order->end_location;

				$data = [
					'route'         => $route,
					'providerInfo'  => $providerInfo,
					'download_link' => 'http://www.51bangbang.com.cn/index.php?s=/Home/Index/index_m'
				];

				return $this->renderPartial('index', $data);
			} else {
				return $this->redirect(['close']);
			}
		} else if ($order->cate_id == Ref::CATE_ID_FOR_BIZ_SEND) {
			if ($order->order_status == Ref::ORDER_STATUS_AWAITING_PAY || $order->order_status == Ref::ORDER_STATUS_COMPLETED || $order->order_status == Ref::ORDER_STATUS_EVALUATE) {
				return $this->redirect(['finish']);
			}

			if ($order->order_status == Ref::ORDER_STATUS_DEFAULT) {
				//小帮当前位置坐标
				$shopInfo          = ShopHelper::shopDetailByMobile($order->provider_mobile);
				$provider_lng      = $shopInfo['shops_location_lng'];
				$provider_lat      = $shopInfo['shops_location_lat'];
				$provider_location = '[' . $provider_lng . ',' . $provider_lat . ']';

				//小帮信息
				$providerInfo                      = ShopHelper::providerForOrderView($order->provider_id, $order->provider_mobile, $order->start_address);
				$providerInfo['provider_lng']      = $provider_lng;
				$providerInfo['provider_lat']      = $provider_lat;
				$providerInfo['provider_location'] = $provider_location;

				$data = [
					'providerInfo'  => $providerInfo,
					'download_link' => 'http://www.51bangbang.com.cn/index.php?s=/Home/Index/index_m'
				];

				return $this->renderPartial('biz_index', $data);
			} else {
				return $this->redirect(['close']);
			}

		} else {
			return $this->redirect(['error']);
		}
	}

	/**
	 * 404页面
	 * @return string
	 */
	public function actionError()
	{
		$data = [
			'download_link' => 'http://www.51bangbang.com.cn/index.php?s=/Home/Index/index_m'
		];

		return $this->renderPartial('error', $data);
	}

	/**
	 * 订单已完成
	 * @return string
	 */
	public function actionFinish()
	{
		$data = [
			'download_link' => 'http://www.51bangbang.com.cn/index.php?s=/Home/Index/index_m'
		];

		return $this->renderPartial('finish', $data);
	}

	/**
	 * 订单已关闭
	 * @return string
	 */
	public function actionClose()
	{
		$data = [
			'download_link' => 'http://www.51bangbang.com.cn/index.php?s=/Home/Index/index_m'
		];

		return $this->renderPartial('close', $data);
	}
}