<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/17
 */

namespace api\modules\api\controllers;

use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\orders\ErrandBuyHelper;
use common\helpers\orders\ErrandDoHelper;
use common\helpers\orders\ErrandHelper;
use common\helpers\orders\ErrandSendHelper;
use common\helpers\orders\TripBikeHelper;
use common\helpers\orders\TripHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\PushHelper;
use Yii;

class OrderController extends ControllerAccess
{

	/**
	 * 企业送修改收货地址
	 * @return array
	 */
	public function actionBizSendPrice()
	{
		$params['order_no']         = SecurityHelper::getBodyParam('order_no');
		$params['current_address']  = SecurityHelper::getBodyParam('current_address');
		$params['current_location'] = SecurityHelper::getBodyParam('current_location');
		$result                     = BizSendHelper::updateDeliveryPrice($params);

		if (!$result) {
			$this->_code    = -200;
			$this->_message = "更新失败！";
		} else {
			$this->_message = "更新成功！";
		}

		return $this->response();
	}

	/**
	 * 快送类取消订单
	 */
	public function actionErrandCancel()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$result   = ErrandHelper::platformCancel($order_no);
		if (!$result) {
			$this->_code    = -200;
			$this->_message = "取消订单失败！";
		} else {
			$this->_message = "取消订单成功！";
		}

