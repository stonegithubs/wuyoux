<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_worker\modules\v1\controllers;

use common\helpers\security\SecurityHelper;
use Yii;
use api_worker\modules\v1\api\OrderAPI;
use api_worker\modules\v1\helpers\StateCode;

class OrderController extends ControllerAccess
{

	//订单海
	public function actionSea()
	{
		$res = [];
		if ($this->api_version == '1.0') {
			$res = OrderAPI::seaV10($this->provider_id);
		}

		if ($this->api_version == '1.1') {
			$res = OrderAPI::seaV11($this->provider_id);
		}

		if ($this->api_version == '1.2') {
			$res = OrderAPI::seaV12($this->provider_id);
		}

		if ($res) {
			$this->_data['order_sea'] = $res;
		} else {
			$this->_code = $this->setCodeMessage(StateCode::ORDER_SEA_EMPTY);
		}

		return $this->response();
	}

	//小帮订单列表
	public function actionProviderList()
	{
		if ($this->api_version == '1.0') {
			$data       = OrderAPI::providerListV10($this->provider_id); //此处传入小帮的ID
			$page       = SecurityHelper::getBodyParam('page');
			$pagination = $data['pagination'];

			if ($pagination['totalCount'] > 0) {
				if ($page > $pagination['pageCount']) {    //请求页数大于总页数
					$this->setCodeMessage(StateCode::PAGE_OUT_OF_DATA);
				} else {
					$this->_data = $data;
				}
			} else {
				$this->setCodeMessage(StateCode::PAGE_EMPTY_DATA);
			}
		}

		if ($this->api_version == '1.1') {
			$data       = OrderAPI::providerListV11($this->provider_id);
			$pagination = $data['pagination'];
			if ($pagination['totalCount'] > 0) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::PAGE_EMPTY_DATA);
			}
		}

		if ($this->api_version == '1.2') {
			$data       = OrderAPI::providerListV12($this->provider_id);
			$pagination = $data['pagination'];
			if ($pagination['totalCount'] > 0) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::PAGE_EMPTY_DATA);
			}
		}

		return $this->response();
	}
}