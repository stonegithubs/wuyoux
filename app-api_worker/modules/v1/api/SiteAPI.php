<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/31
 */

namespace api_worker\modules\v1\api;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UtilsHelper;
use Yii;

class SiteAPI extends HelperBase
{

	public static function providerLoginV10()
	{
		//1、验证登录信息
		//2、更新登录access_token
		//3、获取shop表信息
		//4、拼接小帮信息

		$params['account']     = SecurityHelper::getBodyParam("account");        //用户账号
		$params['password']    = SecurityHelper::getBodyParam("password");        //密码
		$params['app_type']    = SecurityHelper::getBodyParam("app_type", 1);    //登录渠道(1:Android;2:IOS)
		$params['app_version'] = SecurityHelper::getBodyParam("app_version");    //版本号
		$params['client_id']   = SecurityHelper::getBodyParam("client_id");        //推送ID
		$params['push_type']   = SecurityHelper::getBodyParam("push_type", 1);    //推送类型1个推 2极光

		$result = false;
		$data   = UserHelper::verifyUserLoginInfo($params);

		$provider_id = 0;
		if ($data) {
			$params['user_id']       = $data['uid'];
			$params['role']          = 'provider';                //小帮
			$shopInfo                = ShopHelper::getShopInfoByUserId($data['uid']);
			$result['shops_status']  = 0;    //0默认，1正常，2等待审核，3审核失败，4封号
			$result['shops_message'] = '暂未申请小帮';//TODO 商家提交入驻后，因为前端还是取这个值，所以还是暂未申请小帮，其实是等待审核

			if ($shopInfo) {    //店铺信息 //TODO 封店铺的处理

				$provider['provider_id']        = $shopInfo['id'];        //小帮ID
				$provider['shops_mobile']       = $shopInfo['utel'];    //小帮联系号码
				$provider['shops_name']         = isset($shopInfo['shops_name']) ? $shopInfo['shops_name'] : "无忧小帮";    //小帮名称
				$provider['shops_photo']        = ImageHelper::getUserPhoto($shopInfo['shops_photo']);                    //小帮图片
				$provider['shops_province']     = $shopInfo['shops_province'];        //所在省
				$provider['shops_address']      = $shopInfo['shops_address'];        //所在地址
				$provider['shops_location']     = "[" . $shopInfo['shops_location_lng'] . "," . $shopInfo['shops_location_lat'] . "]";    //当前坐标
				$provider['shops_location_lng'] = $shopInfo['shops_location_lng'];        //当前坐标经度
				$provider['shops_location_lat'] = $shopInfo['shops_location_lat'];        //当前坐标纬度
				$provider['job_time']           = $shopInfo['job_time'];                //兼职全职
				$provider['type_id']            = $shopInfo['type_second'];                //分类
				$provider['type_label']         = CateListHelper::getCateName($shopInfo['type_second'], ',');
				$provider['bail_payed']         = intval(false);

				$status = $shopInfo['status'];    //0等待审核，1已审核，2审核失败，3待补充资料,4已删除

				//小帮的状态  0默认，1正常，2等待审核，3审核失败，4封号
				$status == 0 ? $result['shops_status'] = 2 : null;//等待审核
				$status == 1 ? $result['shops_status'] = 1 : null;//正常中
				$status == 2 ? $result['shops_status'] = 3 : null;//3审核失败
				$status == 3 ? $result['shops_status'] = 3 : null;//3审核失败
				$result['shops_message'] = ShopHelper::getApplyResultMessage($data['uid'], $shopInfo['status']);

				//封号的状态
				if ($shopInfo['guangbi'] != 1) {
					$result['shops_status']  = 4;
					$result['shops_message'] = "您已经被封号，暂不能接单，请联系客服";
				}

				if ($shopInfo['bail_money'] >= Ref::BAIL_MONEY) {
					$provider['bail_payed'] = intval(true);
				}
				$result['provider'] = $provider;    //小帮信息
				$provider_id        = $shopInfo['id'];
				$result['is_shops'] = 1;
			}

			$tokenData                 = UserHelper::setProviderToken($params, $provider_id);
			$result['user_id']         = $data['uid'];//用户ID
			$result['nickname']        = $data['nickname']; //昵称
			$result['sex']             = intval($data['sex']);            //性别 0男 1女
			$result['mobile']          = $data['mobile'];        //登录手机
			$result['birthday']        = $data['birthday'];        //生日
			$result['avatar']          = ImageHelper::getUserPhoto($data['userphoto']);     //头像
			$result['access_token']    = $tokenData['access_token'];           //access_token
			$result['expire']          = $tokenData['expire'];                     //超时时间
			$result['is_pay']          = !empty($data['paypassword']) ? 1 : 0;     //是否有支付密码(废弃字段)
			$result['is_pay_password'] = !empty($data['paypassword']) ? 1 : 0;     //是否有支付密码
			//是否已经入住商家
			if (YII_DEBUG) {    //调试信息，前端不需要理会
				$result['debug_data'] = Yii::$app->cache->get("USER_TOKEN" . $tokenData['access_token']);
			}
		}

		return $result;
	}

	/**
	 * 小帮基本配置
	 */
	public static function providerConfigV10()
	{
		return [
			'map_interval'     => 10,                    //地图上传时间
			'bail_money'       => Ref::BAIL_MONEY,    //缴纳最低保证金
			'is_invited'       => 1,
			'platform_phone'   => Ref::PLATFORM_PHONE,
			'service_time'     => Ref::SERVICE_TIME,
			'chat_link'        => 'http://51bangbang.udesk.cn/im_client/?web_plugin_id=43064',//联系我们URL  @2018-1-11
			'web_view_domain'  => Yii::$app->params['web_view_domain'],    //web view 域名 @2018-1-24
			'app_allow_domain' => UtilsHelper::appAllowDomain(),   //APP 允许访问的域名		@2018-3-6
			'is_open_package'  => 1,    //小帮配送到达后是否开启红包 @2018-4-24
			'startup_ad'       => 0,    //启动页广告 @2018-6-14
			'popup_ad'         => 0,    //弹窗广告 @2018-6-14
		];
	}

	/**
	 * 小帮登出
	 */
	public static function providerLogoutV10()
	{
		$access_token = Yii::$app->request->getBodyParam("access_token");
		$data         = UserHelper::getUserToken($access_token);

		//小帮下线
		$provider_id = $data ? $data['provider_id'] : 0;
		if ($provider_id) {

			$params['shop_login_status'] = 0;
			$params['update_time']       = time();
			ShopHelper::update($provider_id, $params);
			AMapHelper::poiOfflineLBS($provider_id);
		}

		UserHelper::deleteToken($access_token);

	}
}