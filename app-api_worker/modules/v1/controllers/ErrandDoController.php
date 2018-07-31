<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_worker\modules\v1\controllers;

use api_worker\modules\v1\api\ErrandDoAPI;
use api_worker\modules\v1\helpers\StateCode;
use api_worker\modules\v1\traits\ErrandTrait;

class ErrandDoController extends ControllerAccess
{
	/**
	 * 小帮快送小帮端 trait
	 *
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionRobbing()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerCancel()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerCancelFlow()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerDelete()
	 * @see  \api_worker\modules\v1\traits\ErrandTrait::actionWorkerProgress()
	 */    //

	use ErrandTrait;

	/**
	 * 小帮任务页
	 */
	public function actionWorkerTask()
	{

		if ($this->api_version == '1.0') {

			$data = ErrandDoAPI::workerTaskV10($this->provider_id, $this->appData);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_DETAIL);
			}
		}

		return $this->response();
	}

	/**
	 * 小帮详情页
	 */
	public function actionWorkerDetail()
	{

		if ($this->api_version == '1.0') {
			$data = ErrandDoAPI::workerDetailV10($this->provider_id, $this->appData);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_WORKER_DETAIL);
			}
		}

		return $this->response();
	}
}