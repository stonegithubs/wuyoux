<?php
/**
 * Created by PhpStorm.
 * User: JasonLeung
 * Date: 2018/1/19
 * Time: 13:41
 */

namespace api_user\modules\v1\helpers;

use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\BizHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use common\helpers\utils\UtilsHelper;
use common\models\activity\ActivityFlag;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use yii\db\Query;

class UserBizSendHelper extends BizSendHelper
{
	/**
	 * 保存临时订单
	 * @param $user_id
	 * @return array|bool
	 */
	public static function orderNow($user_id)
	{
		$result  = false;
		$bizInfo = BizHelper::getBizData($user_id);
		if ($bizInfo) {
			$biz_address     = isset($bizInfo['biz_address']) ? $bizInfo['biz_address'] : null;
			$biz_address_ext = isset($bizInfo['biz_address_ext']) ? $bizInfo['biz_address_ext'] : null;
			$tag_id          = isset($bizInfo['tag_id']) ? $bizInfo['tag_id'] : null;
			$params          = [
				'user_id'        => $user_id,
				'city_id'        => isset($bizInfo['city_id']) ? $bizInfo['city_id'] : 0,
				'area_id'        => isset($bizInfo['area_id']) ? $bizInfo['area_id'] : 0,
				'region_id'      => isset($bizInfo['region_id']) ? $bizInfo['region_id'] : 0,
				'start_location' => isset($bizInfo['biz_location']) ? $bizInfo['biz_location'] : null,
				'start_address'  => $biz_address . "," . $biz_address_ext,
				'user_mobile'    => isset($bizInfo['biz_mobile']) ? $bizInfo['biz_mobile'] : 0,
				'user_location'  => SecurityHelper::getBodyParam("user_location"),
				'user_address'   => SecurityHelper::getBodyParam("user_address"),
				'tmp_from'       => SecurityHelper::getBodyParam("order_from"),
				'cate_id'        => Ref::CATE_ID_FOR_BIZ_SEND,
				'content'        => BizHelper::getTagNameById($tag_id),
				'qty'            => SecurityHelper::getBodyParam("qty"),
			];

			$result = parent::saveOrderNow($params);
			if ($result) {
				QueueHelper::bizSendOrder($result['batch_no']);
			}
		}

		return $result;
	}

	/**
	 * /获取最后记录
	 * @param      $user_id
	 * @param null $batch_no
	 * @return array|bool
	 */
	public static function getLastTmpOrder($user_id, $batch_no = null)
	{
		$result = false;
		$status = [
			Ref::BIZ_TMP_STATUS_WAITE,
			Ref::BIZ_TMP_STATUS_PICKED,
			Ref::BIZ_TMP_STATUS_INPUT,
			Ref::BIZ_TMP_STATUS_INPUT,
		];

		if (!$batch_no) {
			//获取最后一条记录
			$lastData = BizTmpOrder::find()->where(['user_id' => $user_id])->orderBy(['tmp_id' => SORT_DESC])->asArray()->one();
			$batch_no = $lastData ? $lastData['batch_no'] : null;
		}

		if ($batch_no) {

			$data = BizTmpOrder::find()->where(['user_id' => $user_id, 'batch_no' => $batch_no])->orderBy(['tmp_status' => SORT_ASC])->asArray()->all();
			if ($data) {
				$res = [
					'batch_no'        => $batch_no,//批次号
					'type'            => 1,
					'provider_num'    => 0,    //小帮人数
					'robbed_num'      => 0,    //接单数量
					'tmp_qty'         => 0,    //订单总数
					'order_total_qty' => 0,    //实际下单数
					'input_num'       => 0,    //已配送
					'list'            => [],
					'show_cancel_btn' => 0,    //显示取消按钮 默认0不显示，1显示
				];

				foreach ($data as $row) {
					$res['tmp_qty']         += $row['tmp_qty'];
					$res['order_total_qty'] += $row['order_num'];
					$res['provider_num']    += 1;

					//已经接单
					if ($row['robbed'] == Ref::ORDER_ROBBED) {
						$provider          = ShopHelper::providerForOrderView($row['provider_id'], $row['provider_mobile'], $row['provider_address']);
						$tmp_list          = [
							'order_num'               => $row['order_num'] ? $row['order_num'] : 0,
							'tmp_status'              => $row['tmp_status'],
							'create_time'             => UtilsHelper::todayTimeFormat($row['create_time']),
							'starting_distance_label' => "距离您约" . UtilsHelper::distance($row['starting_distance']),
							'cancel_type'             => $row['cancel_type'],
						];
						$res['robbed_num'] += 1;
						$res['list'][]     = array_merge($tmp_list, $provider);
					}
					//配送中
					if ($row['tmp_status'] == Ref::BIZ_TMP_STATUS_INPUT) {
						$res['input_num'] += 1;
					}

					//获取取消订单的类型
					if ($row['cancel_type']) {
						$res['cancel_type'] = $row['cancel_type'];
						$res['tmp_no']      = $row['tmp_no'];
					}

					//还存在等待接单的数据  就能取消取消按钮
					if ($row['tmp_status'] == Ref::BIZ_TMP_STATUS_WAITE) {
						$res['show_cancel_btn'] = 1;
					}
				}
				//部分匹配
				if ($res['robbed_num'] > 0) {
					$res['type'] = 2;//部分匹配
				}

				//全部匹配
				if ($res['input_num'] == $res['provider_num']) {    //接单数等于小帮数 就是全部匹配

					$res['type'] = 3;
				}

				$result = $res;
			}
		}

		return $result;
	}

