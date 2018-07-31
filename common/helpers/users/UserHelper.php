<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/30
 */

namespace common\helpers\users;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\models\users\BizInfo;
use common\models\users\UserMarket;
use common\models\users\UserToken;
use yii\db\Query;
use yii\db\Exception;
use Yii;

class UserHelper extends HelperBase
{
	const  userTbl     = "bb_51_user";
	const  userDataTbl = "bb_51_userdata";

	const  PREFIX_TOKEN = "USER_TOKEN";

	/**
	 * 从redis 里面获取用户信息
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	public static function getRedisUserInfo($user_id)
	{

		$result = false;
		$redis  = Yii::$app->redis;
		$redis->select(0);
		$key  = "userInfo:" . $user_id;
		$data = $redis->get($key);
		if ($data) {
			$data = json_decode($data, true);

			$params['is_shop']     = isset($data['is_shop']) ? $data['is_shop'] : 0;
			$params['user_id']     = isset($data['user_id']) ? $data['user_id'] : 0;
			$params['shop_id']     = isset($data['shop_id']) ? $data['shop_id'] : 0;
			$params['work_status'] = isset($data['work_status']) ? $data['work_status'] : 0;
			$params['client_id']   = isset($data['client_id']) ? $data['client_id'] : 0;

			if ($params['user_id'] > 0)
				$result = $params;
		}

		return $result;
	}

	/**
	 *  从redis 里面获取店铺信息
	 *
	 * @param $shop_id
	 *
	 * @return bool
	 */
	public static function getRedisShopInfo($shop_id)
	{

		$result = false;
		$redis  = Yii::$app->redis;
		$redis->select(0);
		$key  = "shopInfo:" . $shop_id;
		$data = $redis->get($key);

		if ($data) {
			$data = json_decode($data, true);

			$params['is_shop']     = isset($data['is_shop']) ? $data['is_shop'] : 0;
			$params['user_id']     = isset($data['user_id']) ? $data['user_id'] : 0;
			$params['shop_id']     = isset($data['shop_id']) ? $data['shop_id'] : 0;
			$params['work_status'] = isset($data['work_status']) ? $data['work_status'] : 0;
			$params['client_id']   = isset($data['client_id']) ? $data['client_id'] : 0;

			if ($params['user_id'] > 0)
				$result = $params;
		}

		return $result;
	}

	public static function getUserInfo($uid, $select = "*")
	{
		return (new Query())->select($select)->from("bb_51_user")->where(['uid' => $uid])->one();
	}

	public static function getShopInfo($shop_id, $select = "*")
	{
		return (new Query())->select($select)->from("bb_51_shops")->where(['id' => $shop_id])->one();
	}

	/**
	 * 检查当前用户和手机是否吻合
	 * @param $mobile
	 * @param $user_id
	 * @return bool
	 */
	public static function checkUserMobile($mobile, $user_id)
	{
		$result    = false;
		$user_info = self::getUserInfo($user_id, 'mobile');
		if ($user_info) {
			$result = $user_info['mobile'] == $mobile ? true : false;
		}

		return $result;
	}

	/**
	 * 检查手机是否存在，存在返回用户信息
	 * @param $mobile
	 * @return array|bool
	 */
	public static function checkMobileExist($mobile)
	{
		return $result = (new Query())->from('bb_51_user')->select(['mobile', 'uid', 'password'])->where(['mobile' => $mobile])->one();
	}


	public static function verifyUserLoginInfo($params)
	{
		$result = false;
		$field  = "score,uid,is_shops,userphoto,nickname,sex,mobile,birthday,paypassword,password,status";
		$data   = (new Query())->select($field)->from("bb_51_user")->where(['mobile' => $params['account']])->one();
		if (!empty($data)) {
			$password = md5(sha1($params['password']));
			if ($password == $data['password']) {
				$result = $data;
				if ($data['status'] == 1) {
					//更新登录信息
					$host = Yii::$app->request->getUserHost() ? Yii::$app->request->getUserHost() : Yii::$app->request->userIP;
					$sql  = "update  `" . self::userTbl . "` set `login_status`=1,`last_login_time`=" . time() . ",";
					$sql  .= " `last_login_ip`=\"" . strval($host) . "\"";
					$sql  .= " where `uid`=" . $data['uid'];
					Yii::$app->db->createCommand($sql)->execute();
				}
			}
		}

		return $result;
	}

