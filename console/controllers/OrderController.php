<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/12/10 12:40
 */

namespace console\controllers;

use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\shop\ShopHelper;
use common\models\orders\Order;
use common\models\orders\OrderTrip;
use yii\console\Controller;
use Yii;

class OrderController extends Controller
{

	public function actionSend($user_id, $order_no)
	{
		$params['user_id']  = $user_id;
		$params['order_no'] = $order_no;
		$result             = ErrandSendHelper::userConfirm($params);
		echo $result ? "success" : "fail";
	}


	public function actionConfirm()
	{
		$sql
			  = "SELECT o.*,odc.errand_type
FROM `wy_order_delay_confirm` odc
left join wy_order o on odc.order_no = o.order_no
WHERE odc.create_time < '1517667180' and o.order_status  = 5
LIMIT 50";
		$data = Yii::$app->db->createCommand($sql)->queryAll();


		if ($data) {
			foreach ($data as $item) {
				$res    = false;
				$params = [
					'order_no' => $item['order_no'],
					'user_id'  => $item['user_id'],
				];
				$type   = $item['errand_type'];
				if ($type == Ref::ERRAND_TYPE_BUY) {
					$res = ErrandBuyHelper::userConfirm($params);
				}

				if ($type == Ref::ERRAND_TYPE_SEND) {
					$res = ErrandSendHelper::userConfirm($params);
				}

				if ($type == Ref::ERRAND_TYPE_DO) {
					$res = ErrandDoHelper::userConfirm($params);
				}

				if ($type == Ref::ERRAND_TYPE_BIZ) {
					$res = BizSendHelper::userConfirm($params);
				}

				echo $res ? "success" : "false";
			}
		} else {
			echo "no";
		}
	}


	public function actionFixBizPrice($order_no, $location, $address)
	{
		$params['order_no']         = $order_no;
		$params['current_address']  = $address;
		$params['current_location'] = $location;
		$result                     = BizSendHelper::updateDeliveryPrice($params);
		echo $result ? "success" : "fail";

	}

	public function actionHandleOldTripBikeOrder()
	{
		$update_num = 0;
		$sql        = "SELECT * FROM `bb_51_orders` WHERE `order_style` = '1' AND `trip_status` = '3' AND `status` = '1' AND `create_time` > '1490976000'  ";//2017-4月至今的数据才迁移
		$list       = Yii::$app->db->createCommand($sql)->queryAll();
		$total_num  = count($list);
		if ($list) {
			foreach ($list as $key => $value) {
				$result      = false;
				$transaction = Yii::$app->db->beginTransaction();
				try {
					$dicount        = bcadd($value['online_money_discount'], $value['online_pay_discount'], 2);
					$shop           = ShopHelper::shopDetailByMobile($value['s_mobile']);
					$tripStatusTime = explode(",", $value['trip_status_time']);

					$order_no               = date('ymdHis', $value['create_time']) . rand(10, 99);
					$orderData              = [
						'user_id'           => $value['uid'],
						'order_no'          => $order_no,
						'cate_id'           => 51,
						'city_id'           => $value['city_id'],
						'area_id'           => $value['area_id'],
						'region_id'         => $value['area_id'] ? $value['area_id'] : $value['city_id'],
						'order_type'        => 1,
						'order_from'        => ($value['send_way'] == 1) ? 1 : 3,
						'order_status'      => 1,
						'robbed'            => 1,
						'payment_status'    => 1,
						'order_amount'      => $value['money'],
						'provider_id'       => $value['shops_id'],
						'user_mobile'       => $value['n_mobile'],
						'provider_mobile'   => $value['s_mobile'],
						'user_location'     => $value['n_location'],
						'user_address'      => $value['n_address'],
						'provider_location' => str_replace('"', '', $value['s_location']),
						'provider_address'  => $shop['shops_address'],
						'start_location'    => $value['n_location'],
						'start_address'     => $value['n_address'],
						'end_location'      => $value['n_end_location'],
						'end_address'       => $value['n_end_address'],
						'create_time'       => $value['create_time'],
						'update_time'       => $value['update_time'],
						'robbed_time'       => $value['competition_time'] ? $value['competition_time'] : $tripStatusTime[0],
						'finish_time'       => $value['arrive_time'] ? $value['arrive_time'] : $tripStatusTime[2],
					];
					$orderModal             = new Order();
					$orderModal->attributes = $orderData;
					if ($orderModal->save()) {
						$trip = [
							'order_id'          => $orderModal->order_id,
							'trip_type'         => 1,
							'trip_status'       => 5,
							'estimate_amount'   => $value['money'],
							'actual_amount'     => $value['money'],
							'estimate_distance' => $value['trip_range'] * 1000,
							'actual_distance'   => $value['trip_range'] * 1000,
							'starting_distance' => number_format(self::getShortDistance($value['n_location'], $value['s_location']), 2) * 1000,
							'license_plate'     => $shop['plate_numbers'],
							'point_time'        => $value['received_time'] ? $value['received_time'] : $tripStatusTime[1],
							'point_location'    => $value['n_location'],
							'point_address'     => $value['n_address'],
							'pick_time'         => $value['received_time'] ? $value['received_time'] : $tripStatusTime[1],
							'pick_location'     => $value['received_location'] ? $value['received_location'] : $value['n_location'],
							'pick_address'      => $value['n_address'],
							'arrive_time'       => $value['arrive_time'] ? $value['arrive_time'] : $tripStatusTime[2],
							'arrive_location'   => $value['arrive_location'] ? $value['arrive_location'] : $value['n_end_location'],
							'arrive_address'    => $shop['shops_address'],
						];

						$tripOrderModal             = new OrderTrip();
						$tripOrderModal->attributes = $trip;
						if ($tripOrderModal->save()) {
							$result = true;
							OrderHelper::saveLogContent($orderModal->order_id, 'old_order_data', $value, '迁移旧订单数据' . $value['orderid']);

							$sql    = "UPDATE bb_51_orders SET status =2,cancel_content='" . $orderModal->order_no . "',remark='订单迁移新订单号:" . $orderModal->order_no . "'  WHERE orderid={$value['orderid']}";
							$result = Yii::$app->db->createCommand($sql)->execute();
						}
					}


					if ($result) {
						$transaction->commit();
						$update_num++;
					}
				}
				catch (Exception $e) {
					var_dump($e);
					$transaction->rollBack();
				}
			}

		}
		echo "total_num:{$total_num}";
		echo "update_num:{$update_num}";
	}


	private function getShortDistance($n, $s)
	{
		$nlatlon = json_decode($n, true);
		$slatlon = json_decode($s, true);

		$lat1  = $nlatlon[0];
		$lon1  = $nlatlon[1];
		$lat2  = $slatlon[0];
		$lon2  = $slatlon[1];
		$unit  = "K";
		$theta = $lon1 - $lon2;
		$dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad
			($lat2)) * cos(deg2rad($theta));
		$dist  = acos($dist);
		$dist  = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit  = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344);
		} elseif ($unit == "N") {
			return ($miles * 0.8684);
		} else { //mi
			return $miles;
		}
	}

	/**
	 * 自动冻结用户余额
	 */
	public function actionBizAutoFreezeBalance()
	{
		BizSendHelper::autoFreezebalance();
	}


	public function actionErrandCancel($order_no)
	{

		$result = ErrandHelper::platformCancel($order_no);
		var_dump($result);
		exit;
	}

}