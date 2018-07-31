<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/26
 */

namespace api\modules\v2\helpers;

use common\components\Ref;

class OpenStateCode
{


	//新增 全部写在这里
	const AUTH_LOGIN_FAILED = 10001;		//认证失败




	//TODO 以下用作参考 后续要删除

	//用户模块
	const USER_USER_LOGIN_FAILED     = 10001;    //登录失败
	const USER_PROVIDER_LOGIN_FAILED = 10002;    //登录失败
	const USER_NOT_EXIST             = 10003;    //用户不存在


	//小帮模块50000
	const SHOP_HAVE_ENTER    = 50001;//商家已入驻成功
	const SHOP_NOT_EXIST     = 50002;//不存在商家
	const SHOP_ENTER_FAILED  = 50003;//商家入驻失败
	const SHOP_ENTERING      = 50004;//商家审核中
	const SHOP_ACCOUNT_EXIST = 50005;//提现账号存在
	const SHOP_CANT_ROB      = 50006;//商家不符合抢单要求

	//订单模块60000
	const ORDER_SEA_EMPTY = 60000;//订单海没有订单

	const ERRAND_CREATE                 = 60001;//保存订单失败
	const ERRAND_PER_PAYMENT            = 60002;//预支付失败
	const ERRAND_PER_PAYMENT_BALANCE    = 60003;//余额支付失败
	const ERRAND_DETAIL                 = 60004;//暂无订单明细
	const ERRAND_ROBBING                = 60005;//订单被抢
	const ERRAND_WORKER_DETAIL          = 60006;//worker 该订单无详情数据
	const ERRAND_WORKER_PROGRESS        = 60007;//worker 状态更改不成功
	const ERRAND_USER_CONFIRM           = 60008;//user 订单确认完成失败
	const ERRAND_WORKER_CANCEL          = 60009;//worker 您取消订单失败，请联系客服;
	const ERRAND_USER_CANCEL            = 60010;//user 您取消订单失败，请联系客服;
	const ERRAND_CANCEL_PROGRESS        = 60011;//您取消订单失败，请联系客服;
	const ERRAND_PER_PAYMENT_WECHAT     = 60012;//微信支付失败;
	const ERRAND_PER_PAYMENT_ALIPAY     = 60013;//支付宝支付失败;
	const ERRAND_CALCULATION            = 60014;//支付宝支付失败;
	const ERRAND_DELETE                 = 60015;//不符合删除条件
	const ERRAND_ADD_CUSTOM_FEE         = 60016;//添加小费失败
	const ERRAND_ADD_CUSTOM_FEE_BALANCE = 60017;//添加小费失败
	const ERRAND_ADD_CUSTOM_FEE_WXPAY   = 60018;//添加小费失败
	const ERRAND_ADD_CUSTOM_FEE_ALIPAY  = 60019;//添加小费失败
	const ERRAND_PAYMENT_CASH           = 60020;//现金支付更新数据失败
	const ERRAND_ROBBING_FAIL           = 60021;//抢单失败

	//帮我买
	const ERRAND_BUY_CREATE_FAILED       = 61000;//帮我买创建订单失败
	const ERRAND_BUY_PER_PAYMENT         = 61001;//预支付失败
	const ERRAND_BUY_PER_PAYMENT_BALANCE = 61002;//余额失败
	const ERRAND_BUY_PER_PAYMENT_WECHAT  = 61003;//微信失败
	const ERRAND_BUY_PER_PAYMENT_ALIPAY  = 61004;//支付宝失败
	const ERRAND_BUY_DETAIL              = 61005;//暂无订单明细
	const ERRAND_BUY_CANCEL_PROGRESS     = 61006;//您取消订单失败，请联系客服;
	const ERRAND_BUY_WORKER_PROGRESS     = 61007;//worker 状态更改不成功
	const ERRAND_BUY_CONFIRM             = 61008;//订单暂无法确认完成
	const ERRAND_BUY_ADD_EXPENSE         = 61009;//添加配送费不成功
	const ERRAND_BUY_DELIVERY_ARRIVAL    = 61010;//请填写金额或修改不成功

