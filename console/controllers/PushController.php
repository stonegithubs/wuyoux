<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/21
 */


namespace console\controllers;
use common\helpers\utils\PushHelper;
use common\models\users\UserToken;
use yii\console\Controller;
use yii\db\Query;
use Yii;

class PushController extends Controller
{
	public function actionProvider($m=15914685847)
	{
		$sql = "select * from bb_51_shops where utel=".$m;
		$shops  = Yii::$app->db->createCommand($sql)->queryOne();

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
			"n_address"      => '无忧帮帮深圳',//$order['n_address'],
			"n_city"         => '中山市',//$order['n_city'],
			"n_location"     => "[113.42078128290503,22.482106744904087]",//$order['n_location'],
			"int_shops"      => 0,//$order['int_shops'],
			"create_time"    => '12时05分01秒',//date("H时i分s秒", $order['create_time']),
			"catename"       => '摩的专车',// $catelist['name'],
			'money'          => '29.99',//$order['money'],        //订单价格
			'trip_range'     => '9.99',//$order['trip_range'], //开始地点与结束地点的距离
			'inform_content' => "测试有新的用户订单消息，请查看！",
			'n_end_address'  => '无忧帮帮中山',
			'type'           => 1,
			'distance'       => '距您约114米',
			'shop_location'  => '234242',
			'push_role'      => 'provider',
			'push_user_id'   => '1721623',//'1706181',

		];

		if($shops){
			$user_id = $shops['uid'];
			$provider_id = $shops['id'];
			$push_info['push_user_id']     = $user_id;
			$push_info['provider_user_id'] = $user_id;
			$tag = "新APP";
			$model   = UserToken::findOne(['user_id' => $user_id, 'role' => 'provider']);
			$pushRes = false;
			if ($model) {
				$tag = "新APP".$model->app_version;
				$pushRes = PushHelper::providerSendOneTransmission($model->client_id, $push_info, $push_info['inform_content']);
			}

			$userData = (new Query())->select("gtui,tool,versionName")->from('bb_51_userdata')->where(['uid' => $user_id])->one();
			if (!$pushRes && count($userData) > 0) {

				$pushRes = PushHelper::oldUserSendOneTransmission($userData['gtui'], $push_info, $push_info['inform_content']);
				$tag     = "旧APP" . $userData['versionName'];
			}

			if (is_array($pushRes)) {

				var_dump($pushRes);
				echo $tag;
			} else {
				$p = [
					'provider_id' => $provider_id,
					'user_id'     => $user_id,
					'push_info'   => $push_info,
					'userData'    => $userData,
					'token_model' => $model
				];
				Yii::$app->debug->job_info("小帮推送失败", $p);
				var_dump($p);
			}
		}


	}
}