<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/30
 */

namespace m\controllers;

use common\components\Ref;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\UrlHelper;
use common\models\orders\Order;
use m\helpers\MarketHelper;
use Yii;
use m\helpers\StateCode;

class MarketController extends ControllerAccess
{
	/**
	 * 全民合伙人 用户端入口
	 * @return string
	 */
	public function actionUserIndex()
	{
		Yii::$app->session->set('entrance', 'user');
		$user_id = $this->user_id;
		MarketHelper::createUserMarket($user_id);
		$result['data'] = MarketHelper::indexData($user_id);

		return $this->renderPartial('index', $result);
	}

	/**
	 * 全民合伙人 小帮端入口
	 * @return string
	 */
	public function actionProviderIndex()
	{
		Yii::$app->session->set('entrance', 'provider');
		$user_id = $this->user_id;
		MarketHelper::createUserMarket($user_id);
		$result['data'] = MarketHelper::indexData($user_id);

		return $this->renderPartial('index', $result);
	}

	/**
	 * 全民合伙人 微信端入口
	 * @return string
	 */
	public function actionWxIndex()
	{
		Yii::$app->session->set('entrance', 'wechat');
		$user_id = $this->user_id;
		MarketHelper::createUserMarket($user_id);
		$result['data'] = MarketHelper::indexData($user_id);

		return $this->renderPartial('index', $result);
	}

	/**
	 * 马上邀请
	 * @return string
	 */
	public function actionInvite()
	{
		$user_id = $this->user_id;
		//入口类型
		$entrance = Yii::$app->session->get('entrance');

		if ($entrance == 'wechat') {
			$gotoUrl = UrlHelper::wxLink(['market/invite']);
			header('location:' . $gotoUrl);
			exit;
		}

		if ($entrance == 'provider') {
			$entrance_type = 2;//小帮
		} else {
			$entrance_type = 1;//用户
		}

		$userMarket                    = MarketHelper::getUserMarket($user_id);
		$result['data']['link']        = UrlHelper::webLink("/market-share/register") . '?market_code=' . $userMarket['market_code'] . '&entrance=' . $entrance_type . '&introduce=1';//分享链接
		$result['data']['qrcode_link'] = UrlHelper::webLink("/market-share/register") . '?market_code=' . $userMarket['market_code'] . '&entrance=' . $entrance_type;//扫码分享链接
		$result['data']['title']       = 'Hi，朋友，无忧帮帮招募合伙人啦！';//分享标题
		$result['data']['desc']        = '零投入，零风险，月收入过万。接的多，邀的多，赚的多，分享赚更多';//分享描述
		$result['data']['imgUrl']      = UrlHelper::webLink('/static/market/img/market_share.png');//分享图片

		return $this->renderPartial('invite', $result);
	}


	/**
	 * 提现成功
	 * @return string
	 */
	public function actionWithdrawSuccess()
	{
		return $this->renderPartial('withdraw-success');
	}

	/**
	 * 提现历史
	 * @return string
	 */
	public function actionWithdrawHistory()
	{
		$user_id        = $this->user_id;
		$marketWithdraw = MarketHelper::getMarketWithdraw($user_id);
		$result['data'] = $marketWithdraw ? $marketWithdraw : null;

		return $this->renderPartial('withdraw-history', $result);
	}

	/**
	 * 提现
	 * @return string
	 */
	public function actionWithdraw()
	{
		$user_id          = $this->user_id;
		$entrance         = Yii::$app->session->get('entrance');
		$transfer_account = MarketHelper::transferAccount($user_id);
		$result['data']   = [
			'current_profit'   => MarketHelper::currentProfit($user_id),
			'transfer_account' => $transfer_account['transfer_account'] ? $transfer_account['transfer_account'] : null,
			'transfer_type'    => $transfer_account['transfer_type'] ? $transfer_account['transfer_type'] : null,
			'entrance'         => $entrance  //入口来源 user用户 provider小帮
		];

		return $this->renderPartial('withdraw', $result);
	}

	/**
	 * 绑定提现账号
	 * @return string
	 */
	public function actionBindTransfer()
	{
		$user_id        = $this->user_id;
		$boundAccount   = MarketHelper::boundAccount($user_id);
		$result['data'] = $boundAccount ? $boundAccount : null;

		return $this->renderPartial('bind-transfer', $result);
	}

	/**
	 * 正在绑定提现账号
	 * @return string
	 */
	public function actionBindingTransfer()
	{
		$user_id        = $this->user_id;
		$boundAccount   = MarketHelper::boundAccount($user_id);
		$result['data'] = $boundAccount ? $boundAccount : null;

		return $this->renderPartial('binding-transfer', $result);
	}

	/**
	 * 我的收益
	 * @return string
	 */
	public function actionProfit()
	{
		$type           = Yii::$app->request->get('type');
		$result['data'] = [
			'type' => $type
		];

		return $this->renderPartial('profit', $result);
	}

	/**
	 * 常见问题
	 * @return string
	 */
	public function actionCommonProblem()
	{
		return $this->renderPartial('common-problem');
	}

	/**
	 * 红包页面
	 * @return string
	 */
	public function actionGift()
	{
		$giftProfit     = MarketHelper::giftProfit($this->user_id);
		$result['data'] = $giftProfit;

		return $this->renderPartial('gift', $result);
	}

