<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace common\components;
class Ref
{

	const PLATFORM_PHONE = '4001355188';
	const SERVICE_TIME   = '9：00 - 21：00';

	const SERVER_PRICE         = 20.00;    //帮我办每小时服务费。
	const WITHDRAW_LEAST_MONEY = 10;       //提现最少的金额
	const BAIL_MONEY           = 200;      //保证金
	const BAIL_RETURN_DAY      = 15;       //保证金15天可退
	const INSURANCE_FEE        = 2;        //首单保险费用扣2元
	/************ 时间 ************/
	const TTL_AUTO_CONFIRM_ERRAND_ORDER = 60 * 60 * 24;    //快送自动确认收货时间

	/************ 订单状态（正向流程） ************/
	//进入履行前
	const ORDER_STATUS_DEFAULT        = 0;  //待处理
	const ORDER_STATUS_AWAITING_PAY   = 1;  //等待支付
	const ORDER_STATUS_PAYMENT_VERIFY = 2;  //等待网关确认支付 (对于通过支付网关"在线支付"，一般由网关返回信息自动处理本状态)
	const ORDER_STATUS_CANCEL         = 3;  //客户取消 （前2个状态可以跳到订单取消；若已支付了，则需要做退款处理）
	const ORDER_STATUS_DECLINE        = 4;  //商家取消

	//进入履行后
	const ORDER_STATUS_DOING     = 5;  //进行中（支付完成后 进入进行中）
	const ORDER_STATUS_COMPLETED = 6;  //已完成
	const ORDER_STATUS_EVALUATE  = 7;  //已评价
	const ORDER_STATUS_DISPUTE   = 8;  //客服处理（双方不同意取消订单，进入纠纷）
	const ORDER_STATUS_CALL_OFF  = 9;  //平台取消

	/************ 订单类型 ****************/
	const ORDER_TYPE_TRIP   = 1;        //小帮出行
	const ORDER_TYPE_ERRAND = 2;        //小帮快送

	/************ 出行类型 ****************/
	const TRIP_TYPE_BIKE  = 1;    //摩的专车
	const TRIP_TYPE_CAR   = 2;    //小帮专车
	const TRIP_TYPE_EBIKE = 3;    //电动车

	/************ 出行状态 ****************/
	const TRIP_STATUS_WAIT   = 1;    //等待接单
	const TRIP_STATUS_PICKED = 2;    //小帮接单
	const TRIP_STATUS_POINT  = 3;    //到达起点
	const TRIP_STATUS_START  = 4;    //开始出行
	const TRIP_STATUS_END    = 5;    //结束行程

	/************ 订单类型 ****************/
	const ERRAND_TYPE_BUY  = 1;    //帮我买
	const ERRAND_TYPE_SEND = 2;    //帮我送
	const ERRAND_TYPE_DO   = 3;    //帮我办
	const ERRAND_TYPE_BIZ  = 4;    //企业送


	/************ 订单支付状态 ************/
	const PAY_STATUS_WAIT           = 1;         //未支付
	const PAY_STATUS_COMPLETE       = 2;         //已支付  （支付状态会触发订单状态的变更）
	const PAY_STATUS_REFUND_PARTIAL = 3;         //部分退款
	const PAY_STATUS_REFUND_ALL     = 4;         //全部退款
	const PAY_STATUS_REFUND_PENDING = 5;         //待退款

	/************ 分类ID ************/
	const CATE_ID_FOR_TRAFFIC_TRAVEL = 46;    //交通出行ID
	const CATE_ID_FOR_MOTOR       = 51;    //小帮出行ID
	const CATE_ID_FOR_ERRAND      = 132;    //跑腿分类ID
	const CATE_ID_FOR_BIZ_SEND_TMP= 133;    //企业送临时订单
	const CATE_ID_FOR_ERRAND_BUY  = 135;    //帮我买
	const CATE_ID_FOR_ERRAND_SEND = 136;    //帮我送
	const CATE_ID_FOR_ERRAND_DO   = 137;    //帮我办
	const CATE_ID_FOR_BIZ_SEND    = 138;    //企业送


	/************ 快送状态 **********/
	const ERRAND_STATUS_WAITE   = 1;    //等待接单
	const ERRAND_STATUS_PICKED  = 2;    //小帮已接单
	const ERRAND_STATUS_CONTACT = 3;    //联系用户
	const ERRAND_STATUS_DOING   = 4;    //正在服务
	const ERRAND_STATUS_FINISH  = 5;    //服务完成
	const ERRAND_STATUS_PAY     = 6;    //配送费用
	const ERRAND_STATUS_PHOTO   = 7;    //拍摄照片
	/*********** 小费类型 ***********/
	const FEE_TYPE_ORDER  = 1;    //小费订单
	const FEE_TYPE_PROD   = 2;    //商品费用
	const FEE_TYPE_REWARD = 3;     //打赏小帮

