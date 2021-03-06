<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/10/9
 */

namespace api_wx\modules\biz\helpers;

use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\orders\BizSendHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\users\BizInfo;
use Yii;
use yii\db\Query;
use common\models\orders\OrderErrand;

class WxBizSendHelper extends BizSendHelper
{

	/**
	 * 修改企业送信息(微信暂时不用修改)
	 * @param $params
	 * @return bool
	 */
	public static function updateBiz($params)
	{
		$user_info = UserHelper::getUserInfo($params['user_id'], 'city_id');
		$address   = RegionHelper::getAddressIdByLocation($params['biz_location'], $user_info['city_id']);

		$update_time           = time();
		$biz_info              = BizInfo::findOne(['user_id' => $params['user_id']]);
		$biz_info->attributes  = $params;
		$biz_info->city_id     = $address['city_id'];
		$biz_info->area_id     = $address['area_id'];
		$biz_info->city_name   = $address['city_name'];
		$biz_info->area_name   = $address['area_name'];
		$biz_info->update_time = $update_time;

		$result = $biz_info->save();

		return $result;
	}


	//获取最后记录
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

	//计价明细
	public static function getCalc($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no']]);//->getAttributes(['order_amount','order_id']);
		if ($model) {
			$errand    = OrderErrand::findOne(['order_id' => $model->order_id]);//->getAttributes(['total_fee','first_fee']);
			$first_fee = isset($errand->first_fee) ? $errand->first_fee : 0;
			//$total_fee    = isset($errand->total_fee) ? $errand->total_fee : 0;
			$order_amount   = $model->order_amount;
			$order_distance = UtilsHelper::distance($errand->order_distance);

			$price_data = RegionHelper::getCityPrice($model->user_location, $model->region_id, Ref::CATE_ID_FOR_BIZ_SEND);
			//$params['online_money'] = $params['online_money'] * $price_data['online_money_discount'];

			$orderHelper = new OrderHelper();
			$calc        = $orderHelper->getOrderCalc($order_amount, $params);

			$range          = number_format($price_data['range_init'] / 1000, 2);
			$card_amount    = sprintf("%.2f", $calc['card_amount']);
			$amount_payable = sprintf("%.2f", $calc['amount_payable']);
			$order_amount   = sprintf("%.2f", ($order_amount - $first_fee));
			//$discount       = sprintf("%.2f", $calc['discount']);

			$allData = RegionHelper::getPriceByDay($model->city_id, $model->area_id, Ref::CATE_ID_FOR_BIZ_SEND);

			$result = [
				'order_distance' => $order_distance, //起点到终点距离
				'card_amount'    => $card_amount,  //扣除优惠券的金额
				'amount_payable' => $amount_payable,  //实际支付
				'order_amount'   => $order_amount,  //订单金额
				//'discount'       => $discount,
				'init_range'     => $range,   //初始公里数
				'init_price'     => $price_data['range_init_price'],  //初始服务费
				'unit_price'     => $price_data['range_unit_price'],  //每公里服务单价
				'day_night'      => '(' . $price_data['night_time'] . '-' . $price_data['day_time'] . ')',
				'night_service'  => isset($allData['night_service_fee']) ? $allData['night_service_fee'] : 0,   //夜间服务费
			];

		}

		return $result;
	}

	public static function getWxBizStatus($user_id)
	{
		$result   = [];
		$biz_info = BizInfo::findOne(['user_id' => $user_id, 'status' => [0, 1, 2]]);
		if ($biz_info) {
			$result['status'] = $biz_info->status;
		}

		return $result;
	}

}