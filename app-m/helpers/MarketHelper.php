<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/31
 */

namespace m\helpers;

use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\HelperBase;
use common\helpers\users\UserHelper;
use common\models\activity\ActivityGiftRecord;
use common\models\activity\MarketProfit;
use common\models\activity\MarketRelation;
use common\models\activity\MarketWithdraw;
use common\models\orders\Order;
use common\models\users\BizInfo;
use common\models\users\UserMarket;
use Yii;
use common\helpers\payment\WalletHelper;
use common\helpers\shop\ShopHelper;
use yii\data\Pagination;
use yii\db\Query;

class MarketHelper extends HelperBase
{
	/**
	 * 获取用户营销数据
	 * @param        $user_id
	 * @param string $select
	 * @return array|bool|null|\yii\db\ActiveRecord
	 */
	public static function getUserMarket($user_id, $select = "*")
	{
		$result     = false;
		$userMarket = UserMarket::find()->select($select)->where(['user_id' => $user_id])->asArray()->one();
		if ($userMarket) {
			$result = $userMarket;
		}

		return $result;
	}

	/**
	 * 生成营销号
	 * @param $marketId
	 * @return string
	 */
	public static function createMarketCode($marketId)
	{
		$len = strlen($marketId);
		$min = $len < 4 ? sprintf("%04d", $marketId) : 0;
		$num = $len >= 4 ? substr($marketId, -4, 4) : $min;

		return date("ymd") . $num;
	}

	/**
	 * 首页顶部随机数据
	 * @return string
	 */
	public static function indexTop()
	{
		//随机用户名
		$sql      = "SELECT uid,nickname FROM `bb_51_user` ORDER BY RAND() LIMIT 1";
		$user     = Yii::$app->db->createCommand($sql)->queryOne();
		$nickname = $user['nickname'] ? $user['nickname'] : '帮帮用户';

		$rand = rand(0, 1);
		if ($rand == 0) {
			//随机邀请金额
			$invite_money      = [0.8, 0.45, 0.14, 0.32, 0.52, 1.13, 0.28, 0.65];
			$rand_num          = array_rand($invite_money);
			$rand_invite_money = $invite_money[$rand_num];
			$result            = $nickname . ' 刚邀请了1位好友获得了' . $rand_invite_money . '元';
		} else {
			//随机提现金额
			$withdraw_money      = [30, 40, 50, 100, 200, 68, 90, 88, 80, 150];
			$rand_num            = array_rand($withdraw_money);
			$rand_withdraw_money = $withdraw_money[$rand_num];
			$result              = $nickname . ' 刚提现了' . $rand_withdraw_money . '元';
		}

		return $result;
	}