	/*********** 支付类型 ***********/
	const PAYMENT_TYPE_BALANCE = 1;    //余额
	const PAYMENT_TYPE_CASH    = 2;    //现金
	const PAYMENT_TYPE_ALIPAY  = 3;    //支付宝
	const PAYMENT_TYPE_WECHAT  = 4;    //微信支付

	/********** 订单来源 ***********/
	const ORDER_FROM_ANDROID       = 1;    //Android
	const ORDER_FROM_IOS           = 2;    //IOS
	const ORDER_FROM_WECHAT        = 3;    //微信
	const ORDER_FROM_PC            = 4;    //PC
	const ORDER_FROM_MINI_APP      = 5;    //小程序
	const ORDER_FROM_OPEN_PLATFORM = 6;   //开放平台

	/********** 交易类型************/
	//正向交易
	const TRANSACTION_TYPE_ORDER        = 1;    //订单
	const TRANSACTION_TYPE_RECHARGE     = 2;    //充值
	const TRANSACTION_TYPE_REFUND       = 3;    //退款
	const TRANSACTION_TYPE_TIPS         = 4;    //小费
	const TRANSACTION_TYPE_PHONE_BILL   = 5;    //话费
	const TRANSACTION_TYPE_CONTRIBUTION = 6;    //捐款
	const TRANSACTION_TYPE_BAIL         = 7;    //保证金

	//退款交易
	const TRANSACTION_TYPE_TIPS_REFUND = 14;    //小费退款


	/********** 优惠券状态 ********/
	const CARD_STATUS_NEW    = 0;    //券状态，0：未使用；1：已使用；2：已过期；3：卡券回收
	const CARD_STATUS_USED   = 1;    //已使用
	const CARD_STATUS_EXPIRE = 2;    //已过期
	const CARD_STATUS_RETURN = 3;    //已回收
	const CARD_STATUS_FROZEN = 4;    //冻结
	const CARD_STATUS_LOCKED = 5;    //锁定

	/********** 用户和企业送属于类型 ********/
	const BELONG_TYPE_USER   = 1;    //普通用户
	const BELONG_TYPE_BIZ    = 2;    //企业送
	const USER_TYPE_ALL      = 1;    //用户类型-所有
	const USER_TYPE_USER     = 2;    //用户类型-用户
	const USER_TYPE_PROVIDER = 3;    //用户类型-小帮

	/********** 订单被抢状态 ********/
	const ORDER_ROB_NEW = 0;    //未被抢
	const ORDER_ROBBED  = 1;    //被抢


	/********** 个推类型 ************/
	const GETUI_TYPE_GRAB                  = 1;    //抢单类型
	const GETUI_TYPE_GRAB_NOTICE           = 2;    //抢单通知和流程通知
	const GETUI_TYPE_CANCEL_NOTICE         = 3;    //取消订单类型
	const GETUI_TYPE_PAY                   = 4;    //支付费用
	const GETUI_TYPE_USER_CONFIRM          = 5;    //用户完成订单
	const GETUI_TYPE_ACTIVITY              = 20;    //活动信息
	const GETUI_TYPE_CUSTOM_FEE            = 21;    //添加小费
	const GETUI_TYPE_PROD_EXPENSE          = 22;    //配送费用
	const GETUI_TYPE_BIZ_GRAB              = 31;    //企业送抢单
	const GETUI_TYPE_ASSIGN_ORDER_PROVIDER = 32;    //后台订单指派
	const GETUI_TYPE_ASSIGN_ORDER_USER     = 33;    //后台订单指派

	/********** 个推To值 ***********/
	const GETUI_TO_ERRAND_ORDER = 'errand.order';    //小帮快送
	const GETUI_TO_BIZ_ORDER    = 'biz.order';      //企业送
	const GETUI_TO_TRIP_ORDER   = 'trip.order';     //小帮出行

	/*********** 推送角色 ************/
	const PUSH_ROLE_USER     = 'user';        //接收方是用户
	const PUSH_ROLE_PROVIDER = 'provider';    //接收方是小帮
	const SYSTEM_USER        = 1;            //用户端
	const SYSTEM_PROVIDER    = 2;            //小帮端
	const SHOP_MAX_RANGE     = 5;    //小帮接单最大距离


