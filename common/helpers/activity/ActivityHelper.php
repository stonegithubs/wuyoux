<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/2/9
 */

namespace common\helpers\activity;

use api_worker\modules\v1\api\ActivityAPI;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\payment\CouponHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\models\activity\Activity;
use common\models\activity\ActivityFlag;
use common\models\activity\ActivityGift;
use common\models\activity\ActivityGiftRecord;
use common\models\activity\MarketRelation;
use common\models\orders\Order;
use m\helpers\MarketHelper;
use Yii;

class ActivityHelper extends HelperBase
{
	const LIMIT_PACKAGE = 5;

	/**
	 * 今天是否加过油
	 * @param $user_id
	 * @return bool
	 */
	public static function find($user_id)
	{
		$result = false;

		$currentDayStart = strtotime(date('Y-m-d 00:00:00', time()));
		$currentDayEnd   = strtotime(date('Y-m-d 23:59:59', time()));
		$model           = ActivityFlag::find()->where(['user_id' => $user_id])->andFilterWhere(['between', 'create_time', $currentDayStart, $currentDayEnd])->orderBy('id desc')->one();

		if ($model) {
			$result = true;
		}

		return $result;
	}

	/**
	 * 添加/修改加油记录
	 * @param $user_id
	 * @return bool
	 */
	public static function save($user_id)
	{
		$result = false;

		$currentDayStart = strtotime(date('Y-m-d 00:00:00', time()));
		$currentDayEnd   = strtotime(date('Y-m-d 23:59:59', time()));
		$model           = ActivityFlag::find()->where(['user_id' => $user_id])->andFilterWhere(['between', 'create_time', $currentDayStart, $currentDayEnd])->orderBy('id desc')->one();

		if ($model) {
			$model->flag        += 1;
			$model->update_time = time();
		} else {
			$model              = new ActivityFlag();
			$model->flag        = 1;
			$model->type        = 1;
			$model->user_id     = $user_id;
			$model->create_time = time();
		}
		$result = $model->save();

		return $result;
	}

	/**
	 * 获取活动
	 * @param bool $extend
	 * @return array|null|\yii\db\ActiveRecord
	 */
	public static function getGiftActivity($extend = false)
	{

		$activity_modal = Activity::find();
		//查询当前
		$activity_modal->andWhere(['<=', "begin_time", time()]);
		$activity_modal->andWhere(['>=', "end_time", time()]);
		$condition = ['status' => 1];
		if (isset($extend['city_id'])) $condition['city_id'] = $extend['city_id'];
		if (isset($extend['area_id'])) $condition['area_id'] = $extend['area_id'];
		if (isset($extend['point_users'])) $condition['point_users'] = $extend['point_users'];
		if (isset($extend['activity_target'])) $condition['activity_target'] = $extend['activity_target'];
		if (isset($extend['activity_type'])) $condition['activity_type'] = $extend['activity_type'];
		$activity_modal->andWhere($condition);
		if (isset($extend['trigger_business']) && !empty($extend['trigger_business'])) {
			$activity_modal->andWhere(['OR', "trigger_business is null", "trigger_business = ''", "FIND_IN_SET('{$extend['trigger_business']}',trigger_business)"]);
		}
		$activity_modal->orderBy(["create_time" => SORT_DESC])->asArray()->one();

		return $activity_modal->orderBy(["create_time" => SORT_DESC])->asArray()->one();
	}

	/**
	 * 添加活动礼包记录
	 * @param $params
	 * @return bool
	 */
	public static function addActivityGiftRecord($params)
	{
		$result            = false;
		$modal             = new ActivityGiftRecord();
		$modal->attributes = $params;
		$modal->save() ? $result = Yii::$app->getDb()->getLastInsertID() : \Yii::$app->debug->log_info("activity", $modal->getErrors());

		return $result;
	}

	/**
	 * 修改活动礼包记录
	 * @param $record_id
	 * @param $param
	 * @return bool
	 */
	public static function editActivityGiftRecord($record_id, $param, $condition = false)
	{
		$result = false;
		if (!empty($condition)) $condition = ['id' => $record_id];
		$record = ActivityGiftRecord::find()->where($condition)->one();
		if ($record) {
			$record->attributes = $param;
			$record->save() ? $result = true : Yii::$app->debug->log_info("activity", $record->getErrors());
		}

		return $result;
	}

