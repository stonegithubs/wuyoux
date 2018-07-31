<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/30
 */

namespace common\helpers\wechat;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\CateListHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderTrip;
use common\models\users\UserWechatRel;
use Yii;

class WechatHelper extends HelperBase
{
	const TEMPLE_ID_PROD_DISPATCH = "T_MExnVhe39xZ26atpEyqUCNJfo8A-EXWI89XRoIpUw";//订单派送正式通知模板
	const TEMPLE_ID_BETA_DISPATCH = "uhJ0MDzODx8ZcJMtnRvgtjumfOV5-1HhBa3wLFGnSQ4";//订单派送测试通知模板

	const TEMPLE_TYPE_DISPATCH = "dispatch";//订单派送类型

	public static function getMessageTemple($type)
	{
		$data = [
			'dispatch'=>self::TEMPLE_ID_BETA_DISPATCH
		];
		if(YII_ENV_PROD){
			$data['dispatch'] = self::TEMPLE_ID_PROD_DISPATCH;
		}
		return isset($data[$type])?$data[$type]:false;
	}


	/**
	 * 发送微信信息模板给用户
	 * @param      $templeId        模板ID
	 * @param      $userId          用户ID
	 * @param bool $url 			打开连接
	 * @param bool $data 			模板数据
	 * @return bool
	 * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
	 */
	public static function sendMessageTempleToUser($templeId, $userId, $url = false, $data = false)
	{
		$result = false;
		if ($templeId && $userId) {
			try{
				$app        = \Yii::$app->mp_wechat->getApp();
				$notice     = $app->template_message;
				$templeData = [
					'touser'      => $userId,
					'template_id' => $templeId,
					'url'         => $url,
					'data'        => $data
				];
				$message    = $notice->send($templeData);
				print_r($message);echo "\n";
				print_r($templeData); echo "\n";
				($message['errcode'] == 0)?$result = true:Yii::$app->debug->log_info("wechat_temple",$message);
				if ($message['errcode'] == 0) $result = true;
			}catch (\Exception $e){
				//缺少参数或信息错误，报错
				Yii::$app->debug->log_info("wechat_temple",$e);
			}
		}

		return $result;
	}

	/**
	 * 发送订单状态信息给用户
	 * @param      $orderId
	 * @param      $orderType
	 * @param bool $option
	 * @return bool
	 * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
	 */
	public static function sendWechatDispatchMessageToUser($orderId,$orderType,$option = false)
	{
		$result = false;
		$orderData = [];
		if($orderType == Ref::CATE_ID_FOR_BIZ_SEND_TMP){
			$order = BizTmpOrder::findOne(['tmp_id'=>$orderId]);
			$user = UserHelper::getUserInfo($order->user_id,['nickname','mobile']);
			$provider = ShopHelper::getShopInfoByProviderId($order->provider_id,['uname','uid']);
			if($order && $provider && $user){
				$orderData = [
					'cate_name'=>CateListHelper::getCategoryName($order->cate_id),
					'order_no'=>$order->tmp_no,
					'touser'=>$provider['uid'],
					'goods_name'=>$order->content,
					'provider_name'=>isset($user['nickname'])?$user['nickname']:"帮帮用户({$user['mobile']})",
					'provider_address'=>$order->user_address,
					'delivery_area'=>$order->delivery_area?$order->delivery_area:"其他",
				];
			}
		}elseif (in_array($orderType,[135,136,137,138])){
			$order = Order::findOne(['order_id'=>$orderId]);
			$errand = OrderErrand::findOne(['order_id'=>$orderId]);
			$user = UserHelper::getUserInfo($order->user_id,['nickname','mobile']);
			$provider = ShopHelper::getShopInfoByProviderId($order->provider_id,['uname','uid']);
			if($order && $provider && $errand && $user){
				$orderData = [
					'cate_name'=>CateListHelper::getCategoryName($order->cate_id),
					'touser'=>$order->user_id,
					'order_no'=>$order->order_no,
					'goods_name'=>$errand->errand_content,
					'provider_name'=>isset($user['nickname'])?$user['nickname']:"帮帮用户({$user['mobile']})",
					'provider_address'=>$order->start_address,
					'delivery_area'=>RegionHelper::getAddressNameById($order->area_id),
				];
			}
		}

		$templeUrl = isset($option['url'])?$option['url']:false;
		if($orderData){
			$userWechat = UserWechatRel::find()->where(['user_id'=>$orderData['touser']])->orderBy(["create_time"=>SORT_DESC])->one();
			$templeId = self::getMessageTemple(self::TEMPLE_TYPE_DISPATCH);
			if($userWechat && $templeId){
				$templeData=self::_setDispatchMessageData($orderData);

				$curl = self::sendMessageTempleToUser($templeId,$userWechat->openid,$templeUrl,$templeData);
				if($curl)	$result = true;
			}
		}
		return $result;
	}