	/**
	 * 获取首页数据
	 * @param $user_id
	 * @return array
	 */
	public static function indexData($user_id)
	{
		//总收益
		$userMarket  = UserMarket::find()->where(['user_id' => $user_id])->asArray()->one();
		$totalProfit = $userMarket['earn_amount'] + $userMarket['freeze_amount'] + $userMarket['transfer_amount'];
		$totalProfit = (string)$totalProfit;

		//今日收益
		$today_start_time = strtotime(date('Y-m-d 00:00:00', time()));
		$today_end_time   = strtotime(date('Y-m-d 23:59:59', time()));
		$todayProfit      = MarketProfit::find()
			->select('amount')
			->where(['market_user_id' => $user_id])
			->andWhere(['between', 'create_time', $today_start_time, $today_end_time])
			->sum('amount');

		//用户信息
		$userInfo = UserHelper::getUserInfo($user_id, 'mobile,is_shops');

		//推荐用户关系
		$userQuery = MarketRelation::find()
			->select('wy_market_relation.user_id,wy_market_relation.sum_amount amount,u.nickname user_name,u.mobile')
			->join('inner join', 'bb_51_user as u', 'wy_market_relation.user_id=u.uid')
			->where(['market_user_id' => $user_id, 'type' => 1, 'wy_market_relation.status' => 1])
			->orderBy('wy_market_relation.create_time desc');

		//推荐用户数
		$userCount    = $userQuery->count();
		$userRelation = $userQuery->limit(20)->asArray()->all();
		foreach ($userRelation as &$value) {
			$bizInfo = BizInfo::find()->where(['user_id' => $value['user_id']])->asArray()->one();
			if ($bizInfo) {
				$value['role'] = '商家';
				//昵称长度
				if (!$bizInfo['biz_name']) {
					$value['user_name'] = '未实名';
				} elseif (mb_strlen($bizInfo['biz_name']) > 3) {
					$value['user_name'] = mb_substr($bizInfo['biz_name'], 0, 2) . "..";
				} else {
					$value['user_name'] = $bizInfo['biz_name'];
				}
			} else {
				$value['role'] = '用户';
				//昵称长度
				if (mb_strlen($value['user_name']) > 3) {
					$value['user_name'] = mb_substr($value['user_name'], 0, 2) . "..";
				}
			}
		}
		//推荐小帮关系
		$providerQuery = MarketRelation::find()
			->select('wy_market_relation.user_id,wy_market_relation.sum_amount amount,u.nickname user_name,u.mobile')
			->join('inner join', 'bb_51_user as u', 'wy_market_relation.user_id=u.uid')
			->where(['market_user_id' => $user_id, 'type' => 2, 'wy_market_relation.status' => 1])
			->orderBy('wy_market_relation.create_time desc');

		//推荐小帮数
		$providerCount    = $providerQuery->count();
		$providerRelation = $providerQuery->limit(20)->asArray()->all();
		foreach ($providerRelation as &$value) {
			$shopInfo = ShopHelper::getShopInfoByUserId($value['user_id']);
			if ($shopInfo['status'] == 1) {
				$value['role'] = '已入驻';
				//昵称长度
				if (!$shopInfo['shops_name']) {
					$value['user_name'] = '未实名';
				} elseif (mb_strlen($shopInfo['shops_name']) > 3) {
					$value['user_name'] = mb_substr($shopInfo['shops_name'], 0, 2) . "..";
				} else {
					$value['user_name'] = $shopInfo['shops_name'];
				}
			} else {
				$value['role']      = '未入驻';
				$value['user_name'] = '帮帮..';
			}
		}

		$giftProfit = MarketProfit::find()
			->where(['market_user_id' => $user_id, 'profit_from' => Ref::PROFIT_FROM_GIFT, 'received' => Ref::GIFT_UNRECEIVED])
			->orderBy('create_time desc')
			->asArray()
			->one();

		$result = [
			'today_profit'      => $todayProfit ? sprintf("%.2f", $todayProfit) : 0,                  //今日收益
			'total_profit'      => $totalProfit ? sprintf("%.2f", $totalProfit) : 0,                  //总收益
			'current_profit'    => $userMarket['earn_amount'] ? sprintf("%.2f", $userMarket['earn_amount']) : 0,      //可转收益
			'mobile'            => $userInfo['mobile'],                              //推荐码
			'user_relation'     => $userRelation ? $userRelation : null,             //推荐用户关系
			'provider_relation' => $providerRelation ? $providerRelation : null,     //推荐小帮关系
			'user_count'        => $userCount,                                       //推荐用户数
			'provider_count'    => $providerCount,                                   //推荐小帮数
			'status'            => 1,                                                //活动状态 0关闭 1开启
			'is_gift'           => $giftProfit ? 1 : 0
		];

		return $result;
	}

	/**
	 * 生成用户营销号记录
	 *
	 * @param $user_id
	 * @return bool|string
	 */
	public static function createUserMarket($user_id)
	{
		$key             = 'user_market_' . $user_id;
		$cacheUserMarket = Yii::$app->cache->get($key);
		if (!$cacheUserMarket) {
			$userMarket = UserMarket::find()->where(['user_id' => $user_id])->one();
			if (!$userMarket) {
				//生成营销记录
				$userMarketModel             = new UserMarket();
				$params                      = [
					'user_id'     => $user_id,
					'status'      => Ref::USER_MARKET_NORMAL,
					'create_time' => time(),
					'market_code' => ''    //TODO 生成营销号编号 手机号
				];
				$userMarketModel->attributes = $params;
				$userMarketModel->save();

				//生成营销号
				$marketCode                   = self::createMarketCode($userMarketModel->id);
				$userMarketModel->market_code = $marketCode;
				$userMarketModel->save();
			}
			Yii::$app->cache->set($key, $user_id, 60 * 60 * 24);
		}
	}