	/**
	 * 更新旧表的bb_51_userdata
	 *  TODO 所有接口从TP框架迭代过来就可以移除该方法了
	 * @param $params
	 * @return bool|string
	 */
	public static function updateOldUserToken($params)
	{
		$result = false;
		$key    = $params['token'];
		$data   = (new Query())->select("id,uid")->from(self::userDataTbl)->where(['uid' => $params['user_id']])->one();

		//兼容旧数据
		if ($params['app_type'] == 1) {
			$params['app_type'] = 2;
		} else {
			$params['app_type'] = 1;
		}

		if (!empty($data)) {

			//更新登录信息
			$sql = "update " . self::userDataTbl . " set value='" . $key . "',tool='" . $params['app_type'] . "' ,";
			$sql .= " versionName='" . $params['app_version'] . "', gtui='" . $params['client_id'] . "'";
			$sql .= " where uid=" . $data['uid'];
			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("login update user token failed");

		} else {

			$field = '(uid,name,value,tool,versionName,gtui,win)';
			$sql   = "insert into  bb_51_userdata  {$field} values ";
			$sql   .= "('{$params['user_id']}','userkey','{$key}','{$params['app_type']}','{$params['app_version']}','{$params['client_id']}',1 ) ;";

			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("login insert user token failed");
		}

		if ($result) $result = $key;

		return $result;
	}

	private static function _saveUserToken($params, $new_token, $prefix, $expire)
	{
		//1、清空cache的user数据
		$userToken = UserToken::findOne(['user_id' => $params['user_id'], 'role' => $params['role']]);//用户版
		if ($userToken) {
			$userToken->attributes  = $params;
			$userToken->update_time = time();
			Yii::$app->cache->delete($prefix . $userToken->access_token);
		} else {
			$userToken              = new UserToken();
			$userToken->attributes  = $params;
			$userToken->create_time = time();
		}
		$userToken->access_token = $new_token;
		$userToken->user_id      = $params['user_id'];
		$userToken->provider_id  = $params['provider_id'];
		$userToken->expire       = $expire;
		$userToken->save();

		$params['token'] = $new_token;
		self::updateOldUserToken($params);    //TODO 所有接口迁移到新架构后可以移除

	}

	//设置用户的token
	public static function setUserToken($params)
	{
		$token                 = md5(Yii::$app->security->generateRandomString());
		$prefix_ID             = "USER_TOKEN_USER";
		$expire                = 86400;
		$params['provider_id'] = 0;
		self::_saveUserToken($params, $token, self::PREFIX_TOKEN, $expire);
		$data = [
			'user_id'     => $params['user_id'],
			'provider_id' => 0,    //用户token不存储小帮ID
			'client_id'   => $params['client_id'],
			'role'        => $params['role'],
		];
		Yii::$app->cache->set(self::PREFIX_TOKEN . $token, $data, $expire);                //token为主键
		Yii::$app->cache->set($prefix_ID . $params['user_id'], $data, $expire);    //user_id为主键

		$result = [
			'access_token' => $token,
			'expire'       => $expire,
		];

		return $result;
	}

	/**
	 * 获取用户的token
	 *
	 * @param $access_token
	 * @return bool|mixed
	 */
	public static function getUserToken($access_token)
	{
		$result = false;
		$data   = Yii::$app->cache->get(self::PREFIX_TOKEN . $access_token);
		$data ? $result = $data : null;

		return $result;
	}

	//设置小帮的token
	public static function setProviderToken($params, $provider_id)
	{
		$token                 = md5(Yii::$app->security->generateRandomString());
		$prefix_ID             = "USER_TOKEN_PROVIDER";
		$expire                = 86400;
		$params['provider_id'] = $provider_id;
		self::_saveUserToken($params, $token, self::PREFIX_TOKEN, $expire);
		$data = [
			'user_id'     => $params['user_id'],
			'provider_id' => $provider_id,
			'client_id'   => $params['client_id'],
			'role'        => $params['role'],
			'app_data'    => $params
		];
		Yii::$app->cache->set(self::PREFIX_TOKEN . $token, $data, $expire);                //token为主键
		Yii::$app->cache->set($prefix_ID . $provider_id, $data, $expire);    //未成为小帮 该值为0

		$result = [
			'access_token' => $token,
			'expire'       => $expire,
		];

		return $result;
	}

