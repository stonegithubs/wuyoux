<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api\modules\v1\controllers;

use Yii;
use api\modules\v1\api\OrderAPI;
use api\modules\v1\helpers\StateCode;

class OrderController extends ControllerAccess
{

	//订单海
	public function actionSea()
	{

		if ($this->api_version == '1.0') {
			$res = OrderAPI::seaV10($this->provider_id);
			if ($res) {
				$this->_data['order_sea'] = $res;
			} else {
				$this->_code = $this->setCodeMessage(StateCode::ORDER_SEA_EMPTY);
			}
			//订单号
//			$data        = [
//				'order_no',        //订单号
//				'user_address',        //用户地址
//				'content',            //内容
//				'category',            //'分类名称',// 小帮快送|帮我买
//				'type',                //订单类型 1,2,3,4，5
//				'distance',            //距离当前地点
//				'overtime',            //超时
//				'start_address',    //开始地址
//				'end_address',        //结束地址
//				'data' => [
//				],                    //附加参数组
//			];
//			$this->_data = $data;
//
		}
		return $this->response();
	}

	public function actionUserList()
	{
		if ($this->api_version == '1.0') {
			$data = OrderAPI::userListV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}

			return $this->response();
		}
	}
}