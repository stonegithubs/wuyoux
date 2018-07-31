<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_worker\modules\v1\controllers;


use api_worker\modules\v1\api\ShopAPI;
use api_worker\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\images\ImageHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\DocumentHelper;
use common\helpers\utils\UtilsHelper;
use Yii;

class ShopController extends ControllerAccess
{

	/**
	 * 提交反馈意见
	 * @return array
	 */
	public function actionFeedBack()
	{
		$data = UserHelper::feedBack($this->user_id);
		if ($data) {

		} else {
			$this->setCodeMessage(StateCode::OTHER_FEEDBACK_SAVE);
		}

		return $this->response();
	}

	/**
	 * 小帮首页
	 */
	public function actionHome()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::homeDataV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}
		if ($this->api_version == '1.1') {
			$data = ShopAPI::homeDataV11($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	/**
	 * 接单类型-查看
	 */
	public function actionTypeInfo()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::getShopType($this->provider_id);

			if ($data) {
				$this->_message = "获取数据成功";
				$this->_data    = $data;
			} else {
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
				$this->_message = StateCode::get(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	/**
	 * 接单类型-修改
	 */
	public function actionTypeUpdate()
	{
		if ($this->api_version == '1.0') {
			$result = ShopAPI::updateShopType($this->provider_id);
			if ($result) {
				AMapHelper::poiOnlineLBS($this->provider_id);
				$this->_message = "修改成功";
			} else {
				$this->_message = "修改失败";
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
			}
		}

		return $this->response();
	}

	/**
	 * 商家收入首页
	 * @return array
	 */
	public function actionIncomeIndex()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::incomeDataV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}

		}

		return $this->response();
	}

	/**
	 * 小帮上线
	 */
	public function actionOnline()
	{
		if (!ShopAPI::judgeOnlineV10($this->provider_id)) {
			$this->_code    = StateCode::SHOP_ENTER_FAILED;
			$this->_message = "商家未入驻成功或者已被封号";

			return $this->response();
		}

		if ($this->api_version == '1.0') {


			$data = ShopAPI::shopOnlineMapV10($this->provider_id, 'online');
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		if ($this->api_version == '1.1') {    //使用高德坐标

			$data = ShopAPI::shopOnlineMapV11($this->provider_id, 'online');
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		return $this->response();
	}

	/**
	 * 小帮下线
	 */
	public function actionOffline()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::shopOnlineMapV10($this->provider_id, 'offline');
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		if ($this->api_version == '1.1') {
			$data = ShopAPI::shopOnlineMapV11($this->provider_id, 'offline');
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		return $this->response();
	}

	/**
	 * 更新位置 APP端45秒更新一次
	 * @return array
	 */
	public function actionUpdateLocation()
	{

		if ($this->api_version == '1.0') {
			$data = ShopAPI::shopOnlineMapV10($this->provider_id, 'online');
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		if ($this->api_version == '1.1') {
			$data = ShopAPI::shopOnlineMapV11($this->provider_id, 'online');
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "暂无数据";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}


		return $this->response();
	}

	/**
	 * 新增提现账户
	 * @return array
	 */
	public function actionDrawingAway()
	{
		if ($this->api_version == '1.0') {
			if (ShopAPI::checkAccountExist($this->provider_id)) {
				$this->setCodeMessage(StateCode::SHOP_ACCOUNT_EXIST);

				return $this->response();
			}

			$data = ShopAPI::drawingWayV10($this->provider_id);
			if ($data) {

			} else {
				$this->_message = "新增提现账号失败";
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
			}

			return $this->response();
		}
	}

	/**
	 * 删除提现账户
	 * @return array
	 */
	public function actionDrawingDelete()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::drawingDeleteV10($this->provider_id);
			if ($data) {

			} else {
				$this->_message = "删除失败";
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
			}

			return $this->response();
		}
	}

	/**
	 * 提现账户列表
	 * @return array
	 */
	public function actionDrawingList()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::drawingListV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "请绑定账号";
				$this->_code    = StateCode::OTHER_EMPTY_DATA;
			}
		}

		return $this->response();
	}

	/**
	 * 绑定提现账户
	 * @return array
	 */
	public function actionDrawingBind()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::drawingBindV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->_message = "绑定失败";
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
			}
		}

		return $this->response();
	}

	/**
	 * 申请提现
	 * @return array
	 */
	public function actionDrawingSave()
	{
		if ($this->api_version == '1.0') {
			$money = SecurityHelper::getBodyParam('money');
			if ($money < Ref::WITHDRAW_LEAST_MONEY) {
				$this->setCodeMessage(StateCode::WITHDRAW_LEAST_MONEY);

				return $this->response();
			}

			if (!ShopHelper::checkDrawMoney($money, $this->provider_id)) {
				$this->setCodeMessage(StateCode::WITHDRAW_OVER_MONEY);

				return $this->response();
			}


			$data = ShopAPI::drawingSaveV10($this->provider_id);
			if ($data) {

			} else {
				$this->_message = "提现失败";
				$this->_code    = StateCode::COMMON_OPERA_ERROR;
			}
		}


		return $this->response();
	}

	/**
	 * 保证金提现
	 * @return array
	 */
	public function actionBailThaw()
	{
		if ($this->api_version == '1.0') {
			$params = [
				'money'       => SecurityHelper::getBodyParam('money'),
				'provider_id' => $this->provider_id,
				'user_id'     => $this->user_id,
				'account_id'  => SecurityHelper::getBodyParam('account_id'),
			];

			if (!SecurityHelper::verifyPayPwdBuyUserId($this->user_id, SecurityHelper::getBodyParam('password'))) {//检验密码
				$this->setCodeMessage(StateCode::PWD_PAY_INCORRECT);
				return $this->response();
			}

			if (OrderHelper::isDoingOrder($this->provider_id)) {//是否有未完成的订单
				$this->setCodeMessage(StateCode::WITHDRAW_BAIL_DOING_ORDER);

				return $this->response();
			}

			if (!ShopHelper::checkBailQualification($this->provider_id, $params['money'])) {//缴纳保证金时间是否大于15天,是否有解冻记录
				$this->setCodeMessage(StateCode::WITHDRAW_BAIL_CANT);

				return $this->response();
			}

			$data = ShopHelper::thawBail($params);
			if ($data) {
				$this->_message = "保证金解冻成功,请耐心等待财务处理！";
			} else {
				$this->setCodeMessage(StateCode::WITHDRAW_BAIL_FAILED);
			}
		}

		return $this->response();
	}

	/**
	 * 缴纳保证金
	 * @return array
	 */
	public function actionBailPay()
	{
		if ($this->api_version == '1.0') {
			$res = ShopAPI::bailPayV10($this->provider_id, $this->user_id);
			$this->setCodeMessage($res['code']);
			$this->_data = $res['data'];
		}

		return $this->response();
	}

	/**
	 * 保证金解冻首页
	 * @return array
	 */
	public function actionBailRefund()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::bailRefundV10($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
			}
		}

		return $this->response();
	}


	/**
	 * 保证金首页（保证金缴纳首页）
	 * @return array
	 */
	public function actionBailIndex()
	{
		if ($this->api_version == '1.0') {

			$data        = DocumentHelper::getBailDocument($this->provider_id);
			$this->_data = $data;

		}

		//@since android 1.0.5 @ios 1.1.7
		if ($this->api_version == '1.1') {
			$data = ShopAPI::bailIndexV11($this->provider_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
			}
		}

		return $this->response();
	}

	/**
	 * 获取商家信息
	 * @return array
	 */
	public function actionGetInfo()
	{
		if ($this->api_version == '1.0') {
			$data = ShopAPI::getInfoV10($this->provider_id, $this->user_id);
			if ($data) {
				$this->_data = $data;
			} else {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_DATA);
			}
		}

		return $this->response();
	}

	/**
	 * 修改商家信息
	 * @return array
	 */
	public function actionSaveInfo()
	{
		if ($this->api_version == '1.0') {
			$params = [
				'shop_photo'  => SecurityHelper::getBodyParam('shop_photo'),
				'shop_name'   => SecurityHelper::getBodyParam('shop_name'),
				'shop_mobile' => SecurityHelper::getBodyParam('shop_mobile'),
			];

			if (!UtilsHelper::checkEmptyParams($params)) {
				$this->setCodeMessage(StateCode::OTHER_EMPTY_PARAMS);

				return $this->response();
			}

			$data = ShopAPI::saveInfoV10($this->provider_id, $params);
			if ($data) {
				$result['shop_image'] = ImageHelper::getUserPhoto($params['shop_photo']);     //头像
				$this->_data          = $result;
			} else {
				$this->setCodeMessage(StateCode::COMMON_OPERA_ERROR);
			}
		}

		return $this->response();
	}

	/**
	 * 获取全民营销二维码
	 * @return array
	 */
	public function actionGetCode()
	{
		if ($this->api_version == '1.0') {
			$data = ShopHelper::getMarketCode($this->user_id);
			if($data){
				$this->_data = $data;
				$this->_message="获取成功";
			}else{
				$this->setCodeMessage(StateCode::COMMON_GET_ERROR);

			}
		}

		return $this->response();
	}

}