	/**
	 * 微信公众号进入m站的授权
	 * @param $params
	 * @return array
	 * @throws \yii\base\Exception
	 */
	public static function setWxToken($params)
	{
		$token  = md5(Yii::$app->security->generateRandomString());
		$expire = 86400 * 7;
		$data   = [
			'user_id'     => $params['user_id'],
			'provider_id' => 0,
			'role'        => 'user',
			'app_data'    => $params
		];
		Yii::$app->cache->set(self::PREFIX_TOKEN . $token, $data, $expire);                //token为主键

		return $token;
	}

	//删除token
	public static function deleteToken($access_token)
	{
		Yii::$app->cache->delete(self::PREFIX_TOKEN . $access_token);
	}

	/**
	 * 保存反馈信息
	 * @param $user_id
	 * @return bool
	 */
	public static function feedBack($user_id)
	{
		$content     = SecurityHelper::getBodyParam('content');
		$insert_data = [
			'uid'         => $user_id,
			'content'     => $content,
			'create_time' => time(),
			'status'      => 0,
		];
		$res         = Yii::$app->db->createCommand()->insert("bb_51_feedback", $insert_data)->execute();
		$result      = $res ? true : false;

		return $result;
	}

	/**
	 * 验证密码
	 * @param $params
	 * @return bool
	 */
	public static function checkPassword($params)
	{
		$user_info     = (new Query())->from("bb_51_user")->select("password")->where(['mobile' => $params['mobile']])->one();
		$past_password = isset($user_info['password']) ? $user_info['password'] : null;

		return $result = SecurityHelper::encryptPassword($params['new_password']) == $past_password ? true : false;
	}

	/**
	 * 注册
	 * @param $params
	 * @return int
	 */
	public static function signUp($params)
	{
		$insert_data = [
			'nickname'     => isset($params['nickname']) ? $params['nickname'] : '帮帮用户',
			'mobile'       => $params['mobile'],
			'password'     => SecurityHelper::encryptPassword($params['password']),
			'city_id'      => RegionHelper::getCityIdByLocation($params['user_location'], 0),
			'n_location'   => $params['user_location'],
			'reg_ip'       => Yii::$app->request->getUserIP(),
			'reg_time'     => time(),
			'status'       => 1,
			'parent_id'    => isset($params['invite_id']) ? $params['invite_id'] : 0,
			'register_src' => $params['register_src'],
		];

		$result = Yii::$app->db->createCommand()->insert(self::userTbl, $insert_data)->execute();

		return $result > 0 ? true : false;
	}

	/**
	 * 修改密码
	 * @param $params
	 * @return int
	 */
	public static function findPassword($params)
	{
		$new_password = SecurityHelper::encryptPassword($params['new_password']);

		return $result = Yii::$app->db->createCommand()->update("bb_51_user", ['password' => $new_password], ['mobile' => $params['mobile']])->execute();
	}

	//根据传入的条件和查询字段查询数据
	public static function selectUserInfo($conditions, $select = "*")
	{
		return (new Query())->select($select)->from("bb_51_user")->where($conditions)->one();
	}


	/**
	 * 获取全民营销二维码相关信息
	 * @param $userId
	 * @return bool
	 */
	public static function getMarketCode($userId)
	{
		$result = false;
		$user = self::getUserInfo($userId,["nickname","userphoto","mobile"]);
		$userMarket = UserMarket::findOne(['user_id'=>$userId]);
		if($user && $userMarket->market_code){
			$nickName = $user['nickname']?$user['nickname']:"帮帮用户({$user['mobile']})";
			$userType = 1;
			$bizInfo = BizInfo::findOne(['status'=>1,'user_id'=>$userId]);
			if($bizInfo){
				$nickName = $bizInfo->biz_name;
				$userType = 2;
			}
			$showName = $nickName;
			$nickName = UtilsHelper::customString($nickName,"middle","*",4,1,1);
			$qrcode = ImageHelper::getMarketCode($userMarket->market_code,$nickName,Ref::USER_TYPE_USER);
			if($qrcode){

				$result = [
					'show_code'=>$qrcode['show'],
					'download_code'=>$qrcode['download'],
					'user_name'=>$showName,
					'head_url'=> ImageHelper::getUserPhoto($user['userphoto']),
					'user_type'=>$userType
				];
			}
		}

		return $result;
	}
}