		return $this->response();
	}

	/**
	 * 小帮快送 指派
	 * @return array
	 */
	public function actionErrandAssign()
	{
		$params['order_no']          = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']       = SecurityHelper::getBodyParam('provider_id');
		$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = SecurityHelper::getBodyParam('provider_mobile');
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');//小帮跟订单起点的距离
		Yii::$app->debug->log_info('errand_assign_params', $params);

		$data = ErrandHelper::saveRobbing($params);
		if ($data) {

			$this->_data = $data;
			if ($data['errand_type'] == Ref::ERRAND_TYPE_BUY) {

				ErrandBuyHelper::pushToUserNotice($data['order_no'], ErrandBuyHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

			} else if ($data['errand_type'] == Ref::ERRAND_TYPE_DO) {

				ErrandDoHelper::pushToUserNotice($data['order_no'], ErrandDoHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);

			} elseif ($data['errand_type'] == Ref::ERRAND_TYPE_SEND) {
				ErrandSendHelper::pushToUserNotice($data['order_no'], ErrandsendHelper::PUSH_USER_TYPE_TASK_PROGRESS, $data);
			}
			ErrandHelper::pushAssignToProvider($data['order_no'], ErrandHelper::PUSH_PROVIDER_TYPE_ORDER_ASSIGN);
		} else {
			$this->_code    = -200;
			$this->_message = "指派小帮快送失败";
		}

		return $this->response();
	}

	//小帮快送和企业送生成订单 改派
	public function actionErrandReAssign()
	{
		$params['order_no']          = SecurityHelper::getBodyParam('order_no');
		$params['provider_id']       = SecurityHelper::getBodyParam('provider_id');
		$provider                    = UserHelper::getShopInfo($params['provider_id'], ['shops_address', 'shops_location', 'shops_location_lat', 'shops_location_lng', 'utel']);
		$params['provider_location'] = "[" . $provider['shops_location_lng'] . "," . $provider['shops_location_lat'] . "]";
		$params['provider_address']  = $provider['shops_address'];
		$params['provider_mobile']   = $provider['utel'];
		$data                        = ErrandHelper::saveReassign($params);
		if ($data) {

			ErrandHelper::pushAssignToProvider($data['order_no'], ErrandHelper::PUSH_PROVIDER_TYPE_ORDER_REASSIGN);
		} else {
			$this->_code    = -200;
			$this->_message = "改派小帮快送失败";
		}

		return $this->response();
	}

	//企业送 临时订单 指派
	public function actionBizTmpAssign()
	{
		$params['tmp_no']            = SecurityHelper::getBodyParam('tmp_no');
		$params['provider_id']       = SecurityHelper::getBodyParam('provider_id');
		$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
		$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
		$params['provider_mobile']   = SecurityHelper::getBodyParam('provider_mobile');
		$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance', 0);//小帮跟订单起点的距离
		Yii::$app->debug->log_info('biz_order_rob_params', $params);

		$data = BizSendHelper::saveTmpRobbing($params);
		if ($data) {
			BizSendHelper::pushTmpToUserNotice($data['tmp_no'], PushHelper::TMP_BIZ_SEND_WORKER_TASK);    //推送给用户
			BizSendHelper::pushTmpToProviderNotice($data['tmp_no'], BizSendHelper::PUSH_TMP_TYPE_ASSIGN_PROVIDER);    //推送给小帮
		} else {

			$this->_code    = -200;
			$this->_message = "指派企业送用户发单失败";
		}

		return $this->response();

	}

	//企业送 临时订单 改派
	public function actionBizTmpReAssign()
	{
		$params['tmp_no']            = SecurityHelper::getBodyParam('tmp_no');
		$params['provider_id']       = SecurityHelper::getBodyParam('provider_id');
		$provider                    = UserHelper::getShopInfo($params['provider_id'], ['shops_address', 'shops_location', 'shops_location_lat', 'shops_location_lng', 'utel']);
		$params['provider_location'] = "[" . $provider['shops_location_lng'] . "," . $provider['shops_location_lat'] . "]";
		$params['provider_address']  = $provider['shops_address'];
		$params['provider_mobile']   = $provider['utel'];
		$data = BizSendHelper::saveTmpReassign($params);
		if ($data) {
			BizSendHelper::pushTmpToProviderNotice($data['tmp_no'], BizSendHelper::PUSH_TMP_TYPE_ASSIGN_PROVIDER);    //推送给小帮
		} else {

			$this->_code    = -200;
			$this->_message = "改派企业送用户发单失败";
		}

		return $this->response();
	}

	//企业送 临时订单 取消订单
	public function actionBizTmpCancel()
	{
		$params['tmp_no']      = SecurityHelper::getBodyParam('tmp_no');
		$params['cancel_type'] = Ref::ERRAND_CANCEL_DEAL_NOTIFY;
		$result                = BizSendHelper::platTmpOrderCancel($params);
		if (!$result) {
			$this->_code    = -200;
			$this->_message = "取消失败";
		}

		return $this->response();
	}

	/**
	 * 小帮出行 指派
	 */
	public function actionTripBikeAssign()
	{

		$provider_id = SecurityHelper::getBodyParam('provider_id');
		$shopInfo    = UserHelper::getShopInfo($provider_id, ["plate_numbers", "utel", "guangbi"]);
		if ($shopInfo) {
			$params['order_no']          = SecurityHelper::getBodyParam('order_no'); //订单号
			$params['provider_id']       = $provider_id;
			$params['provider_location'] = SecurityHelper::getBodyParam('provider_location');
			$params['provider_address']  = SecurityHelper::getBodyParam('provider_address');
			$params['provider_mobile']   = isset($shopInfo['utel']) ? $shopInfo['utel'] : null;
			$params['starting_distance'] = SecurityHelper::getBodyParam('starting_distance');
			$params['license_plate']     = isset($shopInfo['plate_numbers']) ? $shopInfo['plate_numbers'] : null;

			$res = TripBikeHelper::saveRobbing($params);
			if ($res) {

				TripBikeHelper::pushToUserNotice($res['order_id'], TripBikeHelper::PUSH_USER_TYPE_TASK_PROGRESS, $res);
				TripBikeHelper::pushToProviderNotice($res['order_id'], TripBikeHelper::PUSH_PROVIDER_TYPE_ASSIGN, $res);
				$this->_message = "抢单成功！";
			} else {

				$this->_code    = -200;
				$this->_message = "抢单失败！";
			}
		}

		return $this->response();
	}

	/**
	 * Trip类 取消
	 */
	public function actionTripCancel()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$result   = TripHelper::platformCancel($order_no);
		if (!$result) {
			$this->_code    = -200;
			$this->_message = "取消订单失败！";
		} else {
			$this->_message = "取消订单成功！";
		}

		return $this->response();
	}

	/**
	 * 企业送撤销扣款
	 *
	 * @return array
	 */
	public function actionBizRevokePayment()
	{
		$order_no = SecurityHelper::getBodyParam('order_no');
		$result   = BizSendHelper::revokePayment($order_no);
		if (!$result) {
			$this->handleFailure();
		} else {
			$this->_message = "撤销扣款成功！";
		}

		return $this->response();
	}


}