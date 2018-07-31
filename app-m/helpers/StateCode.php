<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/26
 */

namespace m\helpers;

use Yii;

class StateCode
{
	//全民营销
	const  OTHER_EMPTY_DATA       = 90001;//暂无数据
	const  BIND_TRANSFER_FAIL     = 90002;//绑定提现账号失败
	const  WITHDRAW_FAIL          = 90003;//余额不足，无法提现
	const  TRANSFER_FAIL          = 90004;//转到余额失败
	const  CLEAR_TRANSFER_FAIL    = 90005;//清除提现账号失败
	const  USER_EXISTENCE         = 90006;//账号已存在
	const  GET_CODE_FAIL          = 90007;//获取验证码失败
	const  SMS_INCORRECT_CODE     = 90008;//验证码错误或失效
	const  INVITE_MOBILE_NO_EXIST = 90009;//邀请用户不存在
	const  REGISTER_FAIL          = 90010;//注册失败,请重新填写
	const  GET_CODE_FREQUENTLY    = 90011;//获取太频繁
	const  GRAPH_CODE_FAIL        = 90012;//图形验证码错误
	const  PROVIDER_EXISTENCE     = 90013;//小帮已存在
	const  ACTIVITY_OVERDUE       = 90014;//活动已过期
	const  GIFT_RECEIVED          = 90015;//红包已领取

	public static function get($key)
	{
		$data = [
			//全民营销
			self::OTHER_EMPTY_DATA       => '暂无数据',
			self::BIND_TRANSFER_FAIL     => '绑定提现账号失败',
			self::WITHDRAW_FAIL          => '余额不足，无法提现',
			self::TRANSFER_FAIL          => '转到余额失败',
			self::CLEAR_TRANSFER_FAIL    => '清除提现账号失败',
			self::USER_EXISTENCE         => '账号已存在',
			self::GET_CODE_FAIL          => '验证码发送失败',
			self::SMS_INCORRECT_CODE     => '验证码错误或失效',
			self::INVITE_MOBILE_NO_EXIST => '邀请用户不存在',
			self::REGISTER_FAIL          => '注册失败,请重新填写',
			self::GET_CODE_FREQUENTLY    => '获取太频繁',
			self::GRAPH_CODE_FAIL        => '图形验证码错误',
			self::PROVIDER_EXISTENCE     => '小帮已存在',
			self::ACTIVITY_OVERDUE       => '活动已过期',
			self::GIFT_RECEIVED          => '红包已领取',
		];

		return isset($data[$key]) ? $data[$key] : null;
	}
}