	/**
	 * 红包记录页
	 * @return string
	 */
	public function actionGiftRecord()
	{
		$giftProfitReceived = MarketHelper::giftProfitReceived($this->user_id);
		$result['data']     = $giftProfitReceived;

		return $this->renderPartial('gift-record', $result);
	}

	/* -----------------------------营销API------------------------------ */

	/**
	 * 首页顶部随机数据
	 * @return array
	 */
	public function actionAjaxTop()
	{
		$result['data']['top'] = MarketHelper::indexTop();
		$this->_data           = $result;

		return $this->response();
	}

	/**
	 * 我的收益API
	 * @return array
	 */
	public function actionAjaxProfit()
	{
		$user_id = $this->user_id;
		$params  = [
			'page'      => Yii::$app->request->get('page'),
			'page_size' => Yii::$app->request->get('page_size')
		];

		$today_sum_profit = MarketHelper::SumProfit($user_id, Ref::TODAY_PROFIT);
		$total_sum_profit = MarketHelper::SumProfit($user_id, Ref::TOTAL_PROFIT);
		$today_profit     = MarketHelper::Profit($user_id, Ref::TODAY_PROFIT, $params);
		$total_profit     = MarketHelper::Profit($user_id, Ref::TOTAL_PROFIT, $params);

		$result['data']['today_sum_profit'] = $today_sum_profit ? $today_sum_profit : 0;
		$result['data']['total_sum_profit'] = $total_sum_profit ? $total_sum_profit : 0;
		$result['data']['today_profit']     = $today_profit ? $today_profit : null;
		$result['data']['total_profit']     = $total_profit ? $total_profit : null;
		$this->_data                        = $result;

		return $this->response();
	}

	/**
	 * 绑定提现账号API
	 * @return array
	 */
	public function actionAjaxBindTransfer()
	{
		$params         = [
			'transfer_realname'    => Yii::$app->request->get('transfer_realname'),          //提现真实姓名
			'transfer_accountname' => Yii::$app->request->get('transfer_accountname'),       //提现账号名称
			'transfer_account'     => Yii::$app->request->get('transfer_account'),           //提现账号
			'transfer_type'        => Yii::$app->request->get('transfer_type'),              //提现类型(2支付宝 3银行)

		];
		$user_id        = $this->user_id;
		$updateTransfer = MarketHelper::updateTransfer($params, $user_id);
		if (!$updateTransfer) {
			$this->setCodeMessage(StateCode::BIND_TRANSFER_FAIL);
		} else {
			$boundAccount   = MarketHelper::boundAccount($user_id);
			$result['data'] = $boundAccount ? $boundAccount : null;
			$this->_data    = $result;
		}

		return $this->response();
	}

	/**
	 * 清除提现账号API
	 * @return array
	 */
	public function actionAjaxClearTransfer()
	{
		$user_id = $this->user_id;
		$result  = MarketHelper::clearTransfer($user_id);
		if (!$result) {
			$this->setCodeMessage(StateCode::CLEAR_TRANSFER_FAIL);
		}

		return $this->response();
	}

	/**
	 * 提现API
	 * @return array
	 */
	public function actionAjaxWithdraw()
	{
		$transfer_amount = Yii::$app->request->get('transfer_amount');
		$user_id         = $this->user_id;
		$withdraw        = MarketHelper::withdraw($transfer_amount, $user_id);
		if (!$withdraw) {
			$this->setCodeMessage(StateCode::WITHDRAW_FAIL);
		} else {
			$result['data']['current_profit'] = $withdraw['earn_amount'];
			$this->_data                      = $result;
		}

		return $this->response();
	}

	/**
	 * 转到余额API
	 * @return array
	 */
	public function actionAjaxTransfer()
	{
		$transfer_amount = Yii::$app->request->get('transfer_amount');
		$user_id         = $this->user_id;
		$transfer        = MarketHelper::transfer($transfer_amount, $user_id);
		if (!$transfer) {
			$this->setCodeMessage(StateCode::TRANSFER_FAIL);
		} else {
			$result['data']['current_profit'] = $transfer['earn_amount'];
			$this->_data                      = $result;
		}

		return $this->response();
	}

	/**
	 * 查看红包API
	 * @return array
	 */
	public function actionAjaxCheckGift()
	{
		$id            = SecurityHelper::getBodyParam('gift_id');
		$checkActivity = MarketHelper::checkActivityForProfitGift($id, $this->user_id);
		if ($checkActivity) {
			$result['data']['overdue'] = 0;
		} else {
			$result['data']['overdue'] = 1;
		}
		$this->_data = $result;

		return $this->response();
	}

	/**
	 * 拆红包API
	 * @return array
	 */
	public function actionAjaxOpenGift()
	{
		$id = SecurityHelper::getBodyParam('gift_id');

		$receiveGift = MarketHelper::updateActivityForProfitGift($id, $this->user_id);
		if (!$receiveGift) {
			$this->setCodeMessage(StateCode::GIFT_RECEIVED);

			return $this->response();
		}

		$result['data']['gift_amount'] = $receiveGift;
		$result['data']['gift_id']     = $id;
		$result['data']['received']    = Ref::GIFT_RECEIVED;
		$this->_data                   = $result;

		return $this->response();
	}
}