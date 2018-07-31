<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */
namespace api_worker\modules\v1\controllers;



use api_worker\modules\v1\api\DocAPI;
use api_worker\modules\v1\api\ShopAPI;
use api_worker\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\DocumentHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use Yii;

class ApplyController extends ControllerAccess
{
	//入驻首页			v1/apply/index
	//入驻说明文档   		v1/apply/doc
	//摩的入驻   		v1/apply/motor
	//电动车入驻   		v1/apply/electrombile
	//快送入驻   		v1/apply/errand


	/**
	 * 入驻首页
	 */
	public function actionIndex()
	{
		//获取可入驻分类
		if ($this->api_version == '1.0') {
			$data = ShopAPI::applyIndex();
			if($data){
				$this->_data = $data;
				$this->_message ="获取数据成功";
			}else{
				$this->setCodeMessage(StateCode::COMMON_GET_ERROR);
			}
		}
		return $this->response();
	}

	/**
	 * 入驻说明
	 * @废弃
	 */
	public function actionDoc()
	{
		//采用通用接口 ducument/get  ?type=3
	}

	/**
	 * 摩的入驻
	 */
	public function actionMotor()
	{
		if ($this->api_version == '1.0') {

			//1.判断是否已经有商家,商家状态是否正确
			//2.处理参数
			//3.商家入驻
			//4.存储图片
			$userInfo = $this->getUserInfo();
			$shop     = ShopHelper::shopDetailByMobile($userInfo['mobile']);
			if ($shop) {
				if ($shop['status'] == 1) {
					//商家已入驻

					$this->setCodeMessage(StateCode::SHOP_HAVE_ENTER);
					return $this->response();
				}
				if ($shop['status'] == 0) {
					$this->setCodeMessage(StateCode::SHOP_ENTERING);

					return $this->response();
				}
			}
			$status = ShopAPI::enterMotorShopV10($userInfo);
			if ($status) {

				QueueHelper::newApplyNotice($userInfo['uid']);
				ActivityHelper::addProviderMarketFromApply($this->user_id);
				$this->_message = '您的申请资料已成功提交到无忧帮帮,我司将会在24小时内审核您提交的资料,请耐心等待!';

			} else {
				$this->setCodeMessage(StateCode::SHOP_ENTER_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 快送入驻
	 */
	public function actionErrand()
	{
		if ($this->api_version == '1.0') {

			//1.判断是否已经有商家,商家状态是否正确
			//2.处理参数
			//3.商家入驻
			//4.存储图片
			$userInfo = $this->getUserInfo();
			$shop     = ShopHelper::shopDetailByMobile($userInfo['mobile']);
			if ($shop) {
				if ($shop['status'] == 1) {
					//商家已入驻

					$this->setCodeMessage(StateCode::SHOP_HAVE_ENTER);
					return $this->response();
				}
				if ($shop['status'] == 0) {
					$this->setCodeMessage(StateCode::SHOP_ENTERING);

					return $this->response();
				}
			}
			$status = ShopAPI::enterErrandShop($userInfo);

			if ($status) {

				QueueHelper::newApplyNotice($userInfo['uid']);
				ActivityHelper::addProviderMarketFromApply($this->user_id);
				$this->_message = '您的申请资料已成功提交到无忧帮帮,我司将会在24小时内审核您提交的资料,请耐心等待!';
			} else {
				$this->setCodeMessage(StateCode::SHOP_ENTER_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 电动车入驻
	 */
	public function actionElectrombile()
	{
		if ($this->api_version == '1.0') {

			//1.判断是否已经有商家,商家状态是否正确
			//2.处理参数
			//3.商家入驻
			//4.存储图片
			$userInfo = $this-> getUserInfo();
			$shop     = ShopHelper::shopDetailByMobile($userInfo['mobile']);
			if ($shop) {
				if ($shop['status'] == 1) {
					//商家已入驻

					$this->setCodeMessage(StateCode::SHOP_HAVE_ENTER);
					return $this->response();
				}
				if ($shop['status'] == 0) {
					$this->setCodeMessage(StateCode::SHOP_ENTERING);

					return $this->response();
				}
			}
			$status = ShopAPI::enterElectroShopV10($userInfo);
			if ($status) {

 				QueueHelper::newApplyNotice($userInfo['uid']);
				ActivityHelper::addProviderMarketFromApply($this->user_id);
				$this->_message = '您的申请资料已成功提交到无忧帮帮,我司将会在24小时内审核您提交的资料,请耐心等待!';
			} else {
				$this->setCodeMessage(StateCode::SHOP_ENTER_FAILED);
			}
		}

		return $this->response();
	}



}