	/**
	 * 生成临时领取记录
	 * @param $params
	 * @return bool
	 */
	public static function getPackage($params)
	{
		$result                  = false;
		$params['activity_type'] = Ref::ACTIVITY_PACKAGE;
		$order                   = Order::find()->select(["order_id", "cate_id", "city_id", "area_id"])
			->where(['order_no' => $params['order_no'], "provider_id" => $params['provider_id']])
			->asArray()
			->one();
		if ($order) {
			#当前区域区域活动，
			$act_param = self::_setSearchActivityParam(array_merge($order, ['activity_target' => Ref::USER_TYPE_PROVIDER, 'activity_type' => $params['activity_type']]));
			$activity  = self::getGiftActivity($act_param);
			#查询城市活动
			if (empty($activity)) {
				unset($act_param['area_id']);
				$activity = self::getGiftActivity($act_param);
			}
			#查询全国活动
			if (empty($activity)) {
				$act_param['city_id'] = 0;    //全国活动city id =0
				$activity             = self::getGiftActivity($act_param);
			}

			if ($activity) {
				#获取对应的礼包
				$gift = self::getActivityRandomGiftByActivityId($activity['id']);
				if ($gift) {
					#生成用户临时领取记录
					$result = ActivityHelper::addActivityGiftRecord(self::_getGiftRecordParams(array_merge($gift, $params)));
				}
			}
		}

		return $result;
	}

	/**
	 * 获取全民合伙人红包活动
	 * @param $params
	 * @return mixed|null
	 */
	public static function getMarketActivity($order_id)
	{
		$result = null;
		$order  = Order::find()->select(["order_id", "cate_id", "city_id", "area_id"])
			->where(['order_id' => $order_id])
			->asArray()
			->one();
		if ($order) {
			#当前区域区域活动，
			$act_param = self::_setSearchActivityParam(array_merge($order, ['activity_target' => null, 'activity_type' => Ref::ACTIVITY_MARKET]));
			$activity  = self::getGiftActivity($act_param);
			#查询城市活动
			if (empty($activity)) {
				unset($act_param['area_id']);
				$activity = self::getGiftActivity($act_param);
			}
			#查询全国活动
			if (empty($activity)) {
				$act_param['city_id'] = 0;    //全国活动city id =0
				$activity             = self::getGiftActivity($act_param);
			}
			$result = $activity ? $activity['id'] : null;
		}

		return $result;
	}

	//检查全民合伙人是否可以获取红包
	public static function checkActivityForMarket($orderData)
	{
		$result    = false;
		$act_param = self::_setSearchActivityParam(array_merge($orderData, ['activity_target' => null, 'activity_type' => Ref::ACTIVITY_MARKET]));
		$activity  = self::getGiftActivity($act_param);
		#查询城市活动
		if (empty($activity)) {
			unset($act_param['area_id']);
			$activity = self::getGiftActivity($act_param);
		}
		#查询全国活动
		if (empty($activity)) {
			$act_param['city_id'] = 0;    //全国活动city id =0
			$activity             = self::getGiftActivity($act_param);
		}
		$activity ? $result = $activity['id'] : null;

		return $result;
	}


	/**
	 * 获取领取礼包记录参数
	 * @param $params
	 * @return array
	 */
	public static function _getGiftRecordParams($params)
	{
		$data = [
			'role'        => !empty($params['provider_id']) ? Ref::USER_TYPE_PROVIDER : Ref::USER_TYPE_USER,
			'activity_id' => $params['activity_id'],
			'gift_id'     => $params['id'],
			'order_no'    => $params['order_no'],
			'status'      => $params['activity_type'] == 1 ? 0 : 1,
			'create_time' => time(),
			'update_time' => $params['activity_type'] == 1 ? null : time()
		];
		if (!empty($params['user_id'])) $data['user_id'] = $params['user_id'];
		if (!empty($params['provider_id'])) $data['provider_id'] = $params['provider_id'];
		if (!empty($params['card_ids'])) $data['card_ids'] = $params['card_ids'];
		if (!empty($params['card_amount'])) $data['card_amount'] = $params['card_amount'];
		if (!empty($params['amount_section'])) {
			$amount_section         = explode(",", $params['amount_section']);
			$data['package_amount'] = isset($amount_section[1]) ? self::randomFloat($amount_section[0], $amount_section[1]) : $params['amount_section'];
		}

		return $data;
	}

