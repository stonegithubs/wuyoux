<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/26
 */

namespace api_wx\modules\biz\helpers;

class StateCode
{
	//企业送模块50000
	const SHOP_HAVE_ENTER   = 50001;//商家已入驻成功
	const SHOP_NOT_EXIST    = 50002;//不存在商家
	const SHOP_ENTER_FAILED = 50003;//商家入驻失败
	const SHOP_ENTERING     = 50004;//商家审核中

	const BUSINESS_ENTER_FAILED  = 50005;//企业送入住失败
	const BUSINESS_UPDATE_FAILED = 50006;//企业送更新失败
	const BUSINESS_HAVE_ENTER    = 50007;//企业用户已经存在

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
	const ERRAND_DELETE                 = 60015;//删除失败
	const ERRAND_ADD_CUSTOM_FEE         = 60016;//添加小费失败
	const ERRAND_ADD_CUSTOM_FEE_BALANCE = 60017;//添加小费失败
	const ERRAND_ADD_CUSTOM_FEE_WXPAY   = 60018;//添加小费失败
	const ERRAND_ADD_CUSTOM_FEE_ALIPAY  = 60019;//添加小费失败
	const ERRAND_PAYMENT_CASH           = 60020;//现金支付更新数据失败

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

	//企业送
	const BIZ_SEND_CREATE_FAILED       = 63000;//企业送创建订单失败
	const BIZ_SEND_PER_PAYMENT         = 63001;//预支付失败
	const BIZ_SEND_PER_PAYMENT_BALANCE = 63002;//余额失败
	const BIZ_SEND_PER_PAYMENT_WECHAT  = 63003;//微信失败
	const BIZ_SEND_CALC_FAILED         = 63004;//计价失败
	const BIZ_SEND_DETAIL              = 63005;//暂无订单明细
	const BIZ_SEND_CANCEL_PROGRESS     = 63006;//您取消订单失败，请联系客服;
	const BIZ_SEND_WORKER_PROGRESS     = 63007;//worker 状态更改不成功
	const BIZ_SEND_CONFIRM             = 63008;//订单暂无法确认完成
	const BIZ_SEND_ADD_FEE             = 63009;//增加小费

	const BIZ_SEND_ADD_DISTRICT_IS_NULL = 63010;//请输入配送区域
	const BIZ_SEND_ADD_DISTRICT_EXIST   = 63011;//配送区域已经存在
	const BIZ_SEND_ADD_DISTRICT_NUM     = 63012;//配送区域数量已超限，无法再添加
	const BIZ_SEND_ADD_DISTRICT         = 63013;//添加配送区域失败
	const BIZ_SEND_DELETE_DISTRICT      = 63014;//删除用户配送区域失败

	const BIZ_SEND_TMP_INDEX        = 64000;    //无数据列表
	const BIZ_SEND_TMP_UPDATE       = 64001;    //临时数据更新失败
	const BIZ_SEND_TMP_ADD          = 64002;    //临时数据添加失败
	const BIZ_SEND_TMP_DEL          = 64003;    //临时数据删除
	const BIZ_SEND_PAY_BATCH_EXPIRE = 64010;    //请求支付数据过期,请重试


	//支付模块80000
	const TRANSACTION_FAILED = 80001;//创建流水失败

	//其他模块90000
	const OTHER_EMPTY_DATA         = 90001;//暂无数据
	const OTHER_EVALUATE           = 90002;//暂无该分类评价;
	const OTHER_EVALUATE_SAVE      = 90003;//保存评价失败,请稍后再试;
	const OTHER_SMS_INCORRECT_CODE = 90004;//验证码错误
	const OTHER_MOBILE_NO_EXIST    = 90005;//邀请人不存在
	const OTHER_MOBILE_EXIST       = 90006;//该手机已经存在
	const OTHER_PWD_SAME           = 90007;//输入的密码与旧密码相同
	const COMMON_OPERA_ERROR       = 90008;//操作失败
	const COMMON_OPERA_SUCCESS     = 90009;//操作成功
	const OTHER_PWD_INCORRECT      = 90010;//密码不正确
	const SET_USERID_INCORRECT     = 90011;//设置用户ID失败
	const OVER_CODE_MAX_NUM	       = 90012;//验证码超过今天最大次数

	public static function get($key)
	{
		$data = [
			//小帮模块
			self::SHOP_HAVE_ENTER   => "小帮已入驻成功",
			self::SHOP_NOT_EXIST    => "不存在商家",
			self::SHOP_ENTER_FAILED => '商家入驻失败',
			self::SHOP_ENTERING     => '商家审核中,在1~3个工作日内会审核成功',

			self::BUSINESS_ENTER_FAILED  => '企业入驻失败！您重新提交资料。',
			self::BUSINESS_UPDATE_FAILED => '企业送更新失败',
			self::BUSINESS_HAVE_ENTER    => '企业入驻失败！您已提交申请，请耐心等待审核结果。',


			//订单模块 60000
			self::ORDER_SEA_EMPTY        => '没有可接的订单',

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

			self::TRANSACTION_FAILED        => "创建流水失败",
			self::BIZ_SEND_PAY_BATCH_EXPIRE => '请求支付数据过期,请重试',

			//企业送
			self::BIZ_SEND_ADD_DISTRICT_IS_NULL   => "请输入配送区域",
			self::BIZ_SEND_ADD_DISTRICT_EXIST     => "配送区域已经存在",
			self::BIZ_SEND_ADD_DISTRICT_NUM       => "配送区域数量已超限，无法再添加",
			self::BIZ_SEND_ADD_DISTRICT           => "添加配送区域失败",
			self::BIZ_SEND_DELETE_DISTRICT        => "删除用户配送区域失败",

			//其他模块 90000
			self::OTHER_EMPTY_DATA          => '暂无数据',
			self::OTHER_EVALUATE            => '评价功能暂停使用',
			self::OTHER_EVALUATE_SAVE       => '保存评价失败,请稍后再试',
			self::OTHER_SMS_INCORRECT_CODE  => '输入的验证码有误',
			self::OTHER_MOBILE_NO_EXIST     => "该手机号码不存在",
			self::OTHER_MOBILE_EXIST        => '该手机号码已经存在',
			self::OTHER_PWD_SAME            => "新密码不能与旧密码一样",
			self::OTHER_PWD_INCORRECT       => "输入的密码不正确",
		];

		return isset($data[$key]) ? $data[$key] : null;
	}
}

































