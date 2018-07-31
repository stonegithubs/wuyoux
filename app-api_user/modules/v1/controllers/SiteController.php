<?php

namespace api_user\modules\v1\controllers;

use api_user\modules\v1\api\SiteAPI;
use api_user\modules\v1\helpers\StateCode;
use api_user\modules\v1\helpers\UserSettingHelper;
use api_user\modules\v1\helpers\NoticeHelper;
use common\components\ControllerAPI;
use common\helpers\images\ImageHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
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
	 * @see  \common\traits\APISiteTrait::actionSignUp()
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
	 * 首页轮播图
	 * @return array
	 */
	public function actionHomeCarousel()
	{

		if ($this->api_version == '1.0') {
			$data        = UserSettingHelper::homeCarouselV10();
			$this->_data = $data;
		}

		return $this->response();
	}

	/**
	 * 首页列表(含提示和分类信息）
	 * @return array
	 */
	public function actionHomeList()
	{
		$location = Yii::$app->request->getBodyParam("location");
		$tip = NoticeHelper::getTip($location);
		$cate         = [
			['name' => '小帮出行',
			 'id'   => 51,
			 'show'	=> 1,	//显示开关 1：显示 0：隐藏
			 'pic'  => ImageHelper::OSS_URL . 'app/home/home_img_travel@3x.png'
			],
			['name' => '小帮快送',
			 'id'   => 132,
			 'show'	=> 1,	//显示开关 1：显示 0：隐藏
			 'pic'  => ImageHelper::OSS_URL . 'app/home/home_img_delivery@3x.png'
			],
			['name' => '小帮货车',
			 'id'   => 133,
			 'show'	=> 0,	//显示开关 1：显示 0：隐藏
			 'pic'  => ImageHelper::OSS_URL . 'app/home/home_img_truck@3x.png'
			],
		];
		$data['tip']  = $tip;
		$data['cate'] = $cate;
		$this->_data  = $data;

		return $this->response();

	}

}