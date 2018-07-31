<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/23
 */

namespace wechat\helpers;

use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\HelperBase;
use common\models\activity\ActivityGiftRecord;
use common\models\orders\Order;
use yii\db\Query;


class LotteryHelper extends HelperBase
{
	const LIMIT_PACKAGE = 5;

	/**
	 * 用户领取红包 (lottery_status 红包领取状态 1:查不到订单 2:用户已领取 3:红包已领完 4:领取成功 5:今天领取次数超限)
	 * @param $user_id
	 * @param $order_no
	 * @return int
	 */
	public static function receiveGift($user_id, $order_no)
	{
		//4:领取成功(默认)
		$result = 4;

		//1:查不到订单
		$order = Order::findOne(['order_no' => $order_no]);
		if (!$order) {
			$result = 1;

			return $result;
		}

		//2:用户已领取
		$activity_gift_record = ActivityGiftRecord::findOne(['order_no' => $order_no, "user_id" => $user_id, "role" => Ref::USER_TYPE_USER]);
		if ($activity_gift_record) {
			$result = 2;

			return $result;
		}

		//3:红包已领完
		$records = ActivityGiftRecord::find()->where(['order_no' => $order_no, "role" => Ref::USER_TYPE_USER, 'status' => 0, 'user_id' => null])->limit(self::LIMIT_PACKAGE)->all();
		if (!$records) {
			$result = 3;

			return $result;
		}

		//5:今天领取次数超限
		$today_start_time = strtotime(date('Y-m-d 00:00:00', time()));
		$today_end_time   = strtotime(date('Y-m-d 23:59:59', time()));
		$count_records    = (new Query())
			->select('order_no')
			->from('wy_activity_gift_record')
			->where(['user_id' => $user_id, 'role' => Ref::USER_TYPE_USER])
			->andWhere(['between', 'update_time', $today_start_time, $today_end_time])
			->count();
		if ($count_records >= 5) {
			$result = 5;

			return $result;
		}

		ActivityHelper::receiveGiftV10($order_no, $user_id);//用户领取红包

		return $result;
	}

	/**
	 * 获取领取数据
	 * @param $user_id
	 * @param $order_no
	 * @return array|bool
	 */
	public static function getReceiveRecord($user_id, $order_no)
	{
		$result = false;

		$gift_record_model = ActivityGiftRecord::findOne(['order_no' => $order_no, "user_id" => $user_id, "role" => Ref::USER_TYPE_USER]);
		if ($gift_record_model) {
			//优惠券
			if ($gift_record_model->card_ids) {
				$card_ids = explode(',', $gift_record_model->card_ids);
				foreach ($card_ids as $key => $val) {
					$card                               = (new Query())->select('id,name,price,second_category')->from("bb_card")->where(['id' => $val])->one();
					$result['card'][$key]['card_price'] = intval($card['price']);
					if ($card['second_category'] == 0) {
						$result['card'][$key]['card_name'] = '通用券';
					} else if ($card['second_category'] == 51) {
						$result['card'][$key]['card_name'] = '出行券';
					} else if ($card['second_category'] == 132) {
						$result['card'][$key]['card_name'] = '快送券';
					}
				}
			}
			//红包
			if ($gift_record_model->package_amount) {
				$package_amount['card_price'] = $gift_record_model->package_amount;
				$package_amount['card_name']  = '红包';
				if (isset($result['card'])) {
					array_push($result['card'], $package_amount);
				} else {
					$result['card'][] = $package_amount;
				}
			}
		}

		return $result;
	}

	/**
	 * 查询订单记录
	 * @param $order_no
	 * @return bool|null|static
	 */
	public static function findOrder($order_no)
	{
		$result = false;
		$order  = Order::findOne(['order_no' => $order_no]);
		if ($order) {
			$result = $order;
		}

		return $result;
	}
}