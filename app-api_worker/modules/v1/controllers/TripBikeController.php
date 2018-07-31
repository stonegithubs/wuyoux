<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_worker\modules\v1\controllers;

use api_worker\modules\v1\api\ErrandAPI;
use api_worker\modules\v1\api\ErrandBuyAPI;
use api_worker\modules\v1\api\TripBikeAPI;
use api_worker\modules\v1\helpers\StateCode;
use api_worker\modules\v1\traits\ErrandTrait;
use common\components\Ref;
use common\helpers\orders\TripBikeHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\QueueHelper;
use Yii;

//小帮端
class TripBikeController extends ControllerAccess
{
	/**
	 * @see actionRobbing       小帮抢单
	 * @see actionWorkerTask    任务页
	 * @see actionWorkerProgress  工作流程
	 * @see actionWorkerCancel  取消订单
	 * @see actionWorkerDetail  订单详情
	 * @see actionWorkerDelete    订单删除
	 */

	/**
	 * 小帮抢单
	 */
	public function actionRobbing()
	{
		if ($this->api_version == '1.0') {

			$judgeBail = ShopHelper::judgeBail($this->provider_id); //判断商家的保证金是否符合要求
			if (!$judgeBail) {
				$this->setCodeMessage(StateCode::SHOP_CANT_ROB);

				return $this->response();
			}

			$blackList = ShopHelper::isBlacklist($this->provider_id);
			if ($blackList) {
				$this->setCodeMessage(StateCode::SHOP_BLACK);

				return $this->response();
			}

			$blackList = TripBikeHelper::notFinishOrder($this->provider_id);
			if ($blackList) {
				$this->setCodeMessage(StateCode::SHOP_NOT_FINISH_TRIP);

				return $this->response();
			}

			$res = TripBikeAPI::robbingV10($this->provider_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();
	}

	/**
	 * 小帮删除
	 */
	public function actionWorkerDelete()
	{
		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::workerDeleteV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_DELETE);
			}
		}

		return $this->response();
	}

	/**
	 * 任务页
	 */
	public function actionWorkerTask()
	{
		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::workerTaskV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);    //todo message
			}
		}

		return $this->response();
	}

	/**
	 * 小帮详情
	 */
	public function actionWorkerDetail()
	{
		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::workerDetailV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);    //todo message
			}
		}

		return $this->response();
	}

	//小帮取消
	public function actionWorkerCancel()
	{
		if ($this->api_version == '1.0') {

			$result = TripBikeAPI::workerCancelV10($this->provider_id);
			if ($result) {
				$this->_message = "取消成功";
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
				$this->_message = '取消失败';
			}
		}

		return $this->response();
	}


	public function actionWorkProgress()
	{
		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::workerProgressV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);    //todo message
			}
		}

		return $this->response();
	}
}