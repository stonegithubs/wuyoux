<?php

namespace frontend\controllers;

use api\modules\api\blocks\PushBlock;
use common\components\Ref;
use common\helpers\orders\BizSendHelper;
use common\helpers\payment\WxpayHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\sms\SmsHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use yii\web\Controller;
use Yii;

/**
 * Andy的代码测试
 */
class AndyController extends Controller
{


	public function actionSend()
	{

		SmsHelper::demoSend('15914685847', 123422, 'SMS_60025252');
	}


	public function actionPush()
	{
		//用户推送

		PushHelper::userSendOneTransmission(1, 23, 23, 23);

		PushHelper::providerSendOneTransmission(1, 23, 23, 23);
	}

	public function actionPushUser()
	{
		$cid = Yii::$app->request->get('cid', 'f30b89335b703715ffa15cc111aeac53');

		$push_msg  = '调试有新的用户订单消息，请查看！';
		$push_info = [
			"glx"            => 1,//个推类型
			"to"             => "modi.order",
			"url"            => "modi.shop.order.push",
			"uid"            => 847983,
			'shops_id'       => "61381",    //商家ID
			"orderid"        => '152017100545611725',//$order['orderid'],
			"cate_id"        => '51',//$order['cate_id'],
			"city_id"        => '95',//$order['city_id'],
			"way"            => 2,//$order['way'],
			"content"        => '乘客出行',//$order['content'],
			"n_mobile"       => '15914685847',//$order['n_mobile'],
			"n_address"      => '广东药学院',//$order['n_address'],
			"n_city"         => '中山市',//$order['n_city'],
			"n_location"     => "[113.42078128290503,22.482106744904087]",//$order['n_location'],
			"int_shops"      => 0,//$order['int_shops'],
			"create_time"    => '12时05分01秒',//date("H时i分s秒", $order['create_time']),
			"catename"       => '摩的专车',// $catelist['name'],
			'money'          => '29.99',//$order['money'],        //订单价格
			'trip_range'     => '9.99',//$order['trip_range'], //开始地点与结束地点的距离
			'inform_content' => $push_msg,
			'n_end_address'  => '啊会汽车维修中心',
			'type'           => 1,
			'distance'       => '距您约114米',
			'shop_location'  => '234242',

		];
		echo "<pre>";
		$res = PushHelper::userSendOneTransmission($cid, $push_info, $push_msg);

		echo "返回结果" . date("Y-m-d H:i:s") . "<br>";
		print_r($res);

		echo "发送给个推的数据组 <br>";
		print_r($push_info);
		exit;
	}

	public function actionPushProvider()
	{
		$cid = Yii::$app->request->get('cid', '9809f952b0771cf2ab17a7daa492406f');

		$push_msg  = '有新的用户订单消息，请查看！';
		$push_info = [
			"glx"            => 1,//个推类型
			"to"             => "modi.order",
			"url"            => "modi.shop.order.push",
			"uid"            => 847983,
			'shops_id'       => "61381",    //商家ID
			"orderid"        => '152017100545611725',//$order['orderid'],
			"cate_id"        => '51',//$order['cate_id'],
			"city_id"        => '95',//$order['city_id'],
			"way"            => 2,//$order['way'],
			"content"        => '乘客出行',//$order['content'],
			"n_mobile"       => '15914685847',//$order['n_mobile'],
			"n_address"      => '广东药学院',//$order['n_address'],
			"n_city"         => '中山市',//$order['n_city'],
			"n_location"     => "[113.42078128290503,22.482106744904087]",//$order['n_location'],
			"int_shops"      => 0,//$order['int_shops'],
			"create_time"    => '12时05分01秒',//date("H时i分s秒", $order['create_time']),
			"catename"       => '摩的专车',// $catelist['name'],
			'money'          => '29.99',//$order['money'],        //订单价格
			'trip_range'     => '9.99',//$order['trip_range'], //开始地点与结束地点的距离
			'inform_content' => $push_msg,
			'n_end_address'  => '啊会汽车维修中心',
			'type'           => 1,
			'distance'       => '距您约114米',
			'shop_location'  => '234242',
			'push_role'      => 'provider',
			'push_user_id'   => '1721623',//'1706181',

		];
		echo "<pre>";
		$res = PushHelper::providerSendOneTransmission($cid, $push_info, $push_msg);

		echo "返回结果" . date("Y-m-d H:i:s") . "<br>";
		print_r($res);

		echo "发送给个推的数据组 <br>";
		print_r($push_info);
		exit;
	}


	public function actionCheckCode()
	{

		$res = SecurityHelper::checkCode('13631120093', 000000, 1);
		var_dump($res);
		exit;
	}

	public function actionB()
	{
		echo "<pre>";
		$res = WxBizSendHelper::getLastTmpOrder(123);
		var_dump($res);
		exit;
	}