	/**
	 * 可转收益
	 *
	 * @param $user_id
	 * @return int|mixed
	 */
	public static function currentProfit($user_id)
	{
		$result     = 0;
		$userMarket = UserMarket::find()->select('earn_amount')->where(['user_id' => $user_id])->asArray()->one();
		if ($userMarket) {
			$result = sprintf("%.2f", $userMarket['earn_amount']);
		}

		return $result;
	}

	/**
	 * 提现账号
	 * @param $user_id
	 * @return bool|mixed
	 */
	public static function transferAccount($user_id)
	{
		$result     = false;
		$userMarket = UserMarket::find()->where(['user_id' => $user_id])->asArray()->one();
		if ($userMarket) {
			$result['transfer_account'] = $userMarket['transfer_account'] ? substr_replace($userMarket['transfer_account'], '****', 3, 4) : false;
			$result['transfer_type']    = $userMarket['transfer_type'];//提现类型(2支付宝 3银行)
		}

		return $result;
	}

	/**
	 * 已绑账号
	 * @param $user_id
	 * @param $transfer_type
	 * @return array|bool
	 */
	public static function boundAccount($user_id)
	{
		$result     = false;
		$userMarket = UserMarket::find()->where(['user_id' => $user_id])->asArray()->one();
		if ($userMarket) {
			if ($userMarket['transfer_account'] && $userMarket['transfer_realname'] && $userMarket['transfer_accountname'] && $userMarket['transfer_type']) {
				$result = [
					'transfer_account'     => $userMarket['transfer_account'],            //提现账号
					'transfer_realname'    => $userMarket['transfer_realname'],            //提现真实姓名
					'transfer_accountname' => $userMarket['transfer_accountname'],        //提现账号名称
					'transfer_type'        => $userMarket['transfer_type']                //提现类型(2支付宝 3银行)
				];
			}
		}

		return $result;
	}

