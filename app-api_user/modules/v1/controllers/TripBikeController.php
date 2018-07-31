<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\api\TripBikeAPI;
use api_user\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;

class TripBikeController extends ControllerAccess
{

	/**
	 * @see actionIndex             首页
	 * @see actionInputHistory      用户输入的历史地址
	 * @see actionEstimatePrice     价格预估
	 * @see actionCreate            确认发单
	 * @see actionUserTask          任务页
	 * @see actionAddPrice          加价
	 * @see actionPressProvider     催催小帮
	 * @see actionUserCancelIndex   订单取消信息
	 * @see actionUserCancelSave    订单取消保存
	 * @see actionComplaintIndex    订单投诉信息
	 * @see actionComplaintSave     订单投诉保存
	 * @see actionGetPaymentDetail  获取支付详情
	 * @see actionPrePayment        订单支付
	 * @see actionPriceDetail       计价详情
	 * @see actionFinishPage        订单完成页
	 * @see actionCancelPage        订单取消页
	 * @see actionAddReward         打赏小帮
	 * @see actionUserDelete        删除订单
	 */

	//小帮出行首页检查登陆用户的未完成订单
	public function actionIndex()
	{
		//小帮出行首页先访问/v1/map/trip-nearby，然后登陆用户再访问这个
		if ($this->api_version == '1.0') {

			$this->_data = TripBikeAPI::IndexV10($this->user_id);
		}

		return $this->response();
	}


	//用户输入的历史地址
	//没有历史地址则取周边商圈地址
	public function actionInputHistory()
	{
		if ($this->api_version == '1.0') {
			$data = TripBikeAPI::inputHistoryV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	//价格预估
	public function actionEstimatePrice()
	{
		if ($this->api_version == '1.0') {
			$data = TripBikeAPI::estimatePriceV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	//确认发单
	public function actionCreate()
	{
		if ($this->api_version == '1.0') {

			$checkOrder = TripBikeAPI::indexV10($this->user_id);
			if ($checkOrder['order_count'] > 0) {
				$this->setCodeMessage(StateCode::TRIP_CREATE_NOT_PAY);

				//TODO 需要和前端做优化
				return $this->response();
			}

			$userInfo       = UserHelper::getUserInfo($this->user_id);
			$user_city_id   = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
			$user_location  = SecurityHelper::getBodyParam('user_location');
			$start_location = SecurityHelper::getBodyParam('start_location');
			$check          = RegionHelper::checkCurrentRegionAndOpening($user_location, $start_location, Ref::CATE_ID_FOR_MOTOR, $user_city_id);

			if (!$check['pass']) {
				$this->setCodeMessage(StateCode::TRIP_CREATE_FAIL);
				$this->_message = $check['message'];

				return $this->response();
			}

			$res = TripBikeAPI::createOrderV10($this->user_id, $userInfo);

			if ($res) {
				$this->_data = $res;
			} else {
				$this->setCodeMessage(StateCode::TRIP_CREATE_FAIL);
			}
		}

		return $this->response();
	}

	//加价
	public function actionAddPrice()
	{
		if ($this->api_version == '1.0') {

			$res = TripBikeAPI::addPriceV10();
			if ($res) {
				$this->_message = '操作成功';
				$this->_data    = $res['estimate_amount_text'];
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
				$this->_message = '操作失败';
			}
		}

		return $this->response();
	}

	//催催小帮
	public function actionPressProvider()
	{
		if ($this->api_version == '1.0') {

			$res = TripBikeAPI::pressProviderV10($this->user_id);
			if ($res) {
				$this->_message = '平台会把您着急的心情第一时间告诉小帮，请稍等片刻';
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
				$this->_message = '莫着急！休息一会！在上一次催单后15分钟后才可以再次催单喔！';
			}
		}

		return $this->response();
	}

	//订单取消信息
	public function actionUserCancelIndex()
	{
		if ($this->api_version == '1.0') {
			$this->_data = TripBikeAPI::userCancelIndexV10();
		}

		return $this->response();
	}

	//订单取消保存
	public function actionUserCancelSave()
	{
		if ($this->api_version == '1.0') {
			$data = TripBikeAPI::userCancelSaveV10($this->user_id);
			if ($data) {
				$this->_message = '操作成功';
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = '操作失败';
			}
		}

		return $this->response();
	}

	//订单投诉信息
	public function actionComplaintIndex()
	{
		if ($this->api_version == '1.0') {
			$this->_data = TripBikeAPI::complaintIndexV10();
		}

		return $this->response();
	}

	//订单投诉保存
	public function actionComplaintSave()
	{
		if ($this->api_version == '1.0') {
			$data = TripBikeAPI::complaintSaveV10();
			if ($data) {
				$this->_message = '谢谢您的投诉支持。我们会对投诉情况进行核实后，如属实，我们会对小帮进行严厉的惩罚！';
			} else {
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
				$this->_message = '操作失败';
			}
		}

		return $this->response();
	}

	//获取支付详情
	public function actionGetPaymentDetail()
	{
		if ($this->api_version == '1.0') {
			$data = TripBikeAPI::getPaymentDetailV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();

	}

	//订单支付
	public function actionPrePayment()
	{

		if ($this->api_version == '1.0') {

			$res = TripBikeAPI::prePaymentV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();

	}

	//计价详情
	public function actionPriceDetail()
	{
		if ($this->api_version == '1.0') {
			$data = TripBikeAPI::priceDetailV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}

		}

		return $this->response();
	}

	//任务页
	public function actionUserTask()
	{

		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::UserTaskV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	//订单完成页
	public function actionFinishPage()
	{
		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::userFinishV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	//订单取消页
	public function actionCancelPage()
	{

		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::userCancelV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
			}
		}

		return $this->response();
	}

	//打赏小帮
	public function actionAddReward()
	{
		if ($this->api_version == '1.0') {

			$res = TripBikeAPI::addRewardV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
			if ($res['code'] == 0) {
				$this->_message = '打赏成功';
			}
		}

		return $this->response();
	}

	public function actionUserDelete()
	{
		if ($this->api_version == '1.0') {

			$data = TripBikeAPI::userDeleteV10($this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);    //TODO 删除的message
			}
		}

		return $this->response();
	}

	//清除历史记录
	public function actionClearHistory()
	{
		if ($this->api_version == '1.0') {
			TripBikeAPI::clearHistory($this->user_id);
			$this->_message = '清除成功';
		}

		return $this->response();
	}

}