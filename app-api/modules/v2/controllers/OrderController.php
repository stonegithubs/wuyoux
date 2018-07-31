<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/3
 */

namespace api\modules\v2\controllers;

use api\modules\v2\helpers\OpenStateCode;
use api\modules\v2\helpers\PlatformHelper;
use api_user\modules\v1\api\ErrandAPI;
use common\helpers\security\SecurityHelper;

class OrderController extends ControllerAccess
{
	//订单列表
	public function actionList()
	{

		$params = [
			'user_id'   => $this->user_id,
			'status'    => SecurityHelper::getBodyParam('status', 1), //1全部 2发布中 3进行中 4已经完成 5取消
			'page'      => SecurityHelper::getBodyParam('page', 1),
			'page_size' => SecurityHelper::getBodyParam('page_size', 20),
		];

		$this->_data = PlatformHelper::orderList($params);

		return $this->response();
	}

	//创建订单
	public function actionCreate()
	{
		$params = [
			'user_id'         => $this->user_id,
			'user_mobile'     => SecurityHelper::getBodyParam('user_mobile'),     //用户电话
			'user_location'   => SecurityHelper::getBodyParam('user_location'),  //用户所在坐标
			'user_address'    => SecurityHelper::getBodyParam('user_address'),     //用户所在地址
			'start_location'  => SecurityHelper::getBodyParam('start_location'), //起点坐标
			'start_address'   => SecurityHelper::getBodyParam('start_address'),  //起点地址
			'end_location'    => SecurityHelper::getBodyParam('end_location'),   //终点坐标
			'end_address'     => SecurityHelper::getBodyParam('end_address'),    //终点地址
			'receiver_mobile' => SecurityHelper::getBodyParam('receiver_mobile'),//收货人电话
			'receiver_time'   => SecurityHelper::getBodyParam('receiver_time'),  //收货时间
			'content'         => SecurityHelper::getBodyParam('content'),        //发单内容
		];

		$data = PlatformHelper::createOrder($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(OpenStateCode::ERRAND_SEND_CREATE_FAILED);
		}

		return $this->response();
	}


	//订单信息
	public function actionDetail()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$data     = PlatformHelper::orderDetail($order_no);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(OpenStateCode::ERRAND_SEND_DETAIL);
		}

		return $this->response();
	}

	//取消订单
	public function actionCancel()
	{

		$order_no = SecurityHelper::getBodyParam('order_no');
		$data     = PlatformHelper::orderCancel($order_no);
		if ($data['success']) {
			$this->_message = '取消成功';
		} else {
			$this->setCodeMessage(OpenStateCode::ERRAND_USER_CANCEL);
		}
		$this->_data = $data;

		return $this->response();

	}


}