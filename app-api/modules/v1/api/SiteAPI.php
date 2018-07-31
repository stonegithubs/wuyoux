<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/31
 */

namespace api\modules\v1\api;

use api\modules\v1\controllers\ErrandController;
use api\modules\v1\controllers\PushController;
use api\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use yii\db\Query;

class SiteAPI extends HelperBase
{
	/**
	 * 用户登录 version 1.0
	 */
	public static function userLoginV10()
	{
		//1、验证登录信息
		//2、更新登录access_token
		//3、拼接用户信息

		$params['account']     = SecurityHelper::getBodyParam("account");        //用户账号
		$params['password']    = SecurityHelper::getBodyParam("password");        //密码
		$params['app_type']    = SecurityHelper::getBodyParam("app_type", 1);    //登录渠道(1:Android;2:IOS)
		$params['app_version'] = SecurityHelper::getBodyParam("app_version");    //版本号
		$params['client_id']   = SecurityHelper::getBodyParam("client_id");        //推送ID
		$params['push_type']   = SecurityHelper::getBodyParam("push_type", 1);    //推送类型1个推 2极光

		$result = false;
		$data   = UserHelper::verifyUserLoginInfo($params);
		if ($data) {
			//TODO 封用户的处理
			$params['user_id'] = $data['uid'];
			$params['role']    = 'user';//用户
			$tokenData         = UserHelper::setUserToken($params);

			$result['user_id']         = $data['uid'];//用户ID
			$result['nickname']        = $data['nickname'];           //昵称
			$result['sex']             = intval($data['sex']);            //性别 0男 1女
			$result['mobile']          = $data['mobile'];        //登录手机
			$result['birthday']        = $data['birthday'];        //生日
			$result['avatar']          = ImageHelper::getUserPhoto($data['userphoto']);     //头像
			$result['access_token']    = $tokenData['access_token'];           //access_token
			$result['expire']          = $tokenData['expire'];                     //超时时间
			$result['score']           = $data['score'];                         //积分
			$result['is_pay']          = !empty($data['paypassword']) ? 1 : 0;     //是否有支付密码(废弃字段)
			$result['is_pay_password'] = !empty($data['paypassword']) ? 1 : 0;     //是否有支付密码

			if (YII_DEBUG) {        //调试信息，前端不需要理会
				$result['debug_data'] = \Yii::$app->cache->get("USER_TOKEN" . $tokenData['access_token']);
			}
		}

		return $result;
	}

	/**
	 * 首页分类 version 1.1
	 */
	public static function frontCateListV11()
	{
		$data['app_type']    = SecurityHelper::getBodyParam("app_type", 1);
		$data['app_version'] = SecurityHelper::getBodyParam("app_version", '3.3.8');

		return CateListHelper::getHomeCate($data);
	}


	/**
	 * 小帮快送-抢单
	 */
	public static function errandOrderRob($params)
	{
		$result = false;

		if (empty($params['order_no']) || empty($params['provider_id'])) return $result;

		//2.修改订单记录
		$data = ErrandHelper::saveRobbing($params);
		if ($data) {
			$result = $data;

			if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY) {

				ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

			} else if ($data['errand_type'] == Ref::ERRAND_TYPE_DO) {

				ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

			} elseif ($data['errand_type'] == Ref::ERRAND_TYPE_SEND) {
				ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandsendHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);
			}
		}

		return $result;
	}

	/**
	 * 用户配置
	 */
	public static function userConfigV10()
	{
		return [
			'is_invited'     => 0,                    //是否开启邀请
			'platform_phone' => Ref::PLATFORM_PHONE,//平台服务电话
			'service_time'   => Ref::SERVICE_TIME,    //服务时间
			'city_list'      => RegionHelper::getCityList(),
		];
	}

	/**
	 * 平台派单
	 * @param $params
	 * @return array|bool
	 */
	public static function BizOrderDistribute($params)
	{
		$result = false;
		if (empty($params['tmp_no']) || empty($params['provider_id'])) return $result;
		$data = BizSendHelper::saveTmpRobbing($params);
		if ($data) {
			$result = $data;
			BizSendHelper::pushTmpToUserNotice($data['tmp_no']);
		}

		return $result;
	}
}