	public function actionBizOrder()
	{
		$params['tmp_no']       = 151374955018;
		$params['provider_id']  = 61687;
		$params['mobile_group'] = ['1591468587'];

		return BizSendHelper::createOrder($params);
	}

	public function actionCal()
	{
		echo "<pre>";
		$params = [
			'user_id' => 848081,

		];

		$a         = BizSendHelper::getBatchPaymentDetail($params);
		$orderData = BizSendHelper::getPaymentCache($params);
		var_dump($orderData);
		exit;
	}

	public function actionCal2()
	{
		echo "<pre>";
		$params = [
			'user_id' => 848081,

		];

		if (!$orderData = BizSendHelper::getPaymentCache($params)) {

			echo "无数据";
			exit;
		}

		$res = BizSendHelper::getCouponMatchData($orderData, $params['user_id']);
		var_dump($res);
	}

	public function actionCal3()
	{
		echo "<pre>";
		$params = [
			'user_id'     => 848081,
			'select_card' =>
				[
					['parent_id' => 37698, 'num' => 3],//4
					['parent_id' => 37688, 'num' => 1],//8
				]

		];
//		$selectCard = [
//			['parent_id' => 37698, 'num' => 3],//4
//			['parent_id' => 37688, 'num' => 1],//8
//		];
		if (!$orderData = BizSendHelper::getPaymentCache($params)) {

			echo "无数据";
			exit;
		}

		$res = BizSendHelper::smartCouponCal($orderData, $params);
		var_dump($res);
	}

	public function actionAmapAround()
	{

		echo "<pre>";
		$center_location = '[110.942646,21.660813]';
		$filter          = ['cate_id:132'];
		$aa              = Yii::$app->amap->around($center_location, $filter);
//		var_dump($aa);
		exit;
	}

	public function actionAmapConvert()
	{
		$aa = Yii::$app->amap->convert('110.949193,21.666482');
		var_dump($aa);
	}

	public function actionBaiduPoi()
	{
		$data = [

			'shop_id'            => 123,
			'shops_name'         => "小帮中山",
			'shops_address'      => "小帮中山地址",
			'tags'               => '51,132',
			'shops_location_lat' => "22.52" . time(),     //纬度
			'shops_location_lng' => "113.35" . time(),  //经度
			'city_id'            => 1,                //当前城市
			'shop_login_status'  => 1,    //是否在线（1在线，0不在线）
			'cate_id'            => 2,                //分类ID
			'mobile'             => 15914685847,                    //手机
			'user_id'            => 3,                //用户ID
			'range'              => 500,         //商家接单范围（单位：米）
			'vip_status'         => 1,
			'job_time'           => 1,
		];

		$res = Yii::$app->lbs->poiCreate($data);
		var_dump($res);
		exit;
	}

	public function actionBaiduUp($poi)
	{
		$data = [

			'poi_id'             => $poi,
			'shop_id'            => 123,
			'shops_name'         => "小帮中山",
			'shops_address'      => "小帮中山地址",
			'tags'               => '51,132',
			'shops_location_lat' => "22.52" . time(),     //纬度
			'shops_location_lng' => "113.35" . time(),  //经度
			'city_id'            => 1,                //当前城市
			'shop_login_status'  => 1,    //是否在线（1在线，0不在线）
			'cate_id'            => 2,                //分类ID
			'mobile'             => 15914685847,                    //手机
			'user_id'            => 3,                //用户ID
			'range'              => 500,         //商家接单范围（单位：米）
			'vip_status'         => 1,
			'job_time'           => 1,
		];

		$res = Yii::$app->lbs->poiCreate($data);
		var_dump($res);
		exit;
	}

	public function actionBicycling()
	{
		$a = AMapHelper::bicycling("113.429962,22.505249", '113.382953,22.508304');

		var_dump($a);
		exit;
	}

	public function actionSms()
	{
//		$res = SmsHelper::send('15914685847', '12325', 'SMS_132386131');


		SmsHelper::sendReceiverSmsNotice("15914685847", '123432');

		exit;
		$params = [
			'tpl' => "SMS_132386131",
			//			'data' => ['code' => "12325", 'product' => '无忧帮帮']
		];
		$res    = Yii::$app->sms->send("15914685847", $params);

		var_dump($res);
		exit;
	}

	public function actionWxPay()
	{
		$params = [
			'transaction_no' => '123456',
			'body'           => 'test',
			'fee'            => 1,
		];
		$res    = WxpayHelper::userAppOrder($params);
		var_dump($res);
		exit;
	}

	public function actionRefund()
	{
		echo "<pre>";
		$res = WxpayHelper::APPRefundByTradeNo('4200000061201801271565872117', 'T2018012709210569329', 1, 1);
		var_dump($res);
		exit;
	}

	public function actionJsConfig()
	{

		$res = WxpayHelper::getJsConfig(['openLocation', 'getLocation']);
		var_dump($res);
		exit;
	}

	public function actionPrice()
	{
		echo "<pre>";


		$data = RegionHelper::getCityPrice("[116.42792,39.902896]", 85, 135);

		var_dump($data);
		exit;
	}

