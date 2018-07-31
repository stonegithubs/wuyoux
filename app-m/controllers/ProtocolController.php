<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/5
 */

namespace m\controllers;

use common\helpers\utils\UrlHelper;

/**
 * 协议
 * Class RuleController
 * @package m\controllers
 */
class ProtocolController extends ControllerWeb
{
	public function actionIndex($doc)
	{

		//配送服务说明
		if ($doc == 'errand') {
			return $this->renderPartial("errand");
		}

		//小帮使用手册
		if ($doc == 'provider_use') {
			return $this->renderPartial("provider_use");
		}

		//小帮入驻协议
		if ($doc == 'provider_enter') {
			return $this->renderPartial("provider_enter");
		}

		//用户使用说明
		if ($doc == 'user_use') {
			return $this->renderPartial("user_use");
		}

		//关于无忧帮帮
		if ($doc == 'wy_about') {
			return $this->renderPartial("wy_about");
		}

		//无忧帮帮声明
		if ($doc == 'wy_declaration') {
			return $this->renderPartial("wy_declaration");
		}

		//提现收费说明
		if ($doc == 'cash_withdrawal') {
			return $this->renderPartial("cash_withdrawal");
		}

		//优惠券匹配规则
		if ($doc == 'match_card_rule') {
			return $this->renderPartial("match_card_rule");
		}

		//充值协议
		if ($doc == 'recharge_rule') {
			return $this->renderPartial("recharge_rule");
		}

		//保证金管理协议
		if ($doc == 'bail_management') {
			return $this->renderPartial("bail_management");
		}

		//小帮出行服务协议
		if ($doc == 'motorcycle_rule') {
			return $this->renderPartial("motorcycle_rule");
		}

		//无忧帮帮下单须知
		if ($doc == 'create_order_know') {
			return $this->renderPartial("create_order_know");
		}

		//保险说明首页
		if ($doc == 'bail_explain_index') {
			$data = [
				'bail_explain_url'      => UrlHelper::webLink(['protocol/index', 'doc' => 'bail_explain']),
				'motorcycle_bail_url'   => UrlHelper::webLink(['protocol/index', 'doc' => 'motorcycle_bail']),
				'electrombile_bail_url' => UrlHelper::webLink(['protocol/index', 'doc' => 'electrombile_bail']),
				'settlement_claim_url'  => UrlHelper::webLink(['protocol/index', 'doc' => 'settlement_claim']),
			];

			return $this->renderPartial("bail-explain-index", $data);
		}

		//保险说明
		if ($doc == 'bail_explain') {
			return $this->renderPartial("bail-explain");
		}

		//个人摩托车驾驶人意外伤害保险条款
		if ($doc == 'motorcycle_bail') {
			return $this->renderPartial("motorcycle-bail");
		}

		//非机动车驾驶人意外伤害保险条款
		if ($doc == 'electrombile_bail') {
			return $this->renderPartial("electrombile-bail");
		}

		//理赔流程
		if ($doc == 'settlement_claim') {
			return $this->renderPartial("settlement-claim");
		}

		return $this->renderPartial("index");
	}
}