	/**
	 * 随机生成浮点数
	 * @param int $min 最小值
	 * @param int $max 最大值
	 * @return string
	 */
	public static function randomFloat($min = 0, $max = 1)
	{
		return number_format($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
	}

	/**
	 * 设置查询活动参数
	 * @param $params
	 * @return array
	 */
	public static function _setSearchActivityParam($params)
	{
		return [
			"activity_target"  => $params['activity_target'],
			"activity_type"    => $params['activity_type'],
			"city_id"          => $params['city_id'],
			"area_id"          => $params['area_id'],
			"trigger_business" => $params['cate_id'],
		];
	}

	/**
	 * 获取随机红包记录，单条
	 * @param $activity_id
	 * @return bool|\yii\db\ActiveRecord
	 */
	public static function getActivityRandomGiftByActivityId($activity_id)
	{
		$result = false;
		$list   = ActivityGift::find()->where(['activity_id' => $activity_id])->asArray()->all();
		if ($list) {
			$rand_arr = [];
			foreach ($list as $k => $v) {
				$rand_arr[$k] = $v['trigger_rate'];
			}
			$key    = self::accessRateData($rand_arr);
			$result = $list[$key];
		}

		return $result;
	}

	/**
	 * 计算概率
	 * @param $proArr    array('a'=>20,'b'=>30,'c'=>50)['序号'=>几率]
	 * @return int|string
	 */
	public static function accessRateData($proArr)
	{ //计算中奖概率
		$rs     = array_keys($proArr)[0]; //z中奖结果
		$proSum = array_sum($proArr); // 概率数组的总概率精度
		//概率数组循环
		if (count($proArr) > 1) {
			foreach ($proArr as $key => $proCur) {
				$randNum = mt_rand(1, $proSum);
				if ($randNum <= $proCur) {
					$rs = $key;
					break;
				} else {
					$proSum -= $proCur;
				}
			}
			unset($proArr);
		}

		return $rs;
	}

	/**
	 * 打开红包
	 * @param $record_id
	 * @param $provider_id
	 * @param $user_id
	 * @return bool|string
	 */
	public static function openPackage($record_id, $provider_id, $user_id)
	{
		$data        = ['package_amount' => "0.00"];
		$transaction = \Yii::$app->db->beginTransaction();
		try {
			//查询用户领取记录信息
			$record = ActivityGiftRecord::findOne(['id' => $record_id, 'status' => 0]);
			if ($record) {
				$package_amount = number_format($record->package_amount, 2);
				$order_no       = $record->order_no;

				//更新领取记录
				$result = ActivityHelper::editActivityGiftRecord($record_id, ['status' => 1, 'update_time' => time()], ['id' => $record_id, 'status' => 0]);

				//修改商家记录
				$result &= WalletHelper::handleShopBalance($provider_id, $package_amount);

				//添加资金记录
				$shop    = UserHelper::getShopInfo($provider_id, ['shops_money']);
				$balance = isset($shop['shops_money']) ? $shop['shops_money'] : $package_amount;

				$result &= WalletHelper::handleIncomeShop($provider_id, $user_id, $order_no, $package_amount, "活动奖金", 1, 1, $balance);

				if ($result) {
					$data['package_amount'] = $package_amount;
					$transaction->commit();
				}
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $data;
	}

	/**
	 * 用户领取红包
	 * @param $order_no
	 * @param $user_id
	 * @return bool
	 */
	public static function receiveGiftV10($order_no, $user_id)
	{
		$result      = false;
		$transaction = \Yii::$app->db->beginTransaction();
		try {
			$order = Order::findOne(['order_no' => $order_no]);
			if ($order) {
				//判断该用户是否已领取
				if (!ActivityGiftRecord::findOne(['order_no' => $order_no, "user_id" => $user_id, "role" => Ref::USER_TYPE_USER])) {
					//1.查询领取记录，
					$records = ActivityGiftRecord::find()->where(['order_no' => $order_no, "role" => Ref::USER_TYPE_USER, 'status' => 0, 'user_id' => null])->limit(self::LIMIT_PACKAGE)->all();
					if ($records) {
						$rand_key   = mt_rand(0, (count($records) - 1));
						$giftRecord = $records[$rand_key];//随机获取临时领取记录
						if ($giftRecord) {
							$card_total = 0;
							//2.生成对应的卡券记录，
							if ($giftRecord->card_ids) {
								$card_arr = explode(",", $giftRecord->card_ids);
								foreach ($card_arr as $k => $v) {
									//循环添加卡券
									$add_card = CouponHelper::userGetCard($user_id, $v);
									if ($add_card) $card_total += $add_card['card_amount'];
								}
							}

							//3. 修改对应的用户领取记录
							$gift_data      = [
								'user_id'     => $user_id,
								'card_amount' => $card_total,
								'status'      => 1,
								"update_time" => time()
							];
							$gift_condition = ['status' => 0, 'id' => $giftRecord->id, 'order_no' => $order_no];
							$result         = self::editActivityGiftRecord($giftRecord->id, $gift_data, $gift_condition);

							//4.生成对应的红包领取记录
							if ($giftRecord->package_amount > 0) {
								$result &= WalletHelper::userAcceptPackage($user_id, $giftRecord->package_amount);
							}
						}
					}
				}
				if ($result) {
					$result = ActivityGiftRecord::find()->where(['id' => $giftRecord->id])->asArray()->one();
					$transaction->commit();
				}
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 生成活动临时记录
	 * @param $order_no
	 * @throws \yii\db\Exception
	 */
	public static function createTmpActivityRecord($order_no)
	{
		$result        = false;
		$transaction   = \Yii::$app->db->beginTransaction();
		$limit_package = self::LIMIT_PACKAGE;
		try {
			$order = Order::find()->where(['order_no' => $order_no])->asArray()->one();
			if ($order) {
				//判断是否已经生成记录
				$record_num = ActivityGiftRecord::find()->where(['order_no' => $order_no, "role" => Ref::USER_TYPE_USER])->count("id");
				if ($record_num == 0) {
					#当前区域区域活动，
					$act_param = self::_setSearchActivityParam(array_merge($order, ['activity_target' => Ref::USER_TYPE_USER, 'activity_type' => Ref::ACTIVITY_PACKAGE]));
					$activity  = self::getGiftActivity($act_param);
					#查询城市活动
					if (empty($activity)) {
						unset($act_param['area_id']);
						$activity = self::getGiftActivity($act_param);
					}
					#查询全国活动
					if (empty($activity)) {
						unset($act_param['city_id']);
						$activity = self::getGiftActivity($act_param);
					}
					if ($activity) {
						#获取对应的礼包
						for ($i = 1; $i <= $limit_package; $i++) {
							$gift = self::getActivityRandomGiftByActivityId($activity['id']);
							if ($gift) {
								$params = ['order_no' => $order_no, 'activity_type' => Ref::ACTIVITY_PACKAGE];
								#生成用户临时领取记录
								$result = ActivityHelper::addActivityGiftRecord(self::_getGiftRecordParams(array_merge($gift, $params)));
							}
						}
					}
				}
			}
			if ($result) {
				$result = true;
				$transaction->commit();
			}
		}
		catch (\Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 检测是否已经被邀请
	 * @param $userId
	 * @return bool
	 */
	public static function checkIsInvite($userId, $type)
	{
		$modal = MarketRelation::findOne(['user_id' => $userId, 'type' => $type, 'status' => 1]);

		return $modal ? true : false;
	}

	/**
	 * 商家入驻，添加小帮关系
	 * @param $userId
	 * @param $providerId
	 * @return bool|int
	 */
	public static function addProviderMarketFromApply($userId)
	{
		$result   = false;
		$relation = MarketRelation::findOne(['user_id' => $userId]);
		if ($relation && !self::checkIsInvite($userId, 2)) {
			$provider = ShopHelper::getShopInfoByUserId($userId);
			if ($provider) {
				$result = self::addProviderMarketRelation($userId, $provider['id'], $relation->market_user_id);
			}
		}

		return $result;
	}

	/**
	 * 添加用户营销关系
	 * @param     $userId
	 * @param     $inviteId
	 * @param int $from
	 * @return bool|int
	 */
	public static function addUserMarketRelation($userId, $inviteId, $from = 1)
	{
		MarketHelper::createUserMarket($userId);
		MarketHelper::createUserMarket($inviteId);
		$data              = [
			'user_id'        => $userId,
			'market_user_id' => $inviteId,
			'type'           => 1,
			'sum_amount'     => 0.000,
			'status'         => 1,
			'create_time'    => time(),
			'rel_from'       => $from
		];
		$modal             = new MarketRelation();
		$modal->attributes = $data;

		return $modal->save() ? true : false & Yii::$app->debug->log_info("market", $modal->getErrors());
	}

	/**
	 * 添加小帮营销关系
	 * @param     $userId
	 * @param     $providerId
	 * @param     $inviteId
	 * @param int $from
	 * @return bool|int
	 */
	public static function addProviderMarketRelation($userId, $providerId, $inviteId, $from = 2)
	{
		MarketHelper::createUserMarket($userId);
		MarketHelper::createUserMarket($inviteId);
		$data              = [
			'user_id'        => $userId,
			'provider_id'    => $providerId,
			'market_user_id' => $inviteId,
			'type'           => 2,
			'sum_amount'     => 0.000,
			'status'         => 1,
			'rel_from'       => $from,
			'create_time'    => time()
		];
		$modal             = new MarketRelation();
		$modal->attributes = $data;

		return $modal->save() ? true : false & Yii::$app->debug->log_info("market", $modal->getErrors());
	}
}