	public function actionCity()
	{
		echo "<pre>";
		$d = RegionHelper::getCityList();
		var_dump($d);

		exit;
	}

	public function actionMap()
	{
		$DATA = PushBlock::nearbyShop("[113.422961,22.515881]", '');
		var_dump($DATA);
		exit;
	}

	public function actionBiz()
	{
		$payAmount        = 10;
		$payOrderCount    = 2;
		$orderTotal       = 5;
		$orderAmountTotal = 20;
		$params           = [
			'buckle_num'    => $payOrderCount,        //订单数
			'buckle_amount' => $payAmount,    //扣款金额
			'owe_num'       => 111,//$orderTotal - $payOrderCount,            //欠款订单数
			'owe_amount'    => 11,// bcsub($orderAmountTotal, $payAmount),    //欠款金额
		];
		var_dump($params);

		SmsHelper::sendBizCutPaymentPart("15914685847", $params);    //全部扣除;
	}

	public function actionTT()
	{

//		ErrandHelper::platformCancel('18052920523861');
		$params['tmp_no'] = '2018052921033420021';
		BizSendHelper::platTmpOrderCancel($params);
	}

	public function actionAmapCreate()
	{

//		{
//			"_name": "张坤明",
//    "_location": "113.214,22.644651",
//    "coordtype": 3,
//    "_address": "新Teen地商城",
//    "provider_id": "1946",
//    "city_id": "95",
//    "cate_id": "51,132",
//    "mobile": "13435775822",
//    "user_id": "36374",
//    "range": 5000,
//    "vip_status": "1",
//    "job_time": "0",
//    "original_coord": "\"[113.214,22.644651]\""
//}
		$data['provider_id']   = 123;                //商家ID,
		$data['shops_name']    = '好的nq';
		$data['location']      = '113.214,22.644651';
		$data['shops_address'] = '中山市';        //地址,

		$data['city_id']           = '95';            //当前城市
		$data['shop_login_status'] = '1';    //是否在线（1在线，0不在线）
		$data['cate_id']           = '15';                //分类ID
		$data['mobile']            = '15914685841';                    //手机
		$data['user_id']           = '23';              //用户ID
		$data['range']             = 5;        //商家接单范围（单位：米）
		$data['vip_status']        = 1;
		$data['job_time']          = 0;
		$data['original_coord']    = '[113.214,22.644651]';
		echo "<pre>";
		$res = Yii::$app->amap->poiCreate($data);
		var_dump($res);

		echo "ok";
		exit;
	}

	public function actionAmapUpdate()
	{

		$data['provider_id']       = 123;                //商家ID,
		$data['shops_name']        = '商家名称9999';
		$data['location']          = '113.42953077856932,22.522007364359087';
		$data['shops_address']     = '中山市';        //地址,
		$data['tags']              = '摩的专车 小帮快送';                //标签
		$data['city_id']           = '95';            //当前城市
		$data['shop_login_status'] = '1';    //是否在线（1在线，0不在线）
		$data['cate_id']           = '15';                //分类ID
		$data['mobile']            = '15914685847';                    //手机
		$data['user_id']           = '23';              //用户ID
		$data['range']             = 5;        //商家接单范围（单位：米）
		$data['vip_status']        = 1;
		$data['job_time']          = 0;
		$data['poi_id']            = 12;
		$data['original_coord']    = "[113.42953077856932,22.522007364359087]";

		$a = Yii::$app->amap->poiUpdate($data);
		var_dump($a);
		echo "ok";
		exit;
	}


	public function actionAmapDelete()
	{


		Yii::$app->amap->poiDelete(11);
		echo "ok";
		exit;
	}

	public function actionAmapList()
	{
		echo "<pre>";
		$params = [
			'mobile' => '15914685847',
			'filter' => 'cate_id:132'
		];
		$data   = Yii::$app->amap->poiLists($params);

		var_dump($data);
		exit;
		if ($data['status'] == 1 && $data['info'] == 'OK') {

			var_dump($data['datas']);

			foreach ($data['datas'] as $item) {
				$resss = Yii::$app->amap->poiDelete($item['_id']);

				var_dump($resss);

			}
		}


		exit;

	}


	public function actionCo(){
		RegionHelper::isCityOpening('96',1);
		exit;
	}

	public function actionReo(){
		$a = AMapHelper::getRegeo("[106.56145,29.56391]");
		$b = RegionHelper::getAddressIdByLocation("[106.56145,29.56391]",2);

		echo "<pre>";
		var_dump($a);		var_dump($b);exit;
	}

	public function actionOpen(){

		echo "<pre>";
		$user_location ='[113.397967,22.512832]';
		$start_location = '[113.397967,22.512832]';

		$re = RegionHelper::checkCurrentRegionAndOpening($user_location,$start_location,135,96);
		var_dump($re);exit;
	}
}