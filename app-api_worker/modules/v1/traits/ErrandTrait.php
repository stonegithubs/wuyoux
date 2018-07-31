<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/9/11
 */

namespace api_worker\modules\v1\traits;


use api_worker\modules\v1\api\ErrandAPI;
use api_worker\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\shop\ShopHelper;

trait  ErrandTrait
{

	/**
	 * @通用 小帮抢单
	 *
	 */
	public function actionRobbing()
	{
		if ($this->api_version == '1.0') {

			$judgeBail = ShopHelper::judgeBail($this->provider_id);//判断商家的保证金是否符合要求
			if (!$judgeBail) {
				$this->setCodeMessage(StateCode::SHOP_CANT_ROB);

				return $this->response();
			}

			$blackList = ShopHelper::isBlacklist($this->provider_id);
			if ($blackList) {
				$this->setCodeMessage(StateCode::SHOP_BLACK);

				return $this->response();
			}

			$data = ErrandAPI::robbingV10($this->user_id, $this->provider_id);
			if ($data) {

				$this->_data = $data;

				//TODO 不同的推送类型，优化缓存处理
				if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY) {

					ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

				} else if ($data['errand_type'] == Ref::ERRAND_TYPE_DO) {

					ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

				} elseif ($data['errand_type'] == Ref::ERRAND_TYPE_SEND) {
					ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandsendHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);
				}
			} else {
				$this->setCodeMessage(StateCode::ERRAND_ROBBING);
			}
		}

		return $this->response();
	}

	/**
	 * @通用 小帮逻辑删除
	 * @return array
	 */
	public function actionWorkerDelete()
	{

		if ($this->api_version == '1.0') {
			$data = ErrandAPI::workerDeleteV10($this->provider_id);
			if ($data) {
				$this->_message = "删除成功";
			} else {

				$this->setCodeMessage(StateCode::ERRAND_DELETE);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮取消订单
	 */
	public function actionWorkerCancel()
	{

		if ($this->api_version == '1.0') {
			$data = ErrandAPI::workerCancelV10($this->provider_id);
			if ($data) {
				$this->_message = '申请成功待用户处理';
				$this->_data    = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_CANCEL);
			}
		}

		return $this->response();
	}

	/**
	 * 取消订单工作流
	 */
	public function actionWorkerCancelFlow()
	{
		if ($this->api_version == '1.0') {

			$data = ErrandAPI::workerCancelFlowV10($this->provider_id);
			if ($data) {
				$this->_message = "提交成功";
				$this->_data    = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_CANCEL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮工作流程
	 */
	public function actionWorkerProgress()
	{
		if ($this->api_version == '1.0') {
			$data = ErrandAPI::workerProgressV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_PROGRESS);
			}
		}

		return $this->response();
	}
}