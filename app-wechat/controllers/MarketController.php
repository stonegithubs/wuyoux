<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/30
 */

namespace wechat\controllers;

use common\helpers\payment\WxpayHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\UrlHelper;
use m\helpers\MarketHelper;
use yii\helpers\Url;
use Yii;

class MarketController extends ControllerAuthorize
{
	/**
	 * 全民合伙人 微信端入口
	 * @return string
	 */
	public function actionIndex()
	{
		if (!Yii::$app->mp_wechat->isAuthorized()) {
			exit;
		}

		if ($this->_userId) {
			$params['user_id'] = $this->_userId;
			$token             = UserHelper::setWxToken($params);
			//用户已登录，跳转M站微信入口
			$gotoUrl = UrlHelper::webLink(['market/wx-index', 'session_key' => $token]);
		} else {
			//用户未登录，跳转介绍页
			Url::remember(UrlHelper::wxLink('market/index'));
			$gotoUrl = UrlHelper::wxLink(['market/introduce', 'type' => 'market']);
		}
		header('location:' . $gotoUrl);
		exit;
	}

	/**
	 * 介绍页
	 * @return string
	 */
	public function actionIntroduce()
	{
		return $this->renderPartial('introduce');
	}

	/**
	 * 马上邀请
	 * @return string
	 */
	public function actionInvite()
	{
		//用户是否登录
		if (!$this->_userId) {
			$gotoUrl = Url::previous();
			header('location:' . $gotoUrl);
			exit();
		}

		$userMarket    = MarketHelper::getUserMarket($this->_userId); //获取营销号
		$entrance_type = 1; //用户注册入口类型

		//微信分享sdk
		$apiList = [
			'checkJsApi',
			'onMenuShareTimeline',
			'onMenuShareAppMessage',
			'onMenuShareQQ',
			'onMenuShareWeibo',
		];
		$jssdk   = WxpayHelper::getJsConfig($apiList);

		$result['data'] = [
			'jssdk'       => $jssdk,
			'link'        => UrlHelper::wxLink("market-share/register") . '?market_code=' . $userMarket['market_code'] . '&entrance=' . $entrance_type . '&introduce=1',//分享链接,
			'qrcode_link' => UrlHelper::webLink("market-share/register") . '?market_code=' . $userMarket['market_code'] . '&entrance=' . $entrance_type,//扫码分享链接,
			'imgUrl'      => UrlHelper::webLink('/static/market/img/market_share.png')
		];

		return $this->renderPartial('invite', $result);
	}

}