	/**
	 * 发送订单状态信息给"小帮"
	 * @param      $orderId
	 * @param      $orderType
	 * @param bool $option
	 * @return bool
	 * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
	 */
	public static function sendWechatDispatchMessageToProvider($orderId,$orderType,$option = false)
	{
		$result = false;
		$orderData = [];
		if($orderType == Ref::CATE_ID_FOR_BIZ_SEND_TMP){
			$order = BizTmpOrder::findOne(['tmp_id'=>$orderId]);
			$user = UserHelper::getUserInfo($order->user_id,['nickname','mobile']);
			$provider = ShopHelper::getShopInfoByProviderId($order->provider_id,['uname','uid']);
			if($order && $provider && $user){
				$orderData = [
					'cate_name'=>CateListHelper::getCategoryName($order->cate_id),
					'order_no'=>$order->tmp_no,
					'touser'=>$provider['uid'],
					'goods_name'=>$order->content,
					'provider_name'=>isset($user['nickname'])?$user['nickname']:"帮帮用户({$user['mobile']})",
					'provider_address'=>$order->user_address,
					'delivery_area'=>$order->delivery_area?$order->delivery_area:"其他",
				];
			}
		}elseif (in_array($orderType,[135,136,137,138])){
			$order = Order::findOne(['order_id'=>$orderId]);
			$errand = OrderErrand::findOne(['order_id'=>$orderId]);
			$user = UserHelper::getUserInfo($order->user_id,['nickname','mobile']);
			$provider = ShopHelper::getShopInfoByProviderId($order->provider_id,['uname','uid']);

			if($order && $provider && $errand && $user){
				$orderData = [
					'cate_name'=>CateListHelper::getCategoryName($order->cate_id),
					'touser'=>$provider['uid'],
					'order_no'=>$order->order_no,
					'goods_name'=>$errand->errand_content,
					'provider_name'=>isset($user['nickname'])?$user['nickname']:"帮帮用户({$user['mobile']})",
					'provider_address'=>$order->start_address,
					'delivery_area'=>RegionHelper::getAddressNameById($order->area_id),
				];
			}
		}

		$templeUrl = isset($option['url'])?$option['url']:false;
		if($orderData){
			$userWechat = UserWechatRel::find()->where(['user_id'=>$orderData['touser']])->orderBy(["create_time"=>SORT_DESC])->one();
			$templeId = self::getMessageTemple(self::TEMPLE_TYPE_DISPATCH);
			if($userWechat && $templeId){
				$templeData=self::_setDispatchMessageData($orderData);

				$curl = self::sendMessageTempleToUser($templeId,$userWechat->openid,$templeUrl,$templeData);
				if($curl)	$result = true;
			}
		}
		return $result;
	}
	protected static function _setDispatchMessageData($params){
		$result = false;
		if($params){
			$result=[
				'first'=>[
					'value'=>"您好！系统为你分发了一份订单",
					"color"=>"#173177"
				],
				'keyword1'=>[
					'value'=>$params['cate_name'],//订单类型
					"color"=>"#173177"
				],
				'keyword2'=>[
					'value'=>$params['goods_name'],//配送商品
					"color"=>"#173177"
				],
				'keyword3'=>[
					'value'=>$params['provider_name'],//商家名称
					"color"=>"#173177"
				],
				'keyword4'=>[
					'value'=>$params['provider_address'],//商家地址
					"color"=>"#173177"
				],
				'keyword5'=>[
					'value'=>$params['delivery_area'],//配送区域
					"color"=>"#173177"
				],
				'remark'=>[
					'value'=>"无忧帮帮，祝您生活愉快",//结尾备注
					"color"=>"#173177"
				],
			];
		}
		return $result;
	}
}