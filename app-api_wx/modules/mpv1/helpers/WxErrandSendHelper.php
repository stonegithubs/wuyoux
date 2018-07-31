<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/10/9
 */

namespace api_wx\modules\mpv1\helpers;

use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\EvaluateHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;

class WxErrandSendHelper extends ErrandSendHelper
{
	public static function checkOrder($params)
	{

		$result = false;
		$order  = Order::findOne(['order_no' => $params['order_no']]);
		if ($order) {
			$errand = OrderErrand::findOne(['order_id' => $order->order_id]);
			if ($errand) {
				$total_fee    = doubleval($errand->total_fee);
				$service_fee  = doubleval($errand->service_price) * $errand->service_qty;
				$order_amount = $total_fee + $service_fee;           //订单总金额 = 总小费 + 服务时长

				$photo_url = ImageHelper::getErrandImageUrlByIdRef($order->order_id);
				$result    = [
					'order_no'        => $order->order_no,
					'order_time'      => isset($order->payment_time) ? date("m-d H:i", $order->payment_time) : null,        //发单时间 就是支付时间
					'order_type'      => "小帮快送-" . ErrandHelper::getErrandType($errand->errand_type),                    //发单类型
					'content'         => $errand->errand_content,                                                            //发单内容
					'start_address'   => $order->start_address,                                                              //购买地址
					'end_address'     => $order->end_address,                                                                //收货地址
					'service_time'    => isset($errand->service_time) ? date("Y-m-d H:i:00", $errand->service_time) : '',    //收货时间
					'service_price'   => sprintf("%.2f", $errand->service_price),                                            //服务费用
					'payment_type'    => TransactionHelper::getPaymentType($order->payment_id),                              //支付方式
					'order_amount'    => sprintf("%.2f", $order_amount),                                                    //订单总金额
					'publish_time'    => isset($order->payment_time) ? date("m-d H:i", $order->payment_time) : null,        //发布时间 就是支付时间
					'robbed_time'     => isset($order->robbed_time) ? date("m-d H:i", $order->robbed_time) : null,            //接单时间
					'begin_time'      => isset($errand->begin_time) ? date("m-d H:i", $errand->begin_time) : null,            //开始时间
					'finish_time'     => isset($errand->finish_time) ? date("m-d H:i", $errand->finish_time) : null,        //完成时间
					'errand_status'   => $errand->errand_status,                                                            //快送状态
					'robbed'          => $order->robbed,
					'total_fee'       => sprintf("%.2f", $errand->total_fee),
					'spend_time'      => $errand->begin_time ? time() - $errand->begin_time : null,
					'receiver_mobile' => $errand->mobile,
					'photo_url'       => $photo_url ? $photo_url : null,                            //商品图片
					'order_status'    => $order->order_status,
				];


				if ($order->robbed == Ref::ORDER_ROBBED) {    //已经接单 显示小帮信息

					//小帮信息
					$providerInfo = UserHelper::getShopInfo($order->provider_id);
					$id_image     = isset($providerInfo['shops_photo']) ? $providerInfo['shops_photo'] : 0;
					$provider     = [
						'provider_name'    => isset($providerInfo['shops_name']) ? $providerInfo['shops_name'] : "无忧帮帮",    //小帮昵称
						'provider_mobile'  => $order->provider_mobile,                //小帮电话
						'provider_address' => $order->provider_address,                //小帮接单地址
						'provider_photo'   => ImageHelper::getUserPhoto($id_image),//小帮头像
						'provider_star'    => 5,                                    //小帮评分
					];

					$result = array_merge($result, $provider);
				}

				//订单明细添加
				if ($errand->errand_status == Ref::ERRAND_STATUS_FINISH) {

					$evaluate = EvaluateHelper::getEvaluateInfo($order->order_no);
					if ($evaluate) {

						$result['evaluate']     = $evaluate;
						$result['can_evaluate'] = 0;    //有评价信息 不能评价

					} else {

						$result['evaluate']     = null;
						$result['can_evaluate'] = 1;    //无评价信息 能评价
					}
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_APPLY) {
					$result['cancel_content'] = self::CANCEL_PROVIDER_APPLY_MSG;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_AGREE) {
					$result['cancel_content'] = self::CANCEL_PROVIDER_AGREE_MSG;
				}

				if ($errand->cancel_type == Ref::ERRAND_CANCEL_PROVIDER_DISAGREE) {
					$result['cancel_content'] = self::CANCEL_PROVIDER_DISAGREE_MSG;
				}

				//如果是客服的消息
				if ($errand->cancel_type == Ref::ERRAND_CANCEL_DEAL_NOTIFY || $errand->cancel_type == Ref::ERRAND_CANCEL_USER_NOTIFY) {
					$result['cancel_content'] = $order->order_status == Ref::ORDER_STATUS_CALL_OFF
						? self::CANCEL_ORDER_NOTIFY_MSG : self::CANCEL_DEAL_NOTIFY_MSG;
				}
				$result['cancel_type'] = $errand->cancel_type;
			}
		}

		return $result;
	}

	public static function getCalc($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no']]);//->getAttributes(['order_amount','order_id']);
		if ($model) {
			$errand       = OrderErrand::findOne(['order_id' => $model->order_id]);//->getAttributes(['total_fee','first_fee']);
			$first_fee    = isset($errand->first_fee) ? $errand->first_fee : 0;
			$total_fee    = isset($errand->total_fee) ? $errand->total_fee : 0;
			$order_amount = $model->order_amount;

			$price_data             = RegionHelper::getCityPrice($model->user_location, $model->region_id, Ref::CATE_ID_FOR_ERRAND_SEND);
			$params['online_money'] = $params['online_money'] * $price_data['online_money_discount'];
			$orderHelper            = new OrderHelper();
			$calc                   = $orderHelper->getOrderCalc($order_amount, $params);

			$range          = number_format($price_data['range_init'] / 1000, 2);
			$card_amount    = sprintf("%.2f", $calc['card_amount']);
			$amount_payable = sprintf("%.2f", $calc['amount_payable']);
			$online_money   = sprintf("%.2f", $calc['online_money']);
			$total_fee      = sprintf("%.2f", $total_fee);
			$order_amount   = sprintf("%.2f", ($order_amount - $first_fee));
			$service_price  = sprintf("%.2f", $errand->service_price);
			$discount       = sprintf("%.2f", $calc['discount']);

			$result         = [
				'card_amount'    => $card_amount,
				'amount_payable' => $amount_payable,
				'online_money'   => $online_money,
				'total_fee'      => $total_fee,
				'order_amount'   => $order_amount,
				'service_price'  => $service_price,
				'discount'       => $discount,
				'init_range'     => $range,
				'init_price'     => $price_data['range_init_price'],
				'unit_price'     => $price_data['range_unit_price'],
				'day_night'      => '(' . $price_data['night_time'] . '-' . $price_data['day_time'] . ')',
				'night_service'  => $price_data['service_fee'],
			];
		}

		return $result;
	}
}