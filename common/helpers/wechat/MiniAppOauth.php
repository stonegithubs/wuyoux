<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/27
 */

namespace common\helpers\wechat;

use common\components\Ref;
use common\helpers\HelperBase;
use common\models\users\UserMiniToken;
use Yii;

class MiniAppOauth extends HelperBase
{

	const PREFIX_TOKEN = "mini_token_";

	//TODO 小程序企业送授权过程
	public static function miniExample($code)
	{
		//开发思路
		//1、通过code到微信端获取openid+session_key+unionid
		//2、校验当前是否存在openid+unionid
		//2.1、生成授权的access_token

		$data = Yii::$app->mini_biz->getMiniApp()->auth->session($code);
		//todo
	}

	/**
	 * 获取企业送的授权信息
	 *
	 * @param $code
	 * @return array
	 */
	public static function getBizMiniOauth($code)
	{

		$mini_type = Ref::MINI_TYPE_BIZ;
		$result    = [
			'role'         => 'fail',
			'access_token' => '',
			'user_id'      => 0
		];
		$token     = md5(Yii::$app->security->generateRandomString());
		$data      = Yii::$app->mini_biz->getMiniApp()->auth->session($code);
		if (isset($data['openid'])) {

			$result['role'] = 'guest';
			$model          = UserMiniToken::findOne(['openid' => $data['openid'], 'mini_type' => $mini_type]);
			if ($model) {
				$model->session_key = $data['session_key'];
				$model->update_time = time();
				$model->user_id > 0 ? $result['role'] = 'auth' : null;
			} else {
				$model              = new UserMiniToken();
				$model->mini_type   = $mini_type;
				$model->user_id     = 0;
				$model->attributes  = $data;
				$model->create_time = time();

			}
			$model->access_token = $token;
			if ($model->save()) {
				$result['access_token'] = $token;
				$result['user_id']      = $model->user_id;
				$data['user_id']        = $model->user_id;
				Yii::$app->cache->set(self::PREFIX_TOKEN . $mini_type . $token, $data, 86400);                //token为主键
			}
		}

		return $result;
	}

	//获取token
	public static function getMiniToken($access_token, $type)
	{
		return Yii::$app->cache->get(self::PREFIX_TOKEN . $type . $access_token);
	}

	//删除token
	public static function deleteToken($access_token, $type)
	{
		Yii::$app->cache->delete(self::PREFIX_TOKEN . $type . $access_token);
	}

	/**
	 * 设置用户ID
	 * @param $token
	 * @param $user_id
	 * @return bool
	 */
	public static function setUserId($token, $user_id, $mini_type)
	{
		$result    = false;
		$new_token = md5(Yii::$app->security->generateRandomString());
		$model     = UserMiniToken::findOne(['access_token' => $token]);
		if ($model) {
			$model->user_id      = $user_id;
			$model->update_time  = time();
			$model->access_token = $new_token;

			if ($model->save()) {
				$result = [
					'access_token' => $new_token,
					'expire'       => 7200,
				];
				//重新设置数据
				$data = [
					'user_id'     => $model->user_id,
					'openid'      => $model->openid,
					'unionid'     => $model->unionid,
					'session_key' => $model->session_key,
				];

				Yii::$app->cache->set(self::PREFIX_TOKEN . $mini_type . $new_token, $data, 86400);                //token为主键
				self::deleteToken($token, $mini_type);//移除旧token
			}
		}

		return $result;
	}

}

