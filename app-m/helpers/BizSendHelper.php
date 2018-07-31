<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/1/3
 */

namespace m\helpers;

use common\components\Ref;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\users\BizInfo;
use Yii;
use common\helpers\HelperBase;
use yii\db\Query;

class BizSendHelper extends HelperBase
{
	public static function getCalc($params)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $params['order_no']]);//->getAttributes(['order_amount','order_id']);
		if ($model) {
			$errand         = OrderErrand::findOne(['order_id' => $model->order_id]);//->getAttributes(['total_fee','first_fee']);
			$order_distance = UtilsHelper::distance($errand->order_distance);
			if ($model->payment_status == 1) {
				$result = [
					'payment_status' => $model->payment_status,
					'order_distance' => $order_distance,
					'total_amount'   => $model->order_amount,
					'service_amount' => $model->order_amount,
				];
			} else {
				$result = [
					'payment_status' => $model->payment_status,
					'order_distance' => $order_distance,
					'total_amount'   => $model->amount_payable,
					'service_amount' => $model->order_amount,
					'card_discount'  => $model->discount,
					'payment_type'   => TransactionHelper::getPaymentType($model->payment_id),
				];
			}
			$result['order_no'] = $params['order_no'];
		}

		return $result;
	}

	/**
	 * 通过order_no取计价规则
	 * @param $order_no
	 * @return array|bool
	 */
	public static function getRule($order_no)
	{
		$result = false;
		$model  = Order::findOne(['order_no' => $order_no]);
		if ($model) {
			$price_data = RegionHelper::getCityPrice($model->user_location, $model->region_id, Ref::CATE_ID_FOR_BIZ_SEND);


			$allData = RegionHelper::getPriceByDay($model->city_id, $model->area_id, Ref::CATE_ID_FOR_BIZ_SEND);

			$range  = number_format($price_data['range_init'] / 1000, 2);
			$result = [
				'init_range'  => $range,   //初始公里数
				'init_price'  => $price_data['range_init_price'],  //初始服务费
				'unit_price'  => $price_data['range_unit_price'],  //每公里服务单价
				'day_night'   => '(' . $price_data['night_time'] . '-' . $price_data['day_time'] . ')',
				'service_fee' => $allData['night_service_fee'],
			];
		}

		return $result;
	}

	/**
	 * 通过user_id取计价规则
	 * @param $user_id
	 * @return array|bool
	 */
	public static function getRuleByUserId($user_id)
	{
		$result  = false;
		$bizInfo = BizInfo::findOne(['user_id' => $user_id]);
		if ($bizInfo) {
			$time = time();

			$price_data = RegionHelper::getPriceByTime($time, $bizInfo->city_id, $bizInfo->area_id, Ref::CATE_ID_FOR_BIZ_SEND);

			$allData = RegionHelper::getPriceByDay($bizInfo->city_id, $bizInfo->area_id, Ref::CATE_ID_FOR_BIZ_SEND);

			$range  = number_format($price_data['range_init'] / 1000, 2);
			$result = [
				'init_range'  => $range,   //初始公里数
				'init_price'  => $price_data['range_init_price'],  //初始服务费
				'unit_price'  => $price_data['range_unit_price'],  //每公里服务单价
				'day_night'   => '(' . $price_data['night_time'] . '-' . $price_data['day_time'] . ')',
				'service_fee' => $allData['night_service_fee'],
			];
		}

		return $result;
	}

}