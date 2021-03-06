<?php

//数据字典

//由于共用表，所以存在字段不明的情况
//帮我办表结构说明

$order = [
	'order_id'                 => Yii::t('app', 'Order ID'),
	'user_id'                  => Yii::t('app', '下单用户ID'),
	'order_no'                 => Yii::t('app', '订单号'),
	'cate_id'                  => Yii::t('app', '分类ID'),
	'region_id'                => Yii::t('app', '地区ID'),
	'order_type'               => Yii::t('app', '订单类型：1.摩的专车 2.小帮快送'),
	'order_from'               => Yii::t('app', '订单来源：1.android 2.IOS  3.微信'),
	'order_status'             => Yii::t('app', '订单状态：1.等待支付 2.等待网关确认支付 3.用户取消 4.小帮取消 5.进行中 6.已完成 7.已评价 8.客服处理 9.平台取消'),
	'payment_id'               => Yii::t('app', '支付方式：1.余额 2.银行卡 3.支付宝 4.微信'),
	'robbed'                   => Yii::t('app', '是否被抢：0.未抢 1已抢'),
	'payment_status'           => Yii::t('app', '支付状态：1.待支付 2.已支付 3.部分退款 4.全部退款 5.待退款'),
	'order_amount'             => Yii::t('app', '订单金额'),
	'amount_payable'           => Yii::t('app', '实付金额'),
	'discount'                 => Yii::t('app', '优惠金额=卡券金额+在线宝金额+其他优惠'),
	'provider_estimate_amount' => Yii::t('app', '小帮预计收到金额'),
	'provider_actual_amount'   => Yii::t('app', '小帮实际收到金额'),
	'card_id'                  => Yii::t('app', '用户卡券ID'),
	'online_money'             => Yii::t('app', '在线宝金额'),
	'provider_id'              => Yii::t('app', '服务提供者ID（小帮ID）'),
	'appoint_provider_id'      => Yii::t('app', '指定服务提供者ID（小帮ID）接单'),
	'user_mobile'              => Yii::t('app', '用户电话'),
	'provider_mobile'          => Yii::t('app', '小帮电话'),
	'user_location'            => Yii::t('app', '用户下单坐标经纬度[经度,纬度]'),
	'user_address'             => Yii::t('app', '用户下单地址'),
	'provider_location'        => Yii::t('app', '小帮接单坐标经纬度[经度,纬度]'),
	'provider_address'         => Yii::t('app', '小帮接单地址'),
	'start_location'           => Yii::t('app', '下单起点坐标经纬度[经度,纬度]'),
	'start_address'            => Yii::t('app', '下单起点地址'),
	'end_location'             => Yii::t('app', '下单终点坐标经纬度[经度,纬度]'),
	'end_address'              => Yii::t('app', '下单终点地址'),
	'create_time'              => Yii::t('app', '创建时间'),
	'update_time'              => Yii::t('app', '更新时间'),
	'cancel_time'              => Yii::t('app', '取消时间'),
	'robbed_time'              => Yii::t('app', '抢单时间'),
	'payment_time'             => Yii::t('app', '支付时间'),
	'finish_time'              => Yii::t('app', '完成时间'),
	'request_cancel_id'        => Yii::t('app', '发起取消人'),
	'request_cancel_time'      => Yii::t('app', '发起取消时间'),
	'user_deleted'             => Yii::t('app', '用户删除：0.正常 1.删除'),
	'provider_deleted'         => Yii::t('app', '小帮删除：0.正常 1.删除'),
];

$order_errand = [
	'errand_id'         => Yii::t('app', 'Errand ID'),
	'order_id'          => Yii::t('app', '订单ID（取决于订单主表order_id）'),
	'errand_status'     => Yii::t('app', '快送状态：1.等待接单 2.小帮已接单 3.联系客服 4.正在服务 5.服务完成'),
	'order_distance'    => Yii::t('app', '订单起点与终点距离（单位：米）'),
	'starting_distance' => Yii::t('app', '小帮与订单起点距离（单位：米）'),
	'begin_time'        => Yii::t('app', '开始时间'),
	'begin_location'    => Yii::t('app', '开始坐标经纬度[经度,纬度]'),
	'begin_address'     => Yii::t('app', '开始地址'),
	'finish_time'       => Yii::t('app', '完成时间'),
	'finish_location'   => Yii::t('app', '完成坐标经纬度[经度,纬度]'),
	'finish_address'    => Yii::t('app', '完成地址'),
	'total_fee'         => Yii::t('app', '小费总额'),
	'first_fee'         => Yii::t('app', '首次小费'),
	'service_price'     => Yii::t('app', '服务单价'),
	'service_time'      => Yii::t('app', '服务时间'),
	'service_qty'       => Yii::t('app', '服务时长（单位：小时）'),
	'errand_type'       => Yii::t('app', '快送类型：1.帮我买 2.帮我送 3.帮我办'),
	'errand_content'    => Yii::t('app', '快送内容'),
	'mobile'            => Yii::t('app', '联系电话（收货人或者其他人）'),
	'maybe_time'        => Yii::t('app', '预约收货时间'),
	'actual_time'       => Yii::t('app', '实际收货时间'),
	'cancel_type'       => Yii::t('app', '取消类型'),
];
//帮我买表结构说明