	/**
	 * 提现
	 * @param $transfer_amount
	 * @param $user_id
	 * @return bool
	 */
	public static function withdraw($transfer_amount, $user_id)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			//TODO @关 验收
			$userMarket = UserMarket::find()->where(['user_id' => $user_id])->andWhere(['>=', 'earn_amount', $transfer_amount])->asArray()->one();
			if ($userMarket) {
				//1.提现金额符合范围内
				$result = !($transfer_amount <= 0);
				//2.添加提现记录
				$result &= self::addMarketWithdraw($transfer_amount, $user_id, $userMarket, Ref::USER_WITHDRAW);
				//3.修改提现数据
				$result &= self::updateWithDrawData($transfer_amount, $user_id);
				if ($result) {
					$earn_amount = self::getUserMarket($user_id, 'earn_amount');
					if ($earn_amount) {
						$result = $earn_amount;
					}
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
	 * 获取提现记录
	 * @param $user_id
	 * @return bool
	 */
	public static function getMarketWithdraw($user_id)
	{
		$result         = false;
		$marketWithdraw = MarketWithdraw::find()->where(['user_id' => $user_id])->orderBy('apply_time desc')->limit(10)->asArray()->all();
		if ($marketWithdraw) {
			foreach ($marketWithdraw as $key => $value) {
				$result[$key]['transfer_amount']      = $value['transfer_amount'];//转出余额
				$result[$key]['transfer_realname']    = $value['transfer_realname'];//提现真实姓名
				$result[$key]['transfer_accountname'] = $value['transfer_accountname'];//提现账号名称
				$result[$key]['transfer_account']     = $value['transfer_account'];//提现账号
				$result[$key]['transfer_type']        = $value['transfer_type'];//提现类型(2支付宝 3银行)
				$result[$key]['status']               = $value['status'];//状态(1正常 2已处理)
				$result[$key]['remark']               = $value['remark'];//备注
				$result[$key]['apply_time']           = $value['apply_time'] ? date('Y-m-d H:i:s', $value['apply_time']) : null;//申请时间
				$result[$key]['transfer_time']        = $value['transfer_time'] ? date('Y-m-d H:i:s', $value['transfer_time']) : null;//提现时间
			}
		}

		return $result;
	}

	/**
	 * 转到余额
	 * @param $transfer_amount
	 * @param $user_id
	 * @return bool
	 */
	public static function transfer($transfer_amount, $user_id)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$userMarket = UserMarket::find()->where(['user_id' => $user_id])->andWhere(['>=', 'earn_amount', $transfer_amount])->asArray()->one();
			if ($userMarket) {
				//1.提现金额符合范围内
				$result = !($transfer_amount <= 0);
				//2.修改转到余额数据
				$result &= self::updateTransferData($transfer_amount, $user_id);
				//3.添加账户余额
				$result &= self::increaseAccountMoney($transfer_amount, $user_id);
				//4.添加收支明细
				$result &= self::increaseIncomePay($user_id, $transfer_amount);
				if ($result) {
					$earn_amount = self::getUserMarket($user_id, 'earn_amount');
					if ($earn_amount) {
						$result = $earn_amount;
					}
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
	 * 添加提现记录
	 * @param $transfer_amount
	 * @param $user_id
	 * @param $userMarket
	 * @param $apply_source
	 * @return bool
	 */
	public static function addMarketWithdraw($transfer_amount, $user_id, $userMarket, $apply_source)
	{
		//TODO @关 验收
		$marketWithdraw             = new MarketWithdraw();
		$params                     = [
			'user_id'              => $user_id,
			'transfer_amount'      => $transfer_amount,
			'transfer_realname'    => $userMarket['transfer_realname'],
			'transfer_accountname' => $userMarket['transfer_accountname'],
			'transfer_account'     => $userMarket['transfer_account'],
			'transfer_type'        => $userMarket['transfer_type'],
			'status'               => 0,//0待审核 1正常 2已处理
			'apply_source'         => $apply_source,//1用户端 2小帮端
			'apply_time'           => time(),
		];
		$marketWithdraw->attributes = $params;
		$result                     = $marketWithdraw->save() ? true : false;

		return $result;
	}

	/**
	 * 修改提现数据
	 * @param $transfer_amount
	 * @param $user_id
	 * @return bool
	 */
	public static function updateWithDrawData($transfer_amount, $user_id)
	{
		$result     = false;
		$userMarket = UserMarket::find()->where(['user_id' => $user_id])->one();
		if ($userMarket) {
			//TODO @关 验收
			$userMarket->earn_amount   = bcsub($userMarket->earn_amount, $transfer_amount, 3);
			$userMarket->freeze_amount = bcadd($userMarket->freeze_amount, $transfer_amount, 3);
			$userMarket->save() ? $result = true : false;
		}

		return $result;
	}

	/**
	 * 修改转到余额数据
	 * @param $transfer_amount
	 * @param $user_id
	 * @return bool
	 */
	public static function updateTransferData($transfer_amount, $user_id)
	{
		$result     = false;
		$userMarket = UserMarket::find()->where(['user_id' => $user_id])->one();
		if ($userMarket) {
			//TODO  @关 验收
			$userMarket->earn_amount     = bcsub($userMarket->earn_amount, $transfer_amount, 3);
			$userMarket->transfer_amount = bcadd($userMarket->transfer_amount, $transfer_amount, 3);
			$userMarket->save() ? $result = true : false;
		}

		return $result;
	}

	/**
	 * 添加账户余额
	 * @param $transfer_amount
	 * @param $user_id
	 * @return bool
	 */
	public static function increaseAccountMoney($transfer_amount, $user_id)
	{
		//TODO  @关 验收
		$result   = false;
		$entrance = Yii::$app->session->get('entrance');
		if ($entrance == 'user' || $entrance == 'wechat') {
			//用户添加余额
			$result = WalletHelper::increaseUserBalance($user_id, $transfer_amount);
		} else if ($entrance == 'provider') {
			//小帮添加余额
			$shop = ShopHelper::getShopInfoByUserId($user_id);
			$shop ? $result = WalletHelper::handleShopBalance($shop['id'], $transfer_amount) : false;
		}

		return $result;
	}

	/**
	 * 添加收支明细
	 * @return bool
	 */
	public static function increaseIncomePay($user_id, $transfer_amount)
	{
		$result   = false;
		$entrance = Yii::$app->session->get('entrance');
		if ($entrance == 'user' || $entrance == 'wechat') {
			//用户添加余额
			$result = WalletHelper::userIncomePay(Ref::BALANCE_TYPE_IN, Ref::USER_BALANCE_MARKET_IN, $user_id, 0, $transfer_amount, '全民合伙人-转到余额');
		} else if ($entrance == 'provider') {
			//小帮添加余额
			$shopInfo = ShopHelper::getShopInfoByUserId($user_id);
			$balance  = isset($shopInfo['shops_money']) ? $shopInfo['shops_money'] : 0;
			$result   = WalletHelper::handleIncomeShop($shopInfo['id'], $user_id, 0, $transfer_amount, '全民合伙人-转到余额', Ref::PROVIDER_BALANCE_MARKET_IN, Ref::BALANCE_TYPE_IN, $balance);
		}

		return $result;
	}


	/**
	 * 收益合计
	 * @param $user_id
	 * @param $profit_time
	 * @return mixed
	 */
	public static function SumProfit($user_id, $profit_time)
	{
		if ($profit_time == Ref::TODAY_PROFIT) {
			$today_start_time = strtotime(date('Y-m-d 00:00:00', time()));
			$today_end_time   = strtotime(date('Y-m-d 23:59:59', time()));
			$totalProfit      = MarketProfit::find()->where(['market_user_id' => $user_id]);
			$totalProfit = $totalProfit->andWhere(['between', 'wy_market_profit.create_time', $today_start_time, $today_end_time]);
			$sumProfit   = $totalProfit->sum('amount');
		} else {
			$userMarket = UserMarket::find()->where(['user_id' => $user_id])->asArray()->one();
			$sumProfit  = $userMarket['earn_amount'] + $userMarket['freeze_amount'] + $userMarket['transfer_amount'];
			$sumProfit  = (string)$sumProfit;
		}

		return $sumProfit;
	}


	/**
	 * 收益数据
	 * @param $user_id
	 * @param $profit_time
	 * @param $params
	 * @return array|bool|\yii\db\ActiveRecord[]
	 */
	public static function Profit($user_id, $profit_time, $params)
	{
		$result = false;

		//数据总数
		$current_page     = !empty($params['page']) ? $params['page'] : 1;
		$page             = ($current_page > 1) ? $current_page - 1 : 0;
		$page_size        = !empty($params['page_size']) ? $params['page_size'] : 20;
		$today_start_time = strtotime(date('Y-m-d 00:00:00', time()));
		$today_end_time   = strtotime(date('Y-m-d 23:59:59', time()));
		$totalProfit      = MarketProfit::find()->where(['market_user_id' => $user_id, 'profit_from' => 0])->orWhere(['market_user_id' => $user_id, 'profit_from' => 1, 'received' => 1]);
		if ($profit_time == 1) {
			$totalProfit = $totalProfit->andWhere(['between', 'wy_market_profit.create_time', $today_start_time, $today_end_time]);
		}
		$countProfit = $totalProfit->count();

		//分页查询
		$pagination           = new Pagination(['totalCount' => $countProfit]);
		$pagination->page     = $page;
		$pagination->pageSize = $page_size;
		$totalProfit          = MarketProfit::find()
			->select('wy_market_profit.type,wy_market_profit.user_id,wy_market_profit.create_time,wy_market_profit.amount,wy_market_profit.profit_from,u.nickname user_name,u.mobile')
			->join('inner join', 'bb_51_user as u', 'wy_market_profit.user_id=u.uid')
			->where(['market_user_id' => $user_id, 'profit_from' => 0])
			->orWhere(['market_user_id' => $user_id, 'profit_from' => 1, 'received' => 1])
			->offset($pagination->offset)
			->limit($pagination->limit)
			->orderBy('wy_market_profit.create_time desc');
		if ($profit_time == 1) {
			$totalProfit = $totalProfit->andWhere(['between', 'wy_market_profit.create_time', $today_start_time, $today_end_time]);
		}
		$totalProfit = $totalProfit->asArray()->all();
		foreach ($totalProfit as &$value) {
			if ($profit_time == 1) {
				$value['create_time'] = date('H:i', $value['create_time']);
			} else {
				$value['create_time'] = date('Y-m-d', $value['create_time']);
			}
			if ($value['type'] == 1) {
				$bizInfo = BizInfo::find()->where(['user_id' => $value['user_id']])->asArray()->one();
				if ($bizInfo) {
					if (!$bizInfo['biz_name']) {
						$value['user_name'] = '未实名';
					} elseif (mb_strlen($bizInfo['biz_name']) > 3) {
						$value['user_name'] = mb_substr($bizInfo['biz_name'], 0, 2) . "..";
					} else {
						$value['user_name'] = $bizInfo['biz_name'];
					}
					$value['role'] = '商家';
				} else {
					if (mb_strlen($value['user_name']) > 3) {
						$value['user_name'] = mb_substr($value['user_name'], 0, 2) . "..";
					}
					$value['role'] = '用户';
				}
			} else {
				$shopInfo = ShopHelper::getShopInfoByUserId($value['user_id']);
				if (!$shopInfo['shops_name']) {
					$value['user_name'] = '未实名';
				} elseif (mb_strlen($shopInfo['shops_name']) > 3) {
					$value['user_name'] = mb_substr($shopInfo['shops_name'], 0, 2) . "..";
				} else {
					$value['user_name'] = $shopInfo['shops_name'];
				}
				$value['role'] = '小帮';
			}
		}
		if ($totalProfit) {
			$result = $totalProfit;
		}

		return $result;
	}

	/**
	 * 修改提现账号
	 * @param $params
	 * @param $user_id
	 * @return bool
	 */
	public static function updateTransfer($params, $user_id)
	{
		$result          = false;
		$userMarketModel = UserMarket::find()->where(['user_id' => $user_id])->one();
		if ($userMarketModel) {
			if ($params['transfer_account'] && $params['transfer_realname'] && $params['transfer_accountname'] && $params['transfer_type']) {
				$userMarketModel->attributes = $params;
				$userMarketModel->save() ? $result = true : false;
			}
		}

		return $result;
	}

	/**
	 * 清除提现账号
	 * @param $user_id
	 * @return bool
	 */
	public static function clearTransfer($user_id)
	{
		$result          = false;
		$userMarketModel = UserMarket::find()->where(['user_id' => $user_id])->one();
		if ($userMarketModel) {
			$userMarketModel->attributes = [
				'transfer_account'     => null,
				'transfer_realname'    => null,
				'transfer_accountname' => null,
				'transfer_type'        => null,
			];
			$userMarketModel->save() ? $result = true : false;
		}

		return $result;
	}

	/**
	 * 检查是否设置全民合伙人活动红包
	 * @param $id
	 * @return bool
	 */
	public static function checkActivityForProfitGift($id, $user_id)
	{
		$result       = false;
		$marketProfit = MarketProfit::find()->where(['id' => $id, 'market_user_id' => $user_id, 'profit_from' => Ref::PROFIT_FROM_GIFT, 'received' => Ref::GIFT_UNRECEIVED])->asArray()->one();
		if ($marketProfit) {
			$orderData = Order::find()->where(['order_id' => $marketProfit['order_id']])->asArray()->one();
			if ($orderData) {
				ActivityHelper::checkActivityForMarket($orderData) ? $result = true : null;
			}
			if (!$result) {    //活动结束 更新红包状态为过期
				MarketProfit::updateAll(['received' => Ref::GIFT_OVERDUE, 'create_time' => time()], ['id' => $id, 'received' => Ref::GIFT_UNRECEIVED]);
			}
		}

		return $result;
	}

	/**
	 * 全民合伙人领取红包
	 * @param $gift_id
	 * @param $user_id
	 * @return bool|mixed
	 */
	public static function updateActivityForProfitGift($gift_id, $user_id)
	{
		$result = false;

		//1、查找收益表的红包记录
		//2、收益表的信息查找活动主表
		//3、通过过活动主表生成活动记录=>得到红包金额
		//4、更新数据
		//4.1、红包记录      market_profit
		//4.2、更新关系记录   market_relation
		//4.3、更新累加的收入 user_market

		$marketProfit = MarketProfit::findOne(['id' => $gift_id, 'market_user_id' => $user_id, 'profit_from' => Ref::PROFIT_FROM_GIFT, 'received' => Ref::GIFT_UNRECEIVED]);
		if ($marketProfit) {

			$orderData   = Order::find()->where(['order_id' => $marketProfit->order_id])->asArray()->one();
			$activity_id = $orderData ? ActivityHelper::checkActivityForMarket($orderData) : 0;

			//随机获取gift
			$gift = ActivityHelper::getActivityRandomGiftByActivityId($activity_id);
			if ($gift) {

				$transaction = Yii::$app->db->beginTransaction();
				try {

					//添加红包记录
					$gift['activity_type'] = Ref::ACTIVITY_MARKET;
					$gift['user_id']       = $user_id;
					$gift['order_no']      = $orderData['order_no'];
					$saveGiftData          = ActivityHelper::_getGiftRecordParams($gift);
					$package_amount        = isset($saveGiftData['package_amount']) ? $saveGiftData['package_amount'] : 0;

					//更新主表
					$marketProfit->amount      = $package_amount;
					$marketProfit->received    = Ref::GIFT_RECEIVED;
					$marketProfit->create_time = time();
					$result                    = $marketProfit->save();


					//更新关系收入，累加推荐人总收入
					$saveMarketData ['user_id']        = $marketProfit->user_id;
					$saveMarketData ['type']           = $marketProfit->type;
					$saveMarketData ['market_user_id'] = $marketProfit->market_user_id;
					$saveMarketData ['amount']         = $package_amount;
					$result                            &= WalletHelper::saveMarketAmount($saveMarketData);

					//活动已领取的活动记录
					$saveGiftData['status']  = 1;    //已领取
					$saveGiftData['user_id'] = $marketProfit->market_user_id;
					$saveGiftData['role']    = $marketProfit->type + 1;    //兼容活动红包记录
					ActivityHelper::addActivityGiftRecord($saveGiftData);
					if ($result) {
						$result = $package_amount;
						$transaction->commit();
					}
				}
				catch (\Exception $e) {

					$transaction->rollBack();
				}
			}
		}


		return $result;
	}

	/**
	 * 获取红包收益
	 * @param $user_id
	 * @return null
	 */
	public static function giftProfit($user_id)
	{
		$result     = null;
		$giftProfit = MarketProfit::find()
			->where(['market_user_id' => $user_id, 'profit_from' => Ref::PROFIT_FROM_GIFT, 'received' => Ref::GIFT_UNRECEIVED])
			->orderBy('send_time desc')
			->asArray()
			->all();

		foreach ($giftProfit as $key => $value) {
			//用户昵称
			if ($value['type'] == 1) {
				$bizInfo = BizInfo::find()->where(['user_id' => $value['user_id']])->asArray()->one();
				if ($bizInfo) {
					if ($bizInfo['biz_name']) {
						$result[$key]['invite_name'] = $bizInfo['biz_name'];
					} else {
						$result[$key]['invite_name'] = '未实名商家';
					}
				} else {
					$userInfo = UserHelper::getUserInfo($value['user_id']);
					if ($userInfo['nickname']) {
						$result[$key]['invite_name'] = $userInfo['nickname'];
					} else {
						$result[$key]['invite_name'] = '帮帮用户';
					}
				}
			} elseif ($value['type'] == 2) {
				$shopInfo = ShopHelper::getShopInfoByUserId($value['user_id']);
				if ($shopInfo['shops_name']) {
					$result[$key]['invite_name'] = $shopInfo['shops_name'];
				} else {
					$result[$key]['invite_name'] = '未实名小帮';
				}
			} else {
				$result[$key]['invite_name'] = '帮帮用户';
			}

			//红包发放时间
			$time = strtotime(date('Y-m-d', time())) - 60 * 60 * 24;
			if (date('Y-m-d', $value['send_time']) == date('Y-m-d', time())) {
				//今天
				$result[$key]['send_time'] = date('H:i', $value['send_time']);
			} elseif ($value['send_time'] > $time) {
				//昨天
				$result[$key]['send_time'] = '昨天' . date('H:i', $value['send_time']);
			} else {
				//其他时间
				$result[$key]['send_time'] = date('Y年m月d日 H:i', $value['send_time']);
			}

			$result[$key]['gift_id']  = $value['id'];//红包id
			$result[$key]['received'] = $value['received'];//红包领取状态
		}

		return $result;
	}


	/**
	 * 获取已领取红包收益
	 * @param $user_id
	 * @return mixed
	 */
	public static function giftProfitReceived($user_id)
	{
		//用户昵称
		$entrance = Yii::$app->session->get('entrance');
		if ($entrance == 'provider') {
			$shopInfo            = ShopHelper::getShopInfoByUserId($user_id);
			$result['user_name'] = $shopInfo['shops_name'];
		} else {
			$userInfo            = UserHelper::getUserInfo($user_id);
			$result['user_name'] = $userInfo['nickname'];
		}

		//收益数据
		$giftProfit = MarketProfit::find()
			->where(['market_user_id' => $user_id, 'profit_from' => Ref::PROFIT_FROM_GIFT, 'received' => Ref::GIFT_RECEIVED])
			->orderBy('create_time desc')
			->asArray()
			->all();

		//合计收益金额
		$sum = 0;
		foreach ($giftProfit as $key => $value) {
			$sum = $sum + $value['amount'];
		}
		$result['sum'] = (string)$sum;

		//合计收益条数
		$result['count'] = count($giftProfit);
		$record          = null;

		//邀请人用户信息
		foreach ($giftProfit as $key => $value) {
			if ($value['type'] == 1) {
				$bizInfo = BizInfo::find()->where(['user_id' => $value['user_id']])->asArray()->one();
				if ($bizInfo) {
					if ($bizInfo['biz_name']) {
						$record[$key]['invite_name'] = $bizInfo['biz_name'];
					} else {
						$record[$key]['invite_name'] = '未实名商家';
					}
				} else {
					$userInfo = UserHelper::getUserInfo($value['user_id']);
					if ($userInfo['nickname']) {
						$record[$key]['invite_name'] = $userInfo['nickname'];
					} else {
						$record[$key]['invite_name'] = '帮帮用户';
					}
				}
			} elseif ($value['type'] == 2) {
				$shopInfo = ShopHelper::getShopInfoByUserId($value['user_id']);
				if ($shopInfo['shops_name']) {
					$record[$key]['invite_name'] = $shopInfo['shops_name'];
				} else {
					$record[$key]['invite_name'] = '未实名小帮';
				}
			} else {
				$record[$key]['invite_name'] = '帮帮用户';
			}

			//红包领取时间
			$time = strtotime(date('Y-m-d', time())) - 60 * 60 * 24;
			if (date('Y-m-d', $value['create_time']) == date('Y-m-d', time())) {
				//今天
				$record[$key]['create_time'] = date('H:i', $value['create_time']);
			} elseif ($value['create_time'] > $time) {
				//昨天
				$record[$key]['create_time'] = '昨天' . date('H:i', $value['create_time']);
			} else {
				//其他时间
				$record[$key]['create_time'] = date('Y年m月d日 H:i', $value['create_time']);
			}

			$record[$key]['gift_amount'] = $value['amount'];//红包领取金额
		}
		//收益记录
		$result['record'] = $record ? $record : null;

		return $result;
	}
}