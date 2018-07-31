<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_wx\modules\biz\controllers;

use api_wx\modules\biz\helpers\WxUserHelper;
use api_wx\modules\biz\helpers\WxWalletHelper;
use common\helpers\security\SecurityHelper;

/**
 * 钱包控制器
 * Class ErrandSendController
 * @package api_wx\modules\biz\controllers
 */
class WalletController extends ControllerAccess
{

	/**
	 * 首页
	 * @return array
	 */
	public function actionIndex()
	{
		$wallet_info = WxUserHelper::getWalletInfo($this->user_id);

		$this->_data = [
			'balance' => $wallet_info['balance'],
			'count'   => $wallet_info['count'],

		];

		return $this->response();
	}

	/**
	 * 收支明细列表
	 * @return array
	 */
	public function actionTransactionList()
	{
		$params                  = [];
		$params['user_id']       = $this->user_id;
		$params['last_separate'] = SecurityHelper::getBodyParam('last_separate');
		$params['page']          = SecurityHelper::getBodyParam('page');
		$params['pageSize']      = SecurityHelper::getBodyParam('pageSize');
		$params['type']          = SecurityHelper::getBodyParam('type');
		$data                    = WxWalletHelper::transactionList($params);
		if ($data) {
			$this->_data = $data;
		}

		return $this->response();

	}

	/**
	 * 收支明细
	 * @return array
	 */
	public function actionTransactionDetail()
	{
		if ($this->api_version == '1.0') {
			$data = WxWalletHelper::transactionDetail();
			if ($data) {
				$this->_data = $data;
			}
		}

		if ($this->api_version == '1.1') {
			$data = WxWalletHelper::transactionDetailV11();
			if ($data) {
				$this->_data = $data;
			}
		}

		return $this->response();
	}
}