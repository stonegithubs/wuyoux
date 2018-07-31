<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/11
 */

namespace wechat\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\models\users\BizInfo;
use common\models\users\UserWechatRel;
use yii\db\Query;
use Yii;
use yii\web\User;

class WxClientHelper extends HelperBase
{

	const WX_TOKEN = 'wx_token';

	/**
	 * openID 获取用户ID
	 * @param $openId
	 * @return bool|int
	 */
	public static function getUserIdByOpenId($openId)
	{
		$result = false;
		$model  = UserWechatRel::findOne(['openid' => $openId]);
		if ($model) {

			$result = $model->user_id;
		}

		return $result;
	}

	/**
	 * 根据用户ID设置token
	 * @param $userId
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public static function setAccessToken($userId)
	{
		$token = Yii::$app->security->generateRandomString();

		Yii::$app->cache->set(self::WX_TOKEN . $token, $userId);

		return $token;
	}

	/**
	 * 检查cache是否存在
	 * @param $token
	 * @return mixed
	 */
	public static function checkToken($token)
	{
		$result = Yii::$app->cache->get(self::WX_TOKEN . $token);

		if (!$result) {

			//cache不存在时查询表结构
			$model = UserWechatRel::findOne(['access_token' => $token]);
			if ($model) {
				$result = $model->user_id;
			}

		}

		return $result;
	}

	/**
	 * 删除cache
	 * @param $token
	 */
	public static function deleteAccessToken($token)
	{
		Yii::$app->cache->delete(self::WX_TOKEN . $token);

		$model = UserWechatRel::findOne(['access_token' => $token]);
		if ($model) {
			$model->delete();
		}
	}

	/**
	 * 更新token到表
	 * @param $userId
	 * @param $token
	 */
	public static function updateAccessToken($userId, $token)
	{
		$model = UserWechatRel::findOne(['user_id' => $userId]);
		if ($model) {

			$model->access_token = $token;
			$model->update_time  = time();
			$model->save();
		}
	}

	/**
	 * 微信登录
	 * @param      $userId
	 * @param      $openId
	 * @param null $unionId
	 * @return string
	 */
	public static function wxLogin($userId, $openId, $unionId = null)
	{
		$model = UserWechatRel::findOne(['openid' => $openId]);
		if (!$model) {
			$model              = new UserWechatRel();
			$model->create_time = time();
		}

		$token               = self::setAccessToken($userId);
		$model->access_token = $token;
		$model->user_id      = $userId;
		$model->openid       = $openId;
		$model->app_type     = Ref::MINI_TYPE_MP;
		$model->unionid      = $unionId;
		$model->update_time  = time();
		$model->save();

		return $data['access_token'] = $token;
	}
}