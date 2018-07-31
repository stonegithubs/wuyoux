<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/19
 */
namespace m\controllers;

class CustomerServiceController extends ControllerWeb
{
	/**
	 * 用户客服中心首页
	 * @return string
	 */
	public function actionUserIndex()
	{
		return $this->renderPartial('user-index');
	}


	/**
	 * 用户常见问题
	 * @param $doc
	 * @return string
	 */
	public function actionUserProblem($doc)
	{
		//小帮到店无货取问题
		if ($doc == 'get_store') {
			return $this->renderPartial('get-store');
		}
		//未支付小帮服务费，不能下单？
		if ($doc == 'not_create_order') {
			return $this->renderPartial('not-create-order');
		}
		//怎么收费的？
		if ($doc == 'how_charge') {
			return $this->renderPartial('how-charge');
		}
		//订单服务费不合理？
		if ($doc == 'service_charge_unreasonable') {
			return $this->renderPartial('service-charge-unreasonable');
		}
		//平台会自动扣费？
		if ($doc == 'automatic_deduction') {
			return $this->renderPartial('automatic-deduction');
		}
		//若配送物品损坏，如何理赔？
		if ($doc == 'how_claim') {
			return $this->renderPartial('how-claim');
		}
	}

	/**
	 * 小帮客服中心首页
	 * @return string
	 */
	public function actionProviderIndex()
	{
		return $this->renderPartial('provider-index');
	}

	/**
	 * 小帮常见问题
	 * @param $doc
	 * @return string
	 */
	public function actionProviderProblem($doc)
	{
		//注册时收不到验证码
		if ($doc == 'register_code') {
			return $this->renderPartial('register-code');
		}
		//注册会提示"定位失败，请返回重试"
		if ($doc == 'register_position') {
			return $this->renderPartial('register-position');
		}
		//未支付小帮服务费，不能下单
		if ($doc == 'service_charges') {
			return $this->renderPartial('service-charges');
		}
		//小帮接收不到订单推送
		if ($doc == 'push_error') {
			return $this->renderPartial('push-error');
		}
		//小帮定位位置会有偏差
		if ($doc == 'position_deviation') {
			return $this->renderPartial('position-deviation');
		}
		//企业送订单，小帮配送到达后生成的地址有偏差
		if ($doc == 'biz_address_deviation') {
			return $this->renderPartial('biz-address-deviation');
		}
		//小帮抢单后界面位置及用户信息等显示不全
		if ($doc == 'user_info_incomplete') {
			return $this->renderPartial('user-info-incomplete');
		}
		//今日接单有5单，但怎么显示4单
		if ($doc == 'order_num_inaccurate') {
			return $this->renderPartial('order-num-inaccurate');
		}
		//订单配送完成后，配送费用没有到账
		if ($doc == 'not_account') {
			return $this->renderPartial('not-account');
		}
		//小帮版耗电问题
		if ($doc == 'power_problem') {
			return $this->renderPartial('power-problem');
		}
	}
}