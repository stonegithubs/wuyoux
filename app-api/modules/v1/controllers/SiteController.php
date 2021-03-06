<?php

namespace api\modules\v1\controllers;

use api\modules\v1\api\SiteAPI;
use api\modules\v1\helpers\StateCode;
use common\components\ControllerAPI;
use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UtilsHelper;
use common\traits\APISiteTrait;
use Yii;

/**
 * Default controller for the `v1` module
 */
class SiteController extends ControllerAPI
{

	/**
	 * 通用trait
	 *
	 * @see  \common\traits\APISiteTrait::actionFindPassword()
	 * @see  \common\traits\APISiteTrait::actionFindPasswordCode()
	 * @see  \common\traits\APISiteTrait::actionSingUp()
	 * @see  \common\traits\APISiteTrait::actionSignUpCode()
	 */
	use APISiteTrait;

	public function _init()
	{
		parent::_init(); // TODO: Change the autogenerated stub
	}


	//获取用户的token信息
	public function actionGetToken()
	{
		$mobile = Yii::$app->request->getBodyParam("mobile");
		$pwd    = Yii::$app->request->getBodyParam("pwd");
		if (YII_ENV_PROD) {
			if (!$pwd == 'zckj8863') {
				$this->_data = "hi";

				return $this->response();
			}
		}

		$sql
			  = "select * from bb_51_userdata 
					left join bb_51_user on bb_51_userdata.uid = bb_51_user.uid where bb_51_user.mobile='{$mobile}'";
		$data = Yii::$app->db->createCommand($sql)->queryOne();

		if (count($data) > 0) {
			$d = [
				"access_token" => $data['value'],
				"debug_data"   => $data
			];

			$this->_data = $d;
		} else {
			$this->_message = "无数据";
		}

		return $this->response();

	}

	//首页分类
	public function actionHomeCategory()
	{
		if ($this->api_version == '1.0') {

			$data = CateListHelper::getHomeCategory();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
				$this->_message = "暂无数据";
			}
		}

		if ($this->api_version == '1.1') {
			$data = SiteAPI::frontCateListV11();
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
				$this->_message = "暂无数据";
			}
		}

		return $this->response();
	}

	/**
	 * 用户登录
	 */
	public function actionUserLogin()
	{
		if ($this->api_version == '1.0') {
			$data = SiteAPI::userLoginV10(); //TODO 禁用禁止登录
			if ($data) {
				$this->_message = "登录成功";
				$this->_data    = $data;
			} else {
				$this->_code    = StateCode::USER_USER_LOGIN_FAILED;
				$this->_message = "手机或者密码不正确";
			}
		}

		return $this->response();
	}

	/**
	 * 废弃
	 * @return array
	 */
	public function actionClearRedis()
	{
		$data        = RegionHelper::clearCityPriceCache();
		$this->_data = $data;

		return $this->response();
	}

	/**
	 * 第三方-小帮快送-抢单
	 * 废弃
	 */
	public function actionErrandOrderRob()
	{
		//1.获取抢单对应参数
		$params['order_no']          = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']       = SecurityHelper::getBodyParam('provider_id');
		$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = SecurityHelper::getBodyParam('provider_mobile');
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance', 0);//小帮跟订单起点的距离
		Yii::$app->debug->log_info('errand_order_rob_params', $params);
		$data = SiteAPI::errandOrderRob($params);
		if ($data) {
			$this->_message = "抢单成功";
			$this->_data    = $data;
		} else {
			$this->_code    = StateCode::ERRAND_ROBBING_FAIL;
			$this->_message = "抢单失败";
		}

		return $this->response();
	}

	/**
	 * 用户基本配置
	 */
	public function actionUserConfig()
	{

		if ($this->api_version == '1.0') {
			$this->_data = SiteAPI::userConfigV10();
		}

		return $this->response();
	}


	/**
	 * 平台派单
	 * @return array
	 *
	 * 废弃
	 */
	public function actionDistributeOrder()
	{
		//1.获取抢单对应参数
		$params['tmp_no']            = SecurityHelper::getBodyParam('tmp_no');
		$params['provider_id']       = SecurityHelper::getBodyParam('provider_id');
		$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = SecurityHelper::getBodyParam('provider_mobile');
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance', 0);//小帮跟订单起点的距离
		Yii::$app->debug->log_info('biz_order_rob_params', $params);
		$data = SiteAPI::BizOrderDistribute($params);
		if ($data) {
			$this->_message = "抢单成功";
			$this->_data    = $data;
		} else {
			$this->_code    = StateCode::ERRAND_ROBBING_FAIL;
			$this->_message = "抢单失败";
		}

		return $this->response();
	}
}
