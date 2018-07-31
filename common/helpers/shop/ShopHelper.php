<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/24
 */

namespace common\helpers\shop;


use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\Order;
use common\models\users\FreeBailShop;
use common\models\users\UserMarket;
use yii\base\Exception;
use yii\db\Query;
use Yii;


class ShopHelper extends HelperBase
{
	const shopTbl            = 'bb_51_shops';
	const shopIdnetityPicTbl = 'bb_51_shop_identity_pic';

	/**
	 * 添加摩的商家资料
	 *
	 * @param $userInfo
	 * @param $params
	 *
	 * @return int
	 */
	public static function enterMotoShop($params, $driver_license_pic, $travel_license_pic, $id_license_pic)
	{
		$result = false;
		$query  = new Query();
		$query->from(self::shopTbl);
		$query->where(['uid' => $params['uid']]);
		$shop        = $query->select('id,uid')->one();
		$transaction = Yii::$app->db->beginTransaction();
		try {
			if ($shop > 0) {
				$uid = $params['uid'];
				$set = '';
				unset($params['uid']);
				foreach ($params as $k => $v) {
					$set .= '`' . $k . '`' . ' = ' . "'" . $v . "'" . ' , ';
				}
				$sql = "update `bb_51_shops` set `create_time` =" . time() . ", `update_time` =" . time() . " , `status` = 0 , " . rtrim($set, ', ') . " where `uid` = {$uid}";

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("更新shop表失败");
				$result &= self::StoreMotoInDentityPic($shop['id'], $driver_license_pic, $travel_license_pic, $id_license_pic);
			} else {
				$field = '';
				$data  = '';
				unset($params['photo']);
				foreach ($params as $k => $v) {
					$field .= "`" . $k . "`" . ',';
					$data  .= "'" . $v . "'" . ',';
				}
				$sql = "INSERT INTO `bb_51_shops` (" . rtrim($field, ',') . ") VALUES (" . rtrim($data, ',') . ")";

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("插入shop表新数据失败");
				$result &= self::StoreMotoInDentityPic(Yii::$app->db->getLastInsertID(), $driver_license_pic, $travel_license_pic, $id_license_pic);
				//$picResult = self::StoreInDentityPic(Yii::$app->db->getLastInsertID(), $driver_license_pic, $travel_license_pic, $id_license_pic);

			}

			if ($result) {
				//记录业务日志
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 入驻电动车商家
	 *
	 * @param $userInfo
	 * @param $params
	 *
	 * @return int
	 */
	public static function enterElectroShop($params, $other, $car, $id_card)
	{
		$result = false;
		$query  = new Query();
		$query->from(self::shopTbl);
		$query->where(['uid' => $params['uid']]);
		$shop        = $query->select('id,uid')->one();
		$transaction = Yii::$app->db->beginTransaction();
		try {
			if ($shop > 0) {
				$uid = $params['uid'];
				$set = '';
				unset($params['uid']);
				foreach ($params as $k => $v) {
					$set .= '`' . $k . '`' . ' = ' . "'" . $v . "'" . ' , ';
				}
				$sql = "update `bb_51_shops` set `create_time` =" . time() . ", `update_time` =" . time() . " , `status` = 0 , " . rtrim($set, ', ') . " where `uid` = {$uid}";

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("更新shop表失败");
				$result &= self::StoreElectroDentityPic($shop['id'], $other, $car, $id_card);
			} else {
				$field = '';
				$data  = '';
				unset($params['photo']);
				foreach ($params as $k => $v) {
					$field .= "`" . $k . "`" . ',';
					$data  .= "'" . $v . "'" . ',';
				}
				$sql = "INSERT INTO `bb_51_shops` (" . rtrim($field, ',') . ") VALUES (" . rtrim($data, ',') . ")";

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("插入shop表新数据失败");
				$result &= self::StoreElectroDentityPic(Yii::$app->db->getLastInsertID(), $other, $car, $id_card);

			}

			if ($result) {
				//记录业务日志
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 添加小帮快送商家资料
	 *
	 * @param $userInfo
	 * @param $params
	 *
	 * @return int
	 */
	public static function enterErrandShop($params, $id_license_pic)
	{
		$result = false;
		$query  = new Query();
		$query->from(self::shopTbl);
		$query->where(['uid' => $params['uid']]);
		$shop        = $query->select('id,uid')->one();
		$transaction = Yii::$app->db->beginTransaction();
		try {
			if ($shop > 0) {

				$uid = $params['uid'];
				$set = '';
				unset($params['uid']);
				foreach ($params as $k => $v) {
					$set .= '`' . $k . '`' . ' = ' . "'" . $v . "'" . ' , ';
				}
				$sql = "update `bb_51_shops` set `update_time` =" . time() . " , `status` = 0 , " . rtrim($set, ', ') . " where `uid` = {$uid}";

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("更新shop表失败");
				$result &= self::StoreErrandInDentityPic($shop['id'], $id_license_pic);
			} else {
				$field = '';
				$data  = '';
				unset($params['photo']);
				foreach ($params as $k => $v) {
					$field .= "`" . $k . "`" . ',';
					$data  .= "'" . $v . "'" . ',';
				}

				$sql = "INSERT INTO `bb_51_shops` (" . rtrim($field, ',') . ") VALUES (" . rtrim($data, ',') . ")";

				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("插入shop表新数据失败");

				$result &= self::StoreErrandInDentityPic(Yii::$app->db->getLastInsertID(), $id_license_pic);

			}

			if ($result) {
				//记录业务日志
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 存储摩的商家证件资料
	 */
	public static function StoreMotoInDentityPic($shop_id, $driver, $travel, $card)
	{

		$result = false;
		$picIds = '';

		//驾驶证记录添加
		$id     = self::addIdentityImageRecord("DRIVER_LICENSE", $shop_id, $driver['driver_license_pic_pos'], $driver['driver_license_pic_opp'], '驾驶证', $driver['driver_num']);
		$picIds .= $id . ',';

		//x行驶证记录添加
		$id     = self::addIdentityImageRecord('TRAVEL_LICENSE', $shop_id, $travel['travel_license_pic_pos'], $travel['travel_license_pic_opp'], '行驶证', $travel['travel_num']);
		$picIds .= $id . ',';

		//身份证记录添加
		$id     = self::addIdentityImageRecord('IDENTITY_CARD', $shop_id, $card['id_card_pic_pos'], $card['id_card_pic_opp'], '身份证', 0, $card['hand_id_card_pic']);
		$picIds .= $id;

		//更新商家字段
		$sql = "UPDATE `bb_51_shops` SET `identity_pic` ='{$picIds}'  where `id` = " . "'" . $shop_id . "'";
		Yii::$app->db->createCommand($sql)->execute();

		return true;
	}

	/**
	 * 存储电动车入驻证件资料
	 */
	public static function StoreElectroDentityPic($shop_id, $other, $car, $id_card)
	{

		$result = false;
		$picIds = '';

		//驾驶证记录添加
		$id     = self::addIdentityImageRecord("CAR_LICENSE", $shop_id, $car['car'], $car['car_man'], '车辆信息');
		$picIds .= $id . ',';

		//x行驶证记录添加
		$id     = self::addIdentityImageRecord('OTHER_LICENSE', $shop_id, $other['other_pos'], $other['other_opp'], '其他证件');
		$picIds .= $id . ',';

		//身份证记录添加
		$id     = self::addIdentityImageRecord('IDENTITY_CARD', $shop_id, $id_card['id_card_pos'], $id_card['id_card_opp'], '身份证', 0, $id_card['hand_id_card']);
		$picIds .= $id;

		//更新商家字段
		$sql = "UPDATE `bb_51_shops` SET `identity_pic` ='{$picIds}'  where `id` = " . "'" . $shop_id . "'";
		Yii::$app->db->createCommand($sql)->execute();

		return true;
	}

	/**
	 * 存储小帮快送商家证件资料
	 */
	public static function StoreErrandInDentityPic($shop_id, $card)
	{

		$result = false;
		$picIds = '';

		//身份证记录添加
		$id     = self::addIdentityImageRecord('IDENTITY_CARD', $shop_id, $card['id_card_pic_pos'], $card['id_card_pic_opp'], '身份证', $card['card_num'], $card['hand_id_card_pic']);
		$picIds .= $id;

		//删除驾驶证多余数据
		$driver_shop = (new Query())->from('bb_51_shop_identity_pic')->where(['identity' => 'DRIVER_LICENSE', 'shop_id' => $shop_id])->one();
		if ($driver_shop) {
			$sql = "UPDATE `bb_51_shop_identity_pic` SET `license_pos` ='0',`license_opp` ='0'  where `shop_id` ='{$shop_id}' and `identity` = 'DRIVER_LICENSE' ";
			Yii::$app->db->createCommand($sql)->execute();
		}
		$travel_shop = (new Query())->from('bb_51_shop_identity_pic')->where(['identity' => 'TRAVEL_LICENSE', 'shop_id' => $shop_id])->one();
		if ($travel_shop) {
			$sql = "UPDATE `bb_51_shop_identity_pic` SET `license_pos` ='0',`license_opp` ='0'  where `shop_id` ='{$shop_id}' and `identity` = 'TRAVEL_LICENSE' ";
			Yii::$app->db->createCommand($sql)->execute();
		}

		//

		//更新商家字段
		$sql = "UPDATE `bb_51_shops` SET `identity_pic` ='{$picIds}'  where `id` = " . "'" . $shop_id . "'";
		Yii::$app->db->createCommand($sql)->execute();

		return true;
	}

	/**
	 * 更新和新增数据
	 * @param      $licenseName
	 * @param      $shop_id
	 * @param      $license_pos
	 * @param      $license_opp
	 * @param null $remark
	 * @param int  $file_num
	 * @param bool $license_hand
	 * @return string
	 */
	public static function addIdentityImageRecord($licenseName, $shop_id, $license_pos, $license_opp, $remark = null, $file_num = 0, $license_hand = false)
	{
		$result              = false;
		$now                 = time();
		$driverLicenseRecord = (new Query())->select(['idpic_id'])->from(self::shopIdnetityPicTbl)->where(['shop_id' => $shop_id, 'identity' => $licenseName])->one();
		if ($driverLicenseRecord) {
			//存在记录,修改记录
			$value = '`license_pos` = ' . "'" . $license_pos . "'" . ',`license_opp`=' . "'" . $license_opp . "'" . ',`update_time`=' . "'" . $now . "'";
			if ($license_hand) {
				$value .= ',`license_hand`=' . "'" . $license_hand . "'";
			}
			if ($file_num)
				$value .= ',`file_num`=' . "'" . $file_num . "'";
			//重新入驻的证件一律视为重新审核
			$value .= ',`status`= 0,`check` = 0';

			$sql = "UPDATE `bb_51_shop_identity_pic` SET " . $value . " where `shop_id` = '{$shop_id}' and `identity` = '" . $licenseName . "'";
			Yii::$app->db->createCommand($sql)->execute();
			$id = $driverLicenseRecord['idpic_id'];
			//清除多余数据
		} else {
			//不存在记录,则新增记录
			if ($license_hand) {
				if ($file_num) {
					$value = "'{$shop_id}','{$licenseName}','{$license_pos}','{$license_opp}','{$license_hand}','{$remark}','{$now}','{$file_num}'";
					$sql   = "INSERT INTO `bb_51_shop_identity_pic` (`shop_id`,`identity`,`license_pos`,`license_opp`,`license_hand`,`remark`,`create_time`,`file_num`)  VALUE (" . $value . ") ";
				} else {
					$value = "'{$shop_id}','{$licenseName}','{$license_pos}','{$license_opp}','{$license_hand}','{$remark}','{$now}'";
					$sql   = "INSERT INTO `bb_51_shop_identity_pic` (`shop_id`,`identity`,`license_pos`,`license_opp`,`license_hand`,`remark`,`create_time`)  VALUE (" . $value . ") ";
				}
				Yii::$app->db->createCommand($sql)->execute();
			} else {
				$value = "'{$shop_id}','{$licenseName}','{$license_pos}','{$license_opp}','{$remark}','{$now}','{$file_num}'";
				$sql   = "INSERT INTO `bb_51_shop_identity_pic` (`shop_id`,`identity`,`license_pos`,`license_opp`,`remark`,`create_time`,`file_num`)  VALUE (" . $value . ") ";
				Yii::$app->db->createCommand($sql)->execute();
			}
			$id = Yii::$app->db->getLastInsertID();
		}

		return $id;
	}

	/**
	 * 查询商家资料
	 *
	 * @param $param     判断参数
	 * @param $type      参数类型(1:电话号码;2:店铺ID;3:用户ID)
	 */
	public static function shopDetailByMobile($mobile)
	{
		return (new Query())->from(self::shopTbl)->where(['utel' => $mobile])->one();
	}

	/**
	 * 存储商家审核资料
	 */
	public static function StoreShopPic($shop_id)
	{

	}

	/**
	 * 查询免保证金的数据 //TODO 数据放入缓存
	 * @param $provider_id
	 * @return bool
	 */
	public static function freeBailShop($provider_id)
	{
		$result   = false;
		$now_time = time();
		$shops    = FreeBailShop::find()->where(['shops_id' => $provider_id, 'status' => 1])->andWhere(['>', 'end_time', $now_time])->one();
		if ($shops) {
			$result = true;
		}

		return $result;
	}

	/**
	 * 小帮信息for订单详情页
	 */
	public static function providerForOrderView($provider_id, $mobile, $address)
	{
		//小帮信息
		$providerInfo = UserHelper::getShopInfo($provider_id);
		$id_image     = isset($providerInfo['shops_photo']) ? $providerInfo['shops_photo'] : 0;

		return [
			'provider_name'    => isset($providerInfo['shops_name']) ? $providerInfo['shops_name'] : "无忧帮帮",    //小帮昵称
			'provider_mobile'  => $mobile,                //小帮电话
			'provider_address' => $address,                //小帮接单地址
			'provider_photo'   => ImageHelper::getUserPhoto($id_image),//小帮头像
			'provider_star'    => '5.0',                                    //小帮评分
		];
	}

	/**
	 * 商家-更新在线状态
	 */
	public static function update($shops_id, $params = [])
	{
		$result = false;

		$params['update_time'] = time();

		$res = Yii::$app->db->createCommand()->update('bb_51_shops', $params, ["id" => $shops_id])->execute();
		$res ? $result = true : Yii::error("更新表新数据失败");

		return $result;
	}


	/**
	 * 根据用户ID信息获取商家信息
	 */
	public static function getShopInfoByUserId($user_id, $field = "*")
	{
		return (new Query())->select($field)->from(self::shopTbl)->where(['uid' => $user_id])->one();
	}

	/**
	 * 根据用户ID信息获取商家信息
	 */
	public static function getShopInfoByProviderId($provider_id, $field = "*")
	{
		return (new Query())->select($field)->from(self::shopTbl)->where(['id' => $provider_id])->one();
	}

	/**增加提现账户
	 * @param $params
	 * @return int
	 */
	public static function addDrawAway($params)
	{

		$insert_data = [
			'shops_id'    => $params['provider_id'],
			'verify'      => $params['account_type'],
			'brankname'   => $params['account_name'],
			'writer'      => $params['real_name'],
			'branknum'    => $params['account'],
			'is_binding'  => 0,
			'create_time' => time(),
		];

		return Yii::$app->db->createCommand()->insert("bb_51_shops_brank", $insert_data)->execute();
	}

	/**
	 * 删除提现账户
	 * @param $params
	 * @return int
	 */
	public static function deleteDrawAway($params)
	{
		$result    = Yii::$app->db->createCommand()->delete("bb_51_shops_brank", ['shops_id' => $params['provider_id'], 'id' => $params['account_id']])->execute();
		$shop_info = UserHelper::getShopInfo($params['provider_id'], ['brankid']);
		if ($shop_info['brankid'] == $params['account_id']) {
			$result &= Yii::$app->db->createCommand()->update("bb_51_shops", ['zfbname' => '', 'zfbnum' => '', 'zfbsname' => '', 'brankid' => ''], ['id' => $params['provider_id']])->execute();
		}

		return $result;
	}

	/**
	 * 检查提现账户是否存在
	 * @param $params
	 * @return bool
	 */
	public static function checkAccountExist($params)
	{
		$result = false;
		$res    = (new Query())->from("bb_51_shops_brank")->select('id')->where(['shops_id' => $params['provider_id'], 'branknum' => $params['account']])->one();
		if ($res) {
			$result = true;
		}

		return $result;
	}

	/**
	 * 提现账户列表
	 * @param $params
	 * @return bool
	 */
	public static function drawingList($params)
	{
		$result    = false;
		$draw_data = (new Query())->from("bb_51_shops_brank")->where(['shops_id' => $params['provider_id'], 'verify' => $params['type']])->all();
		if ($draw_data) {
			foreach ($draw_data as $key => $value) {
				$result[$key]['account_id']   = $value['id'];
				$result[$key]['account']      = $value['branknum'];
				$result[$key]['is_binding']   = $value['is_binding'];
				$result[$key]['account_name'] = $value['brankname'];
				$result[$key]['real_name']    = $value['writer'];
			}
		}

		return $result;
	}

	/**
	 * 绑定提现账号
	 * @param $params
	 * @return bool|int
	 */
	public static function drawingBind($params)
	{
		$result       = false;
		$account_info = (new Query())->from("bb_51_shops_brank")->where(['id' => $params['account_id'], 'shops_id' => $params['provider_id']])->one();
		if ($account_info) {
			if ($account_info['is_binding'] == 1) {
				return $result = true;
			}

			$transaction = Yii::$app->db->beginTransaction();
			try {
				//把之前绑定的改成不绑定
				Yii::$app->db->createCommand()->update("bb_51_shops_brank", ['is_binding' => 0], ['shops_id' => $params['provider_id'], 'is_binding' => 1])->execute();
				//绑定传过来的提现账户
				$result = Yii::$app->db->createCommand()->update("bb_51_shops_brank", ['is_binding' => 1], ['id' => $params['account_id'], 'shops_id' => $params['provider_id']])->execute();
				//更新商家表
				$update_data = [
					'zfbname'     => $account_info['brankname'],
					'zfbnum'      => $account_info['branknum'],
					'zfbsname'    => $account_info['writer'],
					'brankid'     => $account_info['id'],
					'update_time' => time(),
				];
				$result      &= Yii::$app->db->createCommand()->update("bb_51_shops", $update_data, ['id' => $params['provider_id']])->execute();
				if ($result) {
					$transaction->commit();
				}
			}
			catch (Exception $e) {
				$transaction->rollBack();
			}
		}

		return $result;

	}

	/**
	 * 商家提现
	 * @param $params
	 * @return bool|int
	 */
	public static function drawingSave($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$shop_info = UserHelper::getShopInfo($params['provider_id'], ['shops_money', 'shops_freeze_money', 'uid']);
			$bank_info = (new Query())->from("bb_51_shops_brank")->where(['id' => $params['binding_id'], 'shops_id' => $params['provider_id']])->one();
			//修改商家表的金钱和冻结金额
			$shop_data = [
				'shops_money'        => $shop_info['shops_money'] - $params['money'],
				'shops_freeze_money' => $shop_info['shops_freeze_money'] + $params['money'],
			];
			$shop_res  = Yii::$app->db->createCommand()->update("bb_51_shops", $shop_data, ['id' => $params['provider_id']])->execute();
			$result    = $shop_res ? true : false;
			//插入提现记录表
			$withdraw_data = [
				'shops_id'    => $params['provider_id'],
				'incomeid'    => 0,
				'brankname'   => $bank_info['brankname'],
				'zfbname'     => $bank_info['writer'],
				'zfbnum'      => $bank_info['branknum'],
				'money'       => $params['money'],
				't_money'     => $params['money'],
				'create_time' => time(),
				'update_time' => time(),
			];
			$withdraw_res  = Yii::$app->db->createCommand()->insert("bb_51_retixian", $withdraw_data)->execute();
			$withdraw_id   = 0;
			if ($withdraw_res) {
				$withdraw_id = Yii::$app->db->getLastInsertID();
			}
			$result &= $withdraw_res;
			//插入商家收支记录
			$income_data = [
				'shops_id'     => $params['provider_id'],
				'order_id'     => $withdraw_id,
				'staffuid'     => $shop_info['uid'],
				'type'         => 2,
				'account_type' => 2,
				'money'        => $params['money'],
				'balance'      => $shop_info['shops_money'] - $params['money'],
				'title'        => '提现',
				'create_time'  => time(),
				'update_time'  => 0,
				'status'       => 0,
			];
			$income_res  = Yii::$app->db->createCommand()->insert("bb_51_income_shop", $income_data)->execute();
			$income_id   = 0;
			if ($income_res) {
				$income_id = Yii::$app->db->getLastInsertID();
			}
			$result &= $income_res;

			if ($withdraw_id && $income_id) {
				$update_res = Yii::$app->db->createCommand()->update("bb_51_retixian", ['incomeid' => $income_id], ['id' => $withdraw_id])->execute();
				$result     &= $update_res;
			}

			if ($result) {
				$transaction->commit();
			}

		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 判断提现金额是否大于商家余额
	 * @param $money
	 * @param $provider_id
	 * @return bool
	 */
	public static function checkDrawMoney($money, $provider_id)
	{
		$result    = false;
		$shop_info = UserHelper::getShopInfo($provider_id, ['shops_money']);
		if ($shop_info) {
			$result = $shop_info['shops_money'] >= $money ? true : false;
		}

		return $result;
	}

	/**
	 * 检查是否有解冻保证金资格（时间，金钱，解冻记录）
	 * @param $provider_id
	 * @param $money
	 * @return bool
	 */
	public static function checkBailQualification($provider_id, $money)
	{
		$result    = false;
		$shop_info = UserHelper::getShopInfo($provider_id, ['bail_time', 'bail_money']);
		if ($shop_info) {
			if (time() > ($shop_info['bail_time'] + 3600 * 24 * Ref::BAIL_RETURN_DAY)) $result = true;
			$shop_info['bail_money'] >= $money ? $result &= true : $result &= false;
		}
		$withdraw_count = (new Query())->from("bb_51_bail_thaw_record")->select(["count(thaw_id) as withdraw_count"])->where(['shops_id' => $provider_id, 'status' => 0])->one();
		$withdraw_count['withdraw_count'] > 0 ? $result &= false : $result &= true;

		return $result;
	}

	/**
	 * 保证金提现解冻
	 * @param $params
	 * @return bool
	 */
	public static function thawBail($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$accountData = (new Query())->from("bb_51_shops_brank")->where(['id' => $params['account_id'], 'shops_id' => $params['provider_id']])->one();
			if ($accountData) {
				$thaw_data = [
					'shops_id'     => $params['provider_id'],
					'uid'          => $params['user_id'],
					'brank_id'     => $params['account_id'],
					'thaw_money'   => $params['money'],
					'create_time'  => time(),
					'bank_name'    => $accountData['brankname'],
					'account_name' => $accountData['writer'],
					'account_num'  => $accountData['branknum'],
				];
				$result    = Yii::$app->db->createCommand()->insert("bb_51_bail_thaw_record", $thaw_data)->execute();
				$thaw_id   = 0;
				if ($result) {
					$thaw_id = Yii::$app->db->getLastInsertID();
				}

				$sql         = "UPDATE bb_51_shops SET bail_money=bail_money-{$params['money']},shops_freeze_money=shops_freeze_money+{$params['money']} WHERE id={$params['provider_id']}";
				$result      &= Yii::$app->db->createCommand($sql)->execute();
				$shops_money = UserHelper::getShopInfo($params['provider_id'], ['shops_money']);
				$income_data = [
					'shops_id'     => $params['provider_id'],
					'order_id'     => $thaw_id,
					'staffuid'     => $params['user_id'],
					'type'         => 7,
					'account_type' => 2,
					'money'        => $params['money'],
					'balance'      => $shops_money['shops_money'],
					'title'        => "解冻保证金" . $params['money'] . '元',
					'create_time'  => time(),
					'update_time'  => 0,
					'status'       => 0
				];
				$result      &= Yii::$app->db->createCommand()->insert("bb_51_income_shop", $income_data)->execute();
				if ($result) {
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}


	/**
	 * 可入驻分类
	 * @param $user_id
	 */
	public static function applyCategories($city_id)
	{
		$result = false;
		//TODO	暂时写死,后期处理

		$data = [
			[
				'cid'     => '46',
				'name'    => "交通出行",
				"catepic" => "https://img02.281.com.cn/cateList/Uploads/Picture/2017-07-31/cate_transporlation.png",
				"list"    => [
					["id" => 51, "name" => "小帮出行", "type" => 1],
					//					["id"=>110,"name"=>"车辆维修","type"=>4],
					//					["id"=>54,"name"=>"顺风车","type"=>4],
					//					["id"=>53,"name"=>"出租车","type"=>4],
					//					["id"=>47,"name"=>"租车","type"=>4]
				]
			],
			[
				'cid'     => '46',
				'name'    => "同城物流",
				"catepic" => "https://img02.281.com.cn/Uploads/catelist/20170810/home_menu_run.png",
				"list"    => [
					["id" => 132, "name" => "小帮快送", "type" => 3],
					//					["id"=>132,"name"=>"帮我送","type"=>3],
					//					["id"=>132,"name"=>"帮我办","type"=>3],
				]
			],


		];

		return $data;

		$cate   = [
			[
				'name'      => '小帮出行',
				'value'     => '51',
				'cate_type' => 2,
				'open'      => 1,
			],
			//			[
			//				'name'      => '电动自行车',
			//				'value'     => '135',
			//				'cate_type' => 3,
			//				'open'      => 1,
			//			],
			[
				'name'      => '小帮快送',
				'value'     => '132',
				'cate_type' => 4,
				'open'      => 1,
			],
			[
				'name'      => '小帮货车',
				'value'     => '133',
				'cate_type' => 1,
				'open'      => 0,
			],
			[
				'name'      => '小帮专车',
				'value'     => '55',
				'cate_type' => 1,
				'open'      => 0,
			],
			[
				'name'      => '小帮维修',
				'value'     => '4',
				'cate_type' => 1,
				'open'      => 0,
			],
			[
				'name'      => '小帮外卖',
				'value'     => '34',
				'cate_type' => 1,
				'open'      => 0,
			],
			[
				'name'      => '小帮搬家',
				'value'     => '2',
				'cate_type' => 1,
				'open'      => 0,
			],
			[
				'name'      => '小帮水电',
				'value'     => '132',
				'cate_type' => 1,
				'open'      => 0,
			],
		];
		$result = $cate;

		return $result;
	}

	/**
	 * 保证金支付
	 * @param $params
	 * @return array|bool
	 */
	public static function bailPay($params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$record_data = [
				'shops_id'    => $params['provider_id'],
				'uid'         => $params['user_id'],
				'money'       => $params['bail_money'],
				'bail_sn'     => date("YmdHis") . rand(11111, 99999),
				'create_time' => time(),
				'pay_status'  => 0,
				'payment'     => $params['payment_id'],
				'rmark'       => '缴纳保证金' . $params['bail_money'] . '元',
			];
			$record_res  = Yii::$app->db->createCommand()->insert("bb_51_bail_record", $record_data)->execute();

			if ($record_res) {
				$result      = true;
				$record_id   = Yii::$app->db->getLastInsertID();
				$tran_params = [
					'payment_id' => $params['payment_id'],
					'type'       => Ref::TRANSACTION_TYPE_BAIL,
				];
				$tradeRes    = TransactionHelper::createTrade($record_id, $params['bail_money'], $tran_params);
				$result      &= $tradeRes;

				if ($result) {
					$result = [
						'payment_id'     => $params['payment_id'],
						'transaction_no' => $tradeRes['transaction_no'],
						'fee'            => $tradeRes['fee'],
						'transaction_id' => $tradeRes['id'],
					];
					$transaction->commit();
				}
			}

		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 保证金支付成功
	 * @param      $transaction_no
	 * @param      $trade_no
	 * @param      $fee
	 * @param null $remark
	 * @param null $data
	 * @return bool
	 */
	public static function bailPaySuccess($transaction_no, $trade_no, $fee, $remark = null, $data = null)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$tradeData = TransactionHelper::updateTrade($transaction_no, $trade_no, $fee, $remark, $data);
			if ($tradeData) {
				$now_time = time();
				//1、更新bail_record表
				$record_id   = $tradeData['ids_ref'];
				$record_data = [
					'pay_status'    => 1,
					'trade_no'      => $trade_no,
					'finished_time' => $now_time,
				];
				$result      = Yii::$app->db->createCommand()->update("bb_51_bail_record", $record_data, ['id' => $record_id])->execute();
				$recordInfo  = (new Query())->from("bb_51_bail_record")->select(['shops_id'])->where(['id' => $record_id])->one();
				$provider_id = $recordInfo['shops_id'];

				//1.1使用余额支付
				$shop_info   = UserHelper::getShopInfo($provider_id, ['uid', 'shops_money']);
				$shops_money = $shop_info['shops_money'];
				if ($tradeData['payment_id'] == Ref::PAYMENT_TYPE_BALANCE) {

					($shops_money - $fee < 0) ? $result = false : $shops_money -= $fee;
					//更新扣减的余额
					$up = WalletHelper::handleIncomeShop($provider_id, $shop_info['uid'], $trade_no, $fee, '余额支付保证金' . $fee . '元', 10, 2, $shops_money);
					$up ? '' : $result = false;
				}

				//2、更新商家的保证金金额和对应的余额
				$sql       = "UPDATE bb_51_shops SET bail_money=bail_money+{$fee},bail_time={$now_time} ,shops_money ={$shops_money} WHERE id={$provider_id}";
				$upShopRes = Yii::$app->db->createCommand($sql)->execute();
				if (!$upShopRes) {
					$result = false;
					Yii::$app->debug->log_info("bailPaySuccess_save_shops", $sql);
				}

				//3、更新商家流水表income
				$saveIncomeShopRes = WalletHelper::handleIncomeShop($provider_id, $shop_info['uid'], $trade_no, $fee, '缴纳保证金' . $fee . '元', 8, 1);
				if (!$saveIncomeShopRes) {
					$result = false;
					Yii::$app->debug->log_info("bailPaySuccess_insert_income_shop", '');
				}

				if ($result) {
					$transaction->commit();
				}
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 获取申请审核的结果信息
	 *
	 * @param $user_id
	 * @param $status
	 * @return string
	 */
	public static function getApplyResultMessage($user_id, $status)
	{
		$result = '等待审核!';
		if ($status == Ref::SHOP_STATUS_PASS) {
			$data   = (new Query())->from("bb_51_shops_verify")->select("text")->where(['uid' => $user_id, 'verify' => 1])->orderBy(['addtime' => SORT_DESC])->one();
			$result = isset($data['text']) ? $data['text'] : "申请商家审核成功";
		} elseif ($status == Ref::SHOP_STATUS_FAIL) {
			$data   = (new Query())->from("bb_51_shops_verify")->select("text")->where(['uid' => $user_id, 'verify' => 2])->orderBy(['addtime' => SORT_DESC])->one();
			$result = isset($data['text']) ? $data['text'] : "申请商家审核失败";
		}

		return $result;
	}

	/**
	 * 获取绑定账号信息
	 * @param $provider
	 * @return bool
	 */
	public static function getThawAccount($provider)
	{
		$result       = false;
		$account_info = (new Query())->from("bb_51_shops_brank")->select(['id', 'brankname', 'branknum'])->where(['shops_id' => $provider, 'is_binding' => 1])->one();
		if ($account_info) {
			if (strpos($account_info['branknum'], "@") === false) {
				$binding_text = $account_info['brankname'] . '(***' . mb_substr($account_info['branknum'], -4, 4) . ')';
			} else {
				$account_arr  = explode("@", $account_info['branknum']);
				$binding_text = $account_info['brankname'] . '(' . mb_substr($account_arr[0], 0, 2) . '***@' . $account_arr[1] . ')';
			}
			$result['is_binding']   = 1;
			$result['binding_id']   = $account_info['id'];
			$result['binding_text'] = $binding_text;
		}

		return $result;
	}

	/**
	 * 判断小帮是否交保证金/免保证金
	 *
	 * @param $shop_id
	 * @return bool
	 */
	public static function judgeBail($shop_id)
	{
		$free_shop = ShopHelper::freeBailShop($shop_id);
		if ($free_shop) {
			return $result = true;
		}

		$shop_data  = UserHelper::getShopInfo($shop_id, "bail_money");
		$bail_money = isset($shop_data['bail_money']) ? $shop_data['bail_money'] : 0;
		$result     = $bail_money >= Ref::BAIL_MONEY ? true : false;

		return $result;
	}


	//是否封号
	public static function isBlacklist($shop_id)
	{
		$result = false;
		$data   = UserHelper::getShopInfo($shop_id, "guangbi");    //0封号1不封号2临时封号
		if ($data) {
			$result = $data['guangbi'] == 1 ? false : true;
		}

		return $result;
	}

	/**
	 * 获取全民营销二维码相关信息
	 * @param $userId
	 * @return bool
	 */
	public static function getMarketCode($userId)
	{
		$result     = false;
		$provider   = self::getShopInfoByUserId($userId, ["shops_name", "shops_photo"]);
		$userMarket = UserMarket::findOne(['user_id' => $userId]);
		if ($provider && $userMarket->market_code) {
			$provider['shops_name'] = $provider['shops_name'] ? $provider['shops_name'] : "无忧小帮";
			$showName               = $provider['shops_name'];
			$provider['shops_name'] = UtilsHelper::customString($provider['shops_name'], "middle", "*", 4, 1, 1);
			$qrcode                 = ImageHelper::getMarketCode($userMarket->market_code, $provider['shops_name'], Ref::USER_TYPE_PROVIDER);
			if ($qrcode) {
				$result = [
					'show_code'     => $qrcode['show'],
					'download_code' => $qrcode['download'],
					'score_detail'  => "服务很好的小帮",
					'head_url'      => ImageHelper::getUserPhoto($provider['shops_photo']),
					'score'         => 1.5,//TODO 暂时写死，待确定
				];
			}
		}

		return $result;
	}

	/**
	 * 获取符合条件的在线小帮
	 * @param $orderData
	 * @return array
	 */
	public static function getOnlineProviderData($orderData)
	{

		//开发思路
		//1、地级市和县级市的条件判断
		//2、查找上班的数据

		$cityId          = $orderData['city_id'];
		$areaId          = $orderData['area_id'];
		$Location_result = RegionHelper::getAddressIdByLocation($orderData['start_location'], $cityId);
		$where = [
			'shop_login_status' => 1,
			'city_id'           => $cityId,
		];
		if ($Location_result['type'] == 2) {
			$where['area_id'] = $areaId;
		}

		//查询结果按照一下格式返回 二维数据
		$data = (new Query())->select([
			'utel AS mobile',
			'id AS provider_id',
			'uid AS user_id',
			'shops_location AS _location',
			'range AS range',
		])->from('bb_51_shops')
			->where($where)
			->andWhere(['like', 'type_second', Ref::CATE_ID_FOR_ERRAND])->all();

		return $data;
	}
}