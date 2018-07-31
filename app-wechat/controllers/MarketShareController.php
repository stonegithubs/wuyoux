<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/13
 */

namespace wechat\controllers;

use common\helpers\payment\WxpayHelper;
use common\helpers\utils\UrlHelper;
use Yii;

class MarketShareController extends ControllerAuthorize
{
	/**
	 * 微信分享
	 * @return string
	 */
	public function actionWechatShare()
	{
		$market_code = Yii::$app->request->get('market_code');
		$entrance    = Yii::$app->request->get('entrance');

		if (!$market_code) {
			return $this->actionError();
		}

		$apiList = [
			'checkJsApi',
			'onMenuShareTimeline',
			'onMenuShareAppMessage',
			'onMenuShareQQ',
			'onMenuShareWeibo',
		];

		$jssdk            = WxpayHelper::getJsConfig($apiList);
		$wechat_share_url = UrlHelper::wxLink('market-share/register') . '?market_code=' . $market_code . '&introduce=1&entrance=' . $entrance;

		$result['data'] = [
			'jssdk'            => $jssdk,
			'wechat_share_url' => $wechat_share_url,
			'entrance'         => $entrance,
			'share_pic'        => Yii::$app->params['wx_domain'] . '/static/market/img/market_share.png'
		];

		return $this->renderPartial('wechat-share', $result);
	}

	/**
	 * 微信开放分享
	 * @return string
	 */
	public function actionWechatOpenShare()
	{
		$entrance = Yii::$app->request->get('entrance');

		$apiList = [
			'checkJsApi',
			'onMenuShareTimeline',
			'onMenuShareAppMessage',
			'onMenuShareQQ',
			'onMenuShareWeibo',
		];

		$jssdk            = WxpayHelper::getJsConfig($apiList);
		$wechat_share_url = UrlHelper::wxLink('market-share/open-introduce');

		$result['data'] = [
			'jssdk'            => $jssdk,
			'wechat_share_url' => $wechat_share_url,
			'entrance'         => $entrance,
			'share_pic'        => Yii::$app->params['wx_domain'] . '/static/market/img/market_share.png'
		];

		return $this->renderPartial('open/wechat-share', $result);
	}

	/**
	 * 404页面
	 * @return string
	 */
	public function actionError()
	{
		return $this->renderPartial('error');
	}

	//由于微信分享必须要跳转到同一个链接，所以这里的处理再重复跳转多一次
	public function actionRegister()
	{
		$market_code = Yii::$app->request->get('market_code');
		$entrance    = Yii::$app->request->get('entrance');

		if (!$market_code) {
			return $this->actionError();
		}

		$gotoUrl = UrlHelper::webLink('market-share/register') . '?market_code=' . $market_code . '&introduce=1&entrance=' . $entrance;
		header('location:' . $gotoUrl);
		exit;
	}

	//由于微信分享必须要跳转到同一个链接，所以这里的处理再重复跳转多一次
	public function actionOpenIntroduce()
	{
		$gotoUrl = UrlHelper::webLink('market-share/open-introduce');
		header('location:' . $gotoUrl);
		exit;
	}
}