	//帮我送
	const ERRAND_SEND_CREATE_FAILED       = 62000;//帮我买创建订单失败
	const ERRAND_SEND_PER_PAYMENT         = 62001;//预支付失败
	const ERRAND_SEND_PER_PAYMENT_BALANCE = 62002;//余额失败
	const ERRAND_SEND_PER_PAYMENT_WECHAT  = 62003;//微信失败
	const ERRAND_SEND_PER_PAYMENT_ALIPAY  = 62004;//支付宝失败
	const ERRAND_SEND_DETAIL              = 62005;//暂无订单明细
	const ERRAND_SEND_CANCEL_PROGRESS     = 62006;//您取消订单失败，请联系客服;
	const ERRAND_SEND_WORKER_PROGRESS     = 62007;//worker 状态更改不成功
	const ERRAND_SEND_CONFIRM             = 62008;//订单暂无法确认完成
	const ERRAND_SEND_ADD_FEE             = 62009;//增加小费


	//支付模块80000

	//提现模块40000
	const WITHDRAW_LEAST_MONEY      = 40001;
	const WITHDRAW_OVER_MONEY       = 40002;
	const WITHDRAW_BAIL_DOING_ORDER = 40003;
	const WITHDRAW_BAIL_CANT        = 40005;
	const WITHDRAW_BAIL_FAILED      = 40006;

	//其他模块90000
	const OTHER_EMPTY_DATA      = 90001;//暂无数据
	const OTHER_EVALUATE        = 90002;//暂无该分类评价
	const OTHER_EVALUATE_SAVE   = 90003;//保存评价失败,请稍后再试
	const OTHER_FEEDBACK_SAVE   = 900016;//反馈提交失败，请稍后再试
	const SMS_INCORRECT_MOBILE  = 90004;//"输入的手机不符"
	const SMS_INCORRECT_CODE    = 90005;//输入的验证码错误
	const SMS_GET_CODE_FAILED   = 90006;//"获取验证码失败"
	const PWD_PAY_CHANGE_FAILED = 90007;//修改支付密码失败;
	const COMMON_OPERA_ERROR    = 90008;//操作失败
	const COMMON_OPERA_SUCCESS  = 90009;//操作成功
	const PWD_CHANGE_FAILED     = 90010;//修改登陆密码失败;
	const PWD_PAY_INCORRECT     = 90011;//支付密码不正确
	const COMMON_GET_ERROR      = 90012;//获取信息失败
	const COMMON_GET_SUCCESS    = 90013;//获取信息成功
	const OTHER_MOBILE_EXIST    = 90014;//电话已经存在

	const CHECK_UPDATE_FAILED = 91000;//暂无更新信息


	public static function get($key)
	{
		$data = [
			//小帮模块
			self::SHOP_HAVE_ENTER    => "小帮已入驻成功",
			self::SHOP_NOT_EXIST     => "不存在商家",
			self::SHOP_ENTER_FAILED  => '商家入驻失败',
			self::SHOP_ENTERING      => '商家审核中,在1~3个工作日内会审核成功',
			self::SHOP_CANT_ROB      => '请缴纳保证金方可接单',
			self::SHOP_ACCOUNT_EXIST => '提现账号存在',

			//订单模块 60000
			self::ORDER_SEA_EMPTY    => '没有可接的订单',

			self::ERRAND_CREATE                   => "创建订单失败",
			self::ERRAND_PER_PAYMENT              => "支付不成功，请重试",
			self::ERRAND_PER_PAYMENT_BALANCE      => '您的账户余额不足或支付失败',
			self::ERRAND_DETAIL                   => '暂无订单明细',
			self::ERRAND_ROBBING                  => '订单被抢',
			self::ERRAND_WORKER_DETAIL            => '该订单无详情数据',
			self::ERRAND_WORKER_PROGRESS          => '状态更改不成功',
			self::ERRAND_USER_CONFIRM             => '订单未完成',
			self::ERRAND_WORKER_CANCEL            => '您取消订单失败，请联系客服',
			self::ERRAND_USER_CANCEL              => '您取消订单失败，请联系客服',
			self::ERRAND_CANCEL_PROGRESS          => '您取消订单失败，请联系客服',
			self::ERRAND_PER_PAYMENT_WECHAT       => '微信支付失败',
			self::ERRAND_PER_PAYMENT_ALIPAY       => '支付宝支付失败',
			self::ERRAND_CALCULATION              => '计价器正在计算',
			self::ERRAND_DELETE                   => '不符合删除条件',
			self::ERRAND_ADD_CUSTOM_FEE           => '添加小费失败',
			self::ERRAND_PAYMENT_CASH             => '现金支付更新数据失败',
			self::ERRAND_ROBBING_FAIL             => "抢单失败",

			//订单帮我买
			self::ERRAND_BUY_CREATE_FAILED        => '创建订单失败',
			self::ERRAND_BUY_PER_PAYMENT          => '支付不成功，请重试',
			self::ERRAND_BUY_PER_PAYMENT_BALANCE  => "余额不足或支付失败",
			self::ERRAND_BUY_PER_PAYMENT_WECHAT   => '微信支付失败',
			self::ERRAND_BUY_PER_PAYMENT_ALIPAY   => '支付宝支付失败',
			self::ERRAND_BUY_DETAIL               => '暂无订单明细',
			self::ERRAND_BUY_WORKER_PROGRESS      => '状态更改不成功',
			self::ERRAND_BUY_CANCEL_PROGRESS      => '您取消订单失败，请联系客服',
			self::ERRAND_BUY_CONFIRM              => '订单暂无法确认完成',
			self::ERRAND_BUY_ADD_EXPENSE          => '商品费用修改不成功或请不要重复提交',
			self::ERRAND_BUY_DELIVERY_ARRIVAL     => '请填写金额或修改不成功',

			//订单帮我送
			self::ERRAND_SEND_CREATE_FAILED       => '创建订单失败',
			self::ERRAND_SEND_PER_PAYMENT         => '支付不成功，请重试',
			self::ERRAND_SEND_PER_PAYMENT_BALANCE => "余额不足或支付失败",
			self::ERRAND_SEND_PER_PAYMENT_WECHAT  => '微信支付失败',
			self::ERRAND_SEND_PER_PAYMENT_ALIPAY  => '支付宝支付失败',
			self::ERRAND_SEND_DETAIL              => '暂无订单明细',
			self::ERRAND_SEND_WORKER_PROGRESS     => '状态更改不成功',
			self::ERRAND_SEND_CANCEL_PROGRESS     => '您取消订单失败，请联系客服',
			self::ERRAND_SEND_CONFIRM             => '订单暂无法确认完成',
			self::ERRAND_SEND_ADD_FEE             => '添加小费不成功',

			self::WITHDRAW_LEAST_MONEY      => "提现不得小于" . Ref::WITHDRAW_LEAST_MONEY . "块钱",
			self::WITHDRAW_OVER_MONEY       => "输入金额大于可提现金额",
			self::WITHDRAW_BAIL_DOING_ORDER => "还有未完成的订单，不能解冻保证金",
			self::WITHDRAW_BAIL_CANT        => "不符合解冻保证金的条件",
			self::WITHDRAW_BAIL_FAILED      => "解冻保证金失败",

			//其他模块 90000
			self::OTHER_EMPTY_DATA          => '暂无数据',
			self::OTHER_EVALUATE            => '评价功能暂停使用',
			self::OTHER_EVALUATE_SAVE       => '保存评价失败,请稍后再试',
			self::OTHER_FEEDBACK_SAVE       => "反馈提交失败，请稍后再试",
			self::SMS_INCORRECT_MOBILE      => '请填写正确的手机号码',
			self::SMS_GET_CODE_FAILED       => '获取验证码失败',
			self::SMS_INCORRECT_CODE        => '输入的验证码错误',
			self::PWD_PAY_CHANGE_FAILED     => "修改支付密码失败",
			self::PWD_CHANGE_FAILED         => "修改登陆密码失败",
			self::PWD_PAY_INCORRECT         => "输入的支付密码不正确",
			self::COMMON_GET_ERROR          => "获取信息失败",
			self::COMMON_GET_SUCCESS        => "获取信息成功",
			self::OTHER_MOBILE_EXIST        => "该电话号码已经存在",
		];

		return isset($data[$key]) ? $data[$key] : null;
	}
}

