$order = [
	'order_id'                 => Yii::t('app', 'Order ID'),
	'user_id'                  => Yii::t('app', '下单用户ID'),
	'order_no'                 => Yii::t('app', '订单号'),
	'cate_id'                  => Yii::t('app', '分类ID'),
	'region_id'                => Yii::t('app', '地区ID'),
	'order_type'               => Yii::t('app', '订单类型：1.摩的专车 2.小帮快送'),
	'order_from'               => Yii::t('app', '订单来源：1.android 2.IOS  3.微信'),
	'order_status'             => Yii::t('app', '订单状态：1.等待支付 2.等待网关确认支付 3.用户取消 4.小帮取消 5.进行中 6.已完成 7.已评价 8.客服处理 9.平台取消'),
	'payment_id'               => Yii::t('app', '支付方式：1.余额 2.银行卡 3.支付宝 4.微信'),
	'robbed'                   => Yii::t('app', '是否被抢：0.未抢 1已抢'),
	'payment_status'           => Yii::t('app', '支付状态：1.待支付 2.已支付 3.部分退款 4.全部退款 5.待退款'),
	'order_amount'             => Yii::t('app', '订单金额'),
	'amount_payable'           => Yii::t('app', '实付金额'),
	'discount'                 => Yii::t('app', '优惠金额=卡券金额+在线宝金额+其他优惠'),
	'provider_estimate_amount' => Yii::t('app', '小帮预计收到金额'),
	'provider_actual_amount'   => Yii::t('app', '小帮实际收到金额'),
	'card_id'                  => Yii::t('app', '用户卡券ID'),
	'online_money'             => Yii::t('app', '在线宝金额'),
	'provider_id'              => Yii::t('app', '服务提供者ID（小帮ID）'),
	'appoint_provider_id'      => Yii::t('app', '指定服务提供者ID（小帮ID）接单'),
	'user_mobile'              => Yii::t('app', '用户电话'),
	'provider_mobile'          => Yii::t('app', '小帮电话'),
	'user_location'            => Yii::t('app', '用户下单坐标经纬度[经度,纬度]'),
	'user_address'             => Yii::t('app', '用户下单地址'),
	'provider_location'        => Yii::t('app', '小帮接单坐标经纬度[经度,纬度]'),
	'provider_address'         => Yii::t('app', '小帮接单地址'),
	'start_location'           => Yii::t('app', '下单起点坐标经纬度[经度,纬度]'),
	'start_address'            => Yii::t('app', '下单起点地址'),
	'end_location'             => Yii::t('app', '下单终点坐标经纬度[经度,纬度]'),
	'end_address'              => Yii::t('app', '下单终点地址'),
	'create_time'              => Yii::t('app', '创建时间'),
	'update_time'              => Yii::t('app', '更新时间'),
	'cancel_time'              => Yii::t('app', '取消时间'),
	'robbed_time'              => Yii::t('app', '抢单时间'),
	'payment_time'             => Yii::t('app', '支付时间'),
	'finish_time'              => Yii::t('app', '完成时间'),
	'request_cancel_id'        => Yii::t('app', '发起取消人'),
	'request_cancel_time'      => Yii::t('app', '发起取消时间'),
	'user_deleted'             => Yii::t('app', '用户删除：0.正常 1.删除'),
	'provider_deleted'         => Yii::t('app', '小帮删除：0.正常 1.删除'),
];

$order_errand = [
	'errand_id'         => Yii::t('app', 'Errand ID'),
	'order_id'          => Yii::t('app', '订单ID（取决于订单主表order_id）'),
	'errand_status'     => Yii::t('app', '快送状态：1.等待接单 2.小帮已接单 3.联系客户 4.开始配送 5.配送到达 6.商品费用 '),
	'order_distance'    => Yii::t('app', '订单起点与终点距离（单位：米）'),
	'starting_distance' => Yii::t('app', '小帮与订单起点距离（单位：米）'),
	'begin_time'        => Yii::t('app', '开始时间'),
	'begin_location'    => Yii::t('app', '开始坐标经纬度[经度,纬度]'),
	'begin_address'     => Yii::t('app', '开始地址'),
	'finish_time'       => Yii::t('app', '完成时间'),
	'finish_location'   => Yii::t('app', '完成坐标经纬度[经度,纬度]'),
	'finish_address'    => Yii::t('app', '完成地址'),
	'total_fee'         => Yii::t('app', '小费总额'),
	'first_fee'         => Yii::t('app', '首次小费'),
	'service_price'     => Yii::t('app', '服务单价'),
	'service_time'      => Yii::t('app', '服务时间'),
	'service_qty'       => Yii::t('app', '服务时长（单位：小时）'),
	'errand_type'       => Yii::t('app', '快送类型：1.帮我买 2.帮我送 3.帮我办'),
	'errand_content'    => Yii::t('app', '快送内容'),
	'mobile'            => Yii::t('app', '联系电话（收货人或者其他人）'),
	'maybe_time'        => Yii::t('app', '预约收货时间'),
	'actual_time'       => Yii::t('app', '实际收货时间'),
	'cancel_type'       => Yii::t('app', '取消类型'),
];

//帮我送表结构说明



