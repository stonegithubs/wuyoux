<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\api\WalletAPI;
use common\components\Ref;
use common\helpers\payment\WalletHelper;
use Yii;

class WalletController extends ControllerAccess
{

	/**
	 * 用户充值首页 [废弃方法] 2018-4-11
	 * @return array
	 */
	public function actionRechargeIndex()
	{
		$data        = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_USER);
		$this->_data = $data;

		return $this->response();
	}

	/**
	 * 公用录入余额充值
	 * @return array
	 */
	public function actionBalanceRecharge()
	{
		if ($this->api_version == '1.0') {
			$res = WalletAPI::balanceRechargeV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();
	}

	/**
	 * 1.1 企业送充值首页
	 * @return array
	 */
	public function actionBizRechargeIndex()
	{
		if ($this->api_version == '1.0') {
			$data        = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_BIZ);
			$this->_data = $data;
		}

		if ($this->api_version == '1.1') {
			$data = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_USER);

			$data['list'][] = [        //在数据后默认显示 其他金额
									   "recharge_id" => 0,
									   "amount"      => 0,
									   "discount"    => 'discount',
									   "content"     => "其他金额"
			];
			$this->_data    = $data;
		}


		return $this->response();
	}

	/**
	 * 1.2 企业送带数据充值
	 * @return array
	 */
	public function actionBizCouponRecharge()
	{
		if ($this->api_version == '1.0') {
			$res = WalletAPI::rechargePaymentV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();
	}

	/**
	 * 2.1 用户充值首页
	 * @return array
	 */
	public function actionUserRechargeIndex()
	{
		if ($this->api_version == '1.0') {
			$data        = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_USER);
			$this->_data = $data;
		}

		if ($this->api_version == '1.1') {
			$data = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_USER);

			$data['list'][] = [        //在数据后默认显示 其他金额
									   "recharge_id" => 0,
									   "amount"      => 0,
									   "discount"    => 'discount',
									   "content"     => "其他金额"
			];
			$this->_data    = $data;
		}

		return $this->response();
	}

	/**
	 * 2.2 用户带数据充值
	 * @return array
	 */
	public function actionUserCouponRecharge()
	{
		if ($this->api_version == '1.0') {
			$res = WalletAPI::rechargePaymentV10($this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();
	}

	//账户余额
	public function actionGetBalance()
	{
		if ($this->api_version == '1.0') {
			$data        = WalletHelper::getBalance($this->user_id);
			$this->_data = $data;
		}

		return $this->response();
	}

}