	//单张订单预支付
	public static function prePayment()
	{
		$params['order_no']   = SecurityHelper::getBodyParam('order_no');
		$params['card_id']    = SecurityHelper::getBodyParam('card_id');
		$payment_id           = SecurityHelper::getBodyParam('payment_id');
		$params['payment_id'] = $payment_id;
		$orderHelper          = new OrderHelper();
		$orderRes             = $orderHelper->generatePrePaymentSingle($params);

		$result = [
			'code' => 0,
			'data' => null
		];

		if ($orderRes) {

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = BizSendHelper::orderPaymentSuccess($orderRes['transaction_no'], $trade_no, $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败 //TODO 支付记录
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/biz-order-payment");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/biz-order-payment");
				$alipayRes                   = AlipayHelper::appOrder($payParams);

				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}

	public static function prePaymentBatch($orderData, $params)
	{
		$payment_id  = $params['payment_id'];
		$orderHelper = new OrderHelper();
		$orderRes    = $orderHelper->generatePrePaymentBatch($orderData, $params);
		$result      = [
			'code' => 0,
			'data' => null
		];

		if ($orderRes) {

			if ($payment_id == Ref::PAYMENT_TYPE_BALANCE) {    //余额支付
				$trade_no  = date("YmdHis");
				$isSuccess = BizSendHelper::orderPaymentSuccess($orderRes['transaction_no'], $trade_no, $payment_id, $orderRes['fee'], "余额支付");
				$isSuccess ? null : $result['code'] = StateCode::ERRAND_PER_PAYMENT_BALANCE;    //余额支付失败 //TODO 支付记录
			}

			if ($payment_id == Ref::PAYMENT_TYPE_WECHAT) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("user-wxpay/biz-order-payment");
				$wxRes                       = WxpayHelper::userAppOrder($payParams);
				$wxRes ? $result['data'] = $wxRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_WECHAT;    //微信支付失败
			}

			if ($payment_id == Ref::PAYMENT_TYPE_ALIPAY) {
				$payParams['fee']            = $orderRes['fee'];
				$payParams['transaction_no'] = $orderRes['transaction_no'];
				$payParams['notify_url']     = UrlHelper::payNotify("alipay/biz-order-payment");
				$alipayRes                   = AlipayHelper::appOrder($payParams);

				$alipayRes ? $result['data'] = $alipayRes : $result['code'] = StateCode::ERRAND_PER_PAYMENT_ALIPAY;    //支付宝支付失败
			}

		} else {
			$result['code'] = $payment_id == Ref::PAYMENT_TYPE_BALANCE ? StateCode::ERRAND_PER_PAYMENT_BALANCE : StateCode::ERRAND_PER_PAYMENT;
		}

		return $result;
	}


	//计价明细
	public static function getCalc($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no']]);//->getAttributes(['order_amount','order_id']);
		if ($model) {
			$errand         = OrderErrand::findOne(['order_id' => $model->order_id]);//->getAttributes(['total_fee','first_fee']);
			$order_distance = UtilsHelper::distance($errand->order_distance);
			if ($model->payment_status == 1) {
				$result['order_data'] = [
					'payment_status' => $model->payment_status,
					'order_distance' => $order_distance,
					'total_amount'   => $model->order_amount,
					'service_amount' => $model->order_amount,
				];
			} else {
				$result['order_data'] = [
					'payment_status' => $model->payment_status,
					'order_distance' => $order_distance,
					'total_amount'   => $model->amount_payable,
					'service_amount' => $model->order_amount,
					'card_discount'  => sprintf("%.2f", CouponHelper::getCardAmount($model->card_id)),
					'payment_type'   => TransactionHelper::getPaymentType($model->payment_id),
				];
			}

			$price_data               = RegionHelper::getCityPrice($model->user_location, $model->region_id, Ref::CATE_ID_FOR_BIZ_SEND);
			$range                    = number_format($price_data['range_init'] / 1000, 2);
			$result['calculate_data'] = [
				'init_range' => $range,   //初始公里数
				'init_price' => $price_data['range_init_price'],  //初始服务费
				'unit_price' => $price_data['range_unit_price'],  //每公里服务单价
				'day_night'  => '(' . $price_data['night_time'] . '-' . $price_data['day_time'] . ')',
			];
		}

		return $result;
	}

	public static function checkGuide($user_id)
	{
		$result       = false;
		$ActivityFlag = ActivityFlag::findOne(['user_id' => $user_id, 'type' => Ref::ACTIVITY_FLAG_GUIDE]);
		if ($ActivityFlag) {
			$result = true;
		}

		return $result;
	}

	public static function saveGuide($user_id)
	{
		$result                   = false;
		$insert_data              = [
			'user_id'     => $user_id,
			'type'        => Ref::ACTIVITY_FLAG_GUIDE,
			'flag'        => 1,
			'create_time' => time(),
		];
		$ActivityFlag             = new ActivityFlag();
		$ActivityFlag->attributes = $insert_data;
		if ($ActivityFlag->save()) {
			$result = true;
		}

		return $result;
	}

}