<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\api\TripBikeAPI;
use api_wx\modules\mpv1\helpers\StateCode;
use api_wx\modules\mpv1\helpers\WxTripBikeHelper;
use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\orders\TripBikeHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use common\models\util\HistoryAddress;
use Yii;

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
	 * @see actionAddFee            打赏小帮
	 * @see actionUserDelete        删除订单
	 * @see actionCheckOrder        实时检查订单
	 */

	//小帮出行首页检查登陆用户的未完成订单
	public function actionIndex()
	{
		//小帮出行首页先访问/v1/map/trip-nearby，然后登陆用户再访问这个

		$status     = "-1";
		$checkOrder = TripBikeHelper::checkOrder($this->user_id, Ref::CATE_ID_FOR_MOTOR, [Ref::ORDER_STATUS_DEFAULT, Ref::ORDER_STATUS_AWAITING_PAY]);
		if ($checkOrder['order_count'] > 0) {
			$trip_status = $checkOrder['trip_status'];
			$status      = $trip_status == 1 ? 0 : $status;
			$status      = $trip_status > 1 ? 1 : $status;
		}

		$this->_data = [
			'order_no'    => $checkOrder['order_no'],     //未完成订单号
			'order_count' => intval($checkOrder['order_count']),  //未完成订单数
			'status'      => $status,
		];

		return $this->response();
	}


	//用户输入的历史地址
	//没有历史地址则取周边商圈地址
	public function actionInputHistory()
	{

		$params['location'] = AMapHelper::coordToStr(SecurityHelper::getBodyParam('user_location'));  //用户坐标
		$params['page']     = SecurityHelper::getBodyParam('page');
		$params['pageSize'] = SecurityHelper::getBodyParam('page_size');
		$res                = TripBikeHelper::historyAddress($this->user_id, $params['page'], $params['pageSize'], Ref::ORDER_TYPE_TRIP);
		//没有历史地址则取周边商圈地址
		if ($res['pagination']['totalCount'] <= 0) {
			$result['type'] = 2;
			$result['data'] = AMapHelper::searchAround($params);
		} else {
			$result['type']       = 1;
			$result['pagination'] = $res['pagination']; //历史地址会有分页信息
			$result['data']       = $res['list'];
		}

		if ($result) {
			$this->_data = $result;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	//价格预估
	public function actionEstimatePrice()
	{
		$startLocation = SecurityHelper::getBodyParam('start_location');
		$endLocation   = SecurityHelper::getBodyParam('end_location');
		$userInfo      = UserHelper::getUserInfo($this->user_id, 'uid, city_id');
		$data          = TripBikeHelper::estimatePrice($startLocation, $endLocation, $userInfo, Ref::CATE_ID_FOR_MOTOR);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	//确认发单
	public function actionCreate()
	{

		//TODO 需要和前端优化
		$checkOrder = TripBikeHelper::checkOrder($this->user_id, Ref::CATE_ID_FOR_MOTOR, [Ref::ORDER_STATUS_DEFAULT, Ref::ORDER_STATUS_AWAITING_PAY]);
		if ($checkOrder['order_count'] > 0) {
			$this->setCodeMessage(StateCode::TRIP_CREATE_NOT_PAY);

			return $this->response();
		}

		$user_id           = $this->user_id;
		$order_amount      = SecurityHelper::getBodyParam("order_amount");
		$estimate_distance = SecurityHelper::getBodyParam("estimate_distance");
		$userInfo          = UserHelper::getUserInfo($user_id);
		$user_city_id      = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
		$regionArr         = RegionHelper::getAddressIdByLocation(SecurityHelper::getBodyParam('start_location'), $user_city_id);
		$user_mobile       = isset($userInfo['mobile']) ? $userInfo['mobile'] : 0;

		$params = [
			"base"     => [
				'cate_id'      => Ref::CATE_ID_FOR_MOTOR,
				'city_id'      => $regionArr['city_id'],
				'region_id'    => $regionArr['region_id'],
				'area_id'      => $regionArr['area_id'],
				'order_from'   => SecurityHelper::getBodyParam("order_from"),
				'user_mobile'  => $user_mobile,
				'order_type'   => Ref::ORDER_TYPE_TRIP,
				'user_id'      => $user_id,
				'order_status' => Ref::ORDER_STATUS_DEFAULT,
			],
			'amount'   => [
				'order_amount' => $order_amount,
			],
			'location' => [
				'user_location'      => SecurityHelper::getBodyParam("user_location"),
				'user_address'       => SecurityHelper::getBodyParam("user_address"),
				'start_location'     => SecurityHelper::getBodyParam("start_location"),
				'start_address'      => SecurityHelper::getBodyParam("start_address"),
				'end_location'       => SecurityHelper::getBodyParam("end_location"),
				'end_address'        => SecurityHelper::getBodyParam("end_address"),
				'end_address_detail' => SecurityHelper::getBodyParam('end_address_detail'),
			],
			'trip'     => [
				'trip_status'       => Ref::TRIP_STATUS_WAIT,
				'trip_type'         => Ref::TRIP_TYPE_BIKE,
				'estimate_location' => SecurityHelper::getBodyParam("end_location"),
				'estimate_address'  => SecurityHelper::getBodyParam("end_address"),
				'estimate_amount'   => $order_amount,
				'estimate_distance' => $estimate_distance,
			]
		];
		//检查地址信息是否缺失
		foreach ($params['location'] as $value) {
			if (!isset($value)) {
				$this->_message = '参数缺失';
				$this->_code    = StateCode::TRIP_CREATE_FAIL;

				return $this->response();
			}
		}

		//检查是否允许下单
		$user_location  = SecurityHelper::getBodyParam('user_location');
		$start_location = SecurityHelper::getBodyParam('start_location');
		$check          = RegionHelper::checkCurrentRegionAndOpening($user_location, $start_location, Ref::CATE_ID_FOR_MOTOR, $user_city_id);

		if (!$check['pass']) {
			$this->setCodeMessage(StateCode::TRIP_CREATE_FAIL);
			$this->_message = $check['message'];

			return $this->response();
		}
		//创建订单
		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($params);
		$res = $orderHelper->save();
		if (is_array($res)) {
			TripBikeHelper::saveHistoryAddress($params['location'], $user_id);
			QueueHelper::tripOrder($res['order_id']);
			QueueHelper::newOrderNotice($res['order_no'], 'trip');
			$this->_data = $res;

		} else {
			$this->setCodeMessage(StateCode::TRIP_CREATE_FAIL);
		}

		return $this->response();
	}

	//加价
	public function actionAddPrice()
	{
		$addPrice = SecurityHelper::getBodyParam('price');  //加价金额
		$orderNo  = SecurityHelper::getBodyParam('order_no');

		$res = TripBikeHelper::addPrice($orderNo, $addPrice);
		if ($res) {
			$this->_message = '操作成功';
			$this->_data    = $res['estimate_amount_text'];
		} else {
			$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
			$this->_message = '操作失败';
		}

		return $this->response();
	}

	//催催小帮
	public function actionPressProvider()
	{
		$orderNo = SecurityHelper::getBodyParam('order_no');
		$res     = TripBikeHelper::pressProvider($orderNo, $this->user_id);
		if ($res) {
			TripBikeHelper::pushToProviderNotice($res['order_id'], TripBikeHelper::PUSH_PROVIDER_TYPE_PRESS, $res);
			$this->_message = '等一等，已经帮你催了!';
		} else {
			$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
			$this->_message = '催促太频繁，请稍后再试!';
		}

		return $this->response();
	}

	//订单取消信息
	public function actionUserCancelIndex()
	{
		//TODO 和APP的接口数据要一致
		$data['title']  = '取消订单';
		$data['option'] = [
			['id' => 1, 'value' => '行程有变，暂时不需要用车', 'type' => 0],
			['id' => 2, 'value' => '等待小帮时间过长', 'type' => 0],
			['id' => 3, 'value' => '平台派单太远', 'type' => 0],
			['id' => 4, 'value' => '小帮以各种理由不来接我', 'type' => 0],
			['id' => 5, 'value' => '联系不上小帮', 'type' => 0],
			['id' => 6, 'value' => '小帮找不到上车点', 'type' => 0],
			['id' => 7, 'value' => '小帮服务态度恶劣', 'type' => 0],
			['id' => 8, 'value' => '小帮迟到', 'type' => 0],
			['id' => 9, 'value' => '其他', 'type' => 1],
		];

		$this->_data = $data;

		return $this->response();
	}

	//订单取消保存
	public function actionUserCancelSave()
	{
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['content_id'] = SecurityHelper::getBodyParam('content_id');
		$params['content']    = SecurityHelper::getBodyParam('content');
		$data                 = TripBikeHelper::userCancelSave($params, $this->user_id);
		if ($data) {
			$data['robbed'] == Ref::ORDER_ROBBED ?
				TripBikeHelper::pushToProviderNotice($data['order_id'], TripBikeHelper::PUSH_PROVIDER_TYPE_CANCEL_PROGRESS, $data) : null;

			$this->_message = '操作成功';
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = '操作失败';
		}

		return $this->response();
	}

	//订单投诉信息
	public function actionComplaintIndex()
	{
		//TODO 和APP的接口数据要一致
		$data['title']  = '投诉小帮';
		$data['option'] = [
			['id' => 1, 'value' => '小帮速度太慢', 'type' => 0],
			['id' => 2, 'value' => '需要等待小帮时间太久', 'type' => 0],
			['id' => 3, 'value' => '小帮强收小费', 'type' => 0],
			['id' => 4, 'value' => '小帮服务态度很差', 'type' => 0],
			['id' => 5, 'value' => '小帮损坏物品或商品', 'type' => 0],
			['id' => 6, 'value' => '小帮故意多收商品费用', 'type' => 0],
			['id' => 7, 'value' => '小帮没有按要求送达具体位置', 'type' => 0],
			['id' => 8, 'value' => '小帮联系不上', 'type' => 0],
			['id' => 9, 'value' => '其他', 'type' => 1],
		];

		$this->_data = $data;

		return $this->response();
	}

	//订单投诉保存
	public function actionComplaintSave()
	{
		$order_no            = SecurityHelper::getBodyParam('order_no');
		$param['content_id'] = SecurityHelper::getBodyParam('content_id');
		$param['content']    = SecurityHelper::getBodyParam('content');
		//TODO 需要做接口数据处理
		$data = TripBikeHelper::complaintSave($order_no, $param);
		if ($data) {
			$this->_message = '操作成功';
		} else {
			$this->_code    = StateCode::COMMON_OPERA_ERROR;
			$this->_message = '操作失败';
		}

		return $this->response();
	}

	//获取支付详情
	public function actionGetPaymentDetail()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['card_id']  = SecurityHelper::getBodyParam('card_id', '-1');
		$params['user_id']  = $this->user_id;
		$data               = TripBikeHelper::getPaymentDetail($params);

		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	//订单支付
	public function actionPrePayment()
	{
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['card_id']    = SecurityHelper::getBodyParam('card_id');
		$paymentId            = SecurityHelper::getBodyParam('payment_id');
		$openId               = SecurityHelper::getBodyParam("openid");
		$params['payment_id'] = $paymentId;
		$url                  = SecurityHelper::getBodyParam('url');

		$orderHelper = new OrderHelper();
		$orderRes    = $orderHelper->updatePrepayment($params);
		$result      = [
			'code' => 0,
			'data' => null
		];

		if ($orderRes) {

			if ($paymentId == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = TripBikeHelper::tripPaymentSuccess($orderRes['transaction_no'], $trade_no, $paymentId, $orderRes['fee'], "余额支付");

				$isSuccess ? "" : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败
			}

			if ($paymentId == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("mp-wxpay/trip-payment");
				$payParams['body']           = "无忧帮帮-小帮出行";
				$payParams['openid']         = $openId;
				$wxRes                       = WxpayHelper::jsOrder($payParams);
				$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
				$wxRes ? $result['data']['payInfo'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}


		} else {
			$result['code'] = $paymentId == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];

		return $this->response();
	}

	//计价详情
	public function actionPriceDetail()
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['card_id']  = SecurityHelper::getBodyParam('card_id', '-1');

		$data = TripBikeHelper::priceDetail($this->user_id, $params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
		}

		return $this->response();
	}

	//任务页
	public function actionUserTask()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$data               = TripBikeHelper::userTask($params);

		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
		}

		return $this->response();
	}

	//订单完成页
	public function actionFinishPage()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$data               = TripBikeHelper::userFinish($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
		}

		return $this->response();
	}

	//订单取消页
	public function actionCancelPage()
	{

		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;

		$data = TripBikeHelper::userCancel($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_BUY_DETAIL);
		}

		return $this->response();
	}

	//打赏小帮 TODO 待定
	public function actionAddReward()
	{
		$result = [
			'code' => 0,
			'data' => null
		];

		$param['order_no']   = SecurityHelper::getBodyParam('order_no');
		$paymentId           = SecurityHelper::getBodyParam('payment_id');
		$openId              = SecurityHelper::getBodyParam("openid");
		$param['payment_id'] = $paymentId;
		$param['fee']        = SecurityHelper::getBodyParam('fee');
		$url                 = SecurityHelper::getBodyParam('url');

		$orderRes = TripBikeHelper::addRewardPrePayment($param);
		if ($orderRes) {

			if ($paymentId == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$isSuccess = TripBikeHelper::addRewardSuccess($orderRes['user_id'], $orderRes['transaction_no'], $orderRes['fee_id'], $orderRes['fee'], "余额支付");
				$isSuccess ? "" : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败
			}

			if ($paymentId == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/trip-payment");
				$payParams['openid']         = $openId;
				$wxRes                       = WxpayHelper::jsOrder($payParams);
				$result['data']['wxInfo']    = WxpayHelper::getJsConfig(['chooseWXPay'], $url);
				$wxRes ? $result['data']['payInfo'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

		} else {
			$result['code'] = ($paymentId == Ref::PAYMENT_TYPE_BALANCE) ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		$this->setCodeMessage($result['code']);
		$this->_data = $result['data'];
		if ($result['code'] == 0) {
			$this->_message = '打赏成功';
		}

		return $this->response();
	}

	public function actionUserDelete()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$params['user_id']  = $this->user_id;
		$data               = TripBikeHelper::userDelete($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::ERRAND_DELETE);
		}

		return $this->response();
	}

	//清除历史记录
	public function actionClearHistory()
	{
		Yii::$app->db->createCommand()->delete(HistoryAddress::tableName(), 'user_id = ' . $this->user_id)->execute();
		$this->_message = '清除成功';

		return $this->response();
	}

	//实时检查订单
	public function actionCheckOrder()
	{
		$orderNo = SecurityHelper::getBodyParam('order_no');
		$data    = WxTripBikeHelper::checkOrderStatus($orderNo);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->_message = '暂无明细';
		}

		return $this->response();
	}
}