	/********* 快送取消类型 ***********/
	const ERRAND_CANCEL_USER_APPLY        = 'user_apply';            //用户申请取消订单
	const ERRAND_CANCEL_USER_AGREE        = 'user_agree';            //用户同意取消订单
	const ERRAND_CANCEL_USER_DISAGREE     = 'user_disagree';         //用户不同意取消订单
	const ERRAND_CANCEL_PROVIDER_APPLY    = 'provider_apply';        //小帮申请取消订单
	const ERRAND_CANCEL_PROVIDER_AGREE    = 'provider_agree';        //小帮同意取消订单
	const ERRAND_CANCEL_PROVIDER_DISAGREE = 'provider_disagree';     //小帮不同意取消订单
	const ERRAND_CANCEL_DEAL_NOTIFY       = 'deal_notify';           //客服处理消息;
	const ERRAND_CANCEL_USER_NOTIFY       = 'user_notify';           //客服处理消息;
	const ERRAND_CANCEL_PROVIDER_NOTIFY   = 'provider_notify';       //客服处理消息;
	const ERRAND_CANCEL_AUTO              = 'auto_cancel';       //客服处理消息;

	/********** 优惠券类型 **************/
	const COUPON_TYPE_DISCOUNT   = 1;    //折扣券
	const COUPON_TYPE_DEDUCTIBLE = 2;    //抵扣券
	const COUPON_TYPE_FREE       = 3;    //免单券
	const COUPON_TYPE_SHOP       = 4;    //商家券
	const COUPON_TYPE_ALL        = 5;    //通用券

	/********** 短信类型 **************/
	const SMS_CODE_REGISTER       = 1;    //注册
	const SMS_CODE_FIND_PASSWORD  = 2;    //找回登录
	const SMS_CODE_PAY_PASSWORD   = 3;    //支付密码
	const SMS_CODE_LOGIN_PASSWORD = 4;    //快捷登录
	/********* 图片类型 **************/
	const IMAGE_TYPE_ERRAND = 1;    //快送订单图片

	/********* 文档类型 **************/
	const DOCUMENT_ERRAND_PROTOCOL    = 1;//跑腿协议
	const DOCUMENT_PROVIDER_USE       = 2;//小帮使用手册
	const DOCUMENT_PROVIDER_ENTER     = 3;//小帮入驻协议
	const DOCUMENT_PROVIDER_AGREEMENT = 4;//小帮使用说明
	const DOCUMENT_USER_USE           = 5;//用户使用手册
	const DOCUMENT_WY_ABOUT           = 8;//关于无忧帮帮
	const DOCUMENT_WY_DECLARATION     = 9;//无忧帮帮声明
	const DOCUMENT_CASH_WITHDRAWAL    = 10;//提现收费说明
	const DOCUMENT_MATCH_CARD_RULE    = 11;//优惠券匹配规则
	const DOCUMENT_RECHARGE_RULE      = 12;//充值协议

	/********** 小帮shop表的状态*******/
	const SHOP_STATUS_WAITE    = 0;        //等待审核
	const SHOP_STATUS_PASS     = 1;        //已经审核
	const SHOP_STATUS_FAIL     = 2;        //审核失败
	const SHOP_STATUS_ADD_FILE = 3;        //待补充资料
	const SHOP_STATUS_DELETE   = 4;        //已删除

	/********** 临时发单状态 *******/
	const BIZ_TMP_STATUS_WAITE    = 1;    //等待接单
	const BIZ_TMP_STATUS_PICKED   = 2;    //已接单
	const BIZ_TMP_STATUS_INPUT    = 3;    //配送中
	const BIZ_TMP_STATUS_CANCEL   = 4;    //用户取消
	const BIZ_TMP_STATUS_DECLINE  = 5;    //小帮取消
	const BIZ_TMP_STATUS_CALL_OFF = 6;    //平台取消

	/***********小程序的类型 **********/
	const MINI_TYPE_BIZ    = 1;    //企业送小程序
	const MINI_TYPE_ERRAND = 2;    //快送小程序
	const MINI_TYPE_TRIP   = 3;    //摩的小程序
	const MINI_TYPE_MP     = 4;    //公众号

	/********** 订单被抢状态 ********/
	const ACTIVITY_FLAG_TRUCK = 1;    //货车加油
	const ACTIVITY_FLAG_GUIDE = 2;    //引导页

	/********** 投诉小帮状态 ********/
	const COMPLAINT_WAIT = 1;    //等待处理
	const COMPLAINT_DEAL = 2;   //已经处理

	//取消订单原因
	public static $cancel_info_mapping
		= [
			1 => '行程有变，暂时不需要用车',
			2 => '等待小帮时间过长',
			3 => '平台派单太远',
			4 => '小帮以各种理由不来接我',
			5 => '联系不上小帮',
			6 => '小帮找不到上车点',
			7 => '小帮服务态度恶劣',
			8 => '小帮迟到',
			9 => '其他'
		];

	//投诉小帮信息
	public static $complaint_mapping
		= [
			1 => '小帮速度太慢',
			2 => '需要等待小帮时间太久',
			3 => '小帮强收小费',
			4 => '小帮服务态度很差',
			5 => '小帮损坏物品或商品',
			6 => '小帮故意多收商品费用',
			7 => '小帮没有按要求送达具体位置',
			8 => '小帮联系不上',
			9 => '其他',
		];

	/********** 文章类型 ********/
	const  POSTS_TYPE_CAROUSEL = 1;  //轮播图
	const  POSTS_TYPE_NOTICE   = 2;  //公告
	const  POSTS_TYPE_PROTOCOL = 3;  //协议
	const  POSTS_TYPE_START_UP = 5;  //APP启动页
	const  POSTS_TYPE_POPUP    = 6;  //弹框广告

	/**********资金出入**********/
	const BALANCE_TYPE_IN  = 1;        //资金入
	const BALANCE_TYPE_OUT = 2;        //资金出

	/********* 用户收支明细类型******/
	const USER_BALANCE_DEFAULT           = 0;//未知
	const USER_BALANCE_RED_ENVELOPES     = 1;//红包收入
	const USER_BALANCE_PHONE_RECHARGE    = 2;//话费充值
	const USER_BALANCE_RECHARGE          = 3;//余额充值
	const USER_BALANCE_TODO              = 4;//待定未知
	const USER_BALANCE_ORDER_PAYMENT     = 5;//订单支付
	const USER_BALANCE_ORDER_REFUND      = 6;//旧订单退款
	const USER_BALANCE_ORDER_NEW_REFUND  = 7;//新订单退款
	const USER_BALANCE_PLATFORM_RECOVERY = 8;//平台回收
	const USER_BALANCE_MARKET_IN         = 9;//营销
	const USER_BALANCE_DONATE            = 10;//捐款
	const USER_BALANCE_COUPON            = 11;//优惠券

	/********* 小帮收支明细类型******/
	const PROVIDER_BALANCE_IN           = 1;    //收款
	const PROVIDER_BALANCE_OUT          = 2;    //提现
	const PROVIDER_BALANCE_RECHARGE     = 3;    //充值 数据有点乱
	const PROVIDER_BALANCE_RETURN       = 4;    //后台回收 数据有点乱
	const PROVIDER_BALANCE_RED          = 5;    //红包
	const PROVIDER_BALANCE_RED_REFUND   = 6;    //红包退回
	const PROVIDER_BALANCE_BAIL_FROZE   = 7;    //解冻保证金
	const PROVIDER_BALANCE_BAIL_PAY     = 8;    //缴纳保证金
	const PROVIDER_BALANCE_ONLINE       = 9;    //在线宝转余额
	const PROVIDER_BALANCE_BAIL_BALANCE = 10;   //余额支付保证金
	const PROVIDER_BALANCE_INSURANCE    = 11;   //保险扣费
	const PROVIDER_BALANCE_MARKET_IN    = 12;   //营销

	/********* 用户营销状态******/
	const USER_MARKET_NORMAL = 1;//正常
	const USER_MARKET_BAN    = 2;//封号

	/********* 提现角色******/
	const USER_WITHDRAW     = 1;//用户
	const PROVIDER_WITHDRAW = 2;//小帮

	/********* 收益时间******/
	const TODAY_PROFIT = 1;//今天收益
	const TOTAL_PROFIT = 2;//历史收益

	/********* 收益来源******/
	const PROFIT_FROM_MAID = 0;//利润分佣
	const PROFIT_FROM_GIFT = 1;//红包奖励

	/********* 红包领取状态******/
	const GIFT_UNRECEIVED = 0;//红包未领取
	const GIFT_RECEIVED   = 1;//红包已领取
	const GIFT_OVERDUE    = 2;//活动已过期

	/********* 活动类型******/
	const ACTIVITY_PACKAGE = 1;//礼包活动
	const ACTIVITY_MARKET  = 2;//全民合伙人
}



