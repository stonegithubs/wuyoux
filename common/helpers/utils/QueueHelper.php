<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/30
 */

namespace common\helpers\utils;


use common\helpers\HelperBase;
use common\helpers\orders\BizSendHelper;
use console\jobs\OrderJob;
use console\jobs\PaymentJob;
use console\jobs\PushJob;
use Yii;

class QueueHelper extends HelperBase
{
	public static function demo()
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\DemoJob([
			'function' => "test1",
			'params'   => ['order_id' => 123],

		]));

		return $jobID;
	}


	/**
	 * 小帮快送 推送订单
	 *
	 * @see  \console\jobs\OrderJob::errandSendOrder()
	 * @see  \console\jobs\OrderJob::autoCancelErrandOrder()
	 * @see  \common\helpers\utils\PushHelper::errandSendOrder() 二级方法
	 * @see  \console\jobs\OrderJob::errandSendOrderPlus()
	 * @see  \common\helpers\utils\PushHelper::errandSendOrderPlus() 二级方法
	 * @param      $order_id
	 * @param bool $retry
	 * @param int  $ttl
	 */
	public static function errandSendOrder($order_id, $retry = false, $ttl = 0)
	{

		if ($retry) {
			Yii::$app->queue->delay($ttl)->push(new \console\jobs\OrderJob([
				'function' => "errandSendOrder",
				'params'   => ['order_id' => $order_id],
			]));
		} else {

			//执行即时推送发单
			Yii::$app->queue->push(new \console\jobs\OrderJob([
				'function' => "errandSendOrder",
				'params'   => ['order_id' => $order_id],
			]));

			//执行定时取消订单
			$cancelTtl = YII_ENV_PROD ? 60 * 30 : 60 * 5;
			Yii::$app->queue->delay($cancelTtl)->push(new \console\jobs\OrderJob([
					'function' => "autoCancelErrandOrder",
					'params'   => ['order_id' => $order_id],
				]
			));
		}


		//执行即时延迟推送发单
		Yii::$app->queue->delay(30)->push(new \console\jobs\OrderJob([
			'function' => "sendErrandOrderPlus",
			'params'   => ['order_id' => $order_id],
		]));

	}

	/**
	 * 企业送 推送订单
	 * @see  \console\jobs\OrderJob::bizSendOrder()
	 * @see  \console\jobs\OrderJob::autoCancelBizTmpOrder()
	 * @see  \common\helpers\utils\PushHelper::bizSendOrder() 二级方法
	 * @see  \common\helpers\orders\BizSendHelper::autoCancelBizTmpOrder() 二级方法
	 *
	 */
	public static function bizSendOrder($batch_no, $retry = false, $ttl = 0)
	{
		if ($retry) {
			Yii::$app->queue->delay($ttl)->push(new \console\jobs\OrderJob([
				'function' => "bizSendOrder",
				'params'   => ['batch_no' => $batch_no],
			]));
		} else {

			//执行即时推送发单
			Yii::$app->queue->push(new \console\jobs\OrderJob([
				'function' => "bizSendOrder",
				'params'   => ['batch_no' => $batch_no],
			]));

			//执行定时取消订单
			$cancelTtl = YII_ENV_PROD ? 60 * 30 : 60 * 5;
			Yii::$app->queue->delay($cancelTtl)->push(new \console\jobs\OrderJob([
				'function' => "autoCancelBizTmpOrder",
				'params'   => ['batch_no' => $batch_no],
			]));
		}

		//执行即时延迟推送发单
		Yii::$app->queue->delay(30)->push(new \console\jobs\OrderJob([
			'function' => "bizSendOrderPlus",
			'params'   => ['batch_no' => $batch_no],
		]));
	}

	/**
	 * 推送给用户端
	 *
	 * @see \console\jobs\PushJob::toOneTransMissionForUser()
	 * @see \common\helpers\utils\PushHelper::userSendOneTransmission()    二级方法
	 *
	 * @param      $pushInfo
	 * @param      $userId
	 * @param null $tag
	 */
	public static function toOneTransmissionForUser($userId, $pushInfo, $tag = null)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "toOneTransMissionForUser",
				'params'   => ['push_info' => $pushInfo, 'user_id' => $userId, 'tag' => $tag],
			]
		));
	}

	/**
	 * 推送给小帮端
	 *
	 * @see \console\jobs\PushJob::toOneTransmissionForProvider()
	 * @see \common\helpers\utils\PushHelper::providerSendOneTransmission()    二级方法
	 *
	 * @param      $pushInfo
	 * @param      $provider_id
	 * @param null $tag
	 */
	public static function toOneTransmissionForProvider($provider_id, $pushInfo, $tag = null)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "toOneTransmissionForProvider",
				'params'   => ['push_info' => $pushInfo, 'provider_id' => $provider_id, 'tag' => $tag],
			]
		));
	}

	/**
	 * 平台快送订单取消通知
	 *
	 * @see  \console\jobs\OrderJob::errandPlatformCancelNotice()
	 * @see  \common\helpers\orders\ErrandHelper::pushPlatformCancelMsg 二级方法 //TODO 统一方法
	 */
	public static function errandPlatformCancelNotice($params, $role)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\OrderJob([
				'function' => "errandPlatformCancelNotice",
				'params'   => ['data' => $params, 'role' => $role],
			]
		));
	}


	/**
	 * 预退款处理
	 * @see \console\jobs\PaymentJob::preRefund()
	 * @see \common\helpers\orders\OrderHelper::preRefund() 二级方法
	 */
	public static function preRefund($data)
	{

		$jobID = Yii::$app->queue->push(new \console\jobs\PaymentJob([
				'function' => "preRefund",
				'params'   => ['data' => $data],
			]
		));
	}

	/**
	 *自动取消订单
	 *
	 * @see \console\jobs\OrderJob::autoCancelErrandOrder()
	 */
	public static function autoCancelErrandOrder($order_no, $delay = 1)
	{
		$jobID = Yii::$app->queue->delay($delay)->push(new \console\jobs\OrderJob([
				'function' => "autoCancelErrandOrder",
				'params'   => ['order_no' => $order_no],
			]
		));
	}

	/**
	 * 自动确认快送订单
	 *
	 * @see \console\jobs\OrderJob::autoConfirmErrandOrder()
	 */
	public static function autoConfirmErrandOrder($data, $delay = 1)
	{
		$jobID = Yii::$app->queue->delay($delay)->push(new \console\jobs\OrderJob([
				'function' => "autoConfirmErrandOrder",
				'params'   => ['data' => $data],
			]
		));
	}


	/**
	 * 新订单后台提醒
	 * @see \console\jobs\OrderJob::newOrderNotice()
	 */
	public static function newOrderNotice($order_no, $type = 'errand_order')
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\OrderJob([
				'function' => "newOrderNotice",
				'params'   => ['order_no' => $order_no, 'type' => $type],
			]
		));
	}


	/**
	 * 新订单后台提醒
	 * @see \console\jobs\OrderJob::newApplyProvider()
	 */
	public static function newApplyNotice($userId)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "newApplyProvider",
				'params'   => ['userId' => $userId],
			]
		));
	}

	/**
	 * 推送链接给用户
	 * @see \console\jobs\PushJob::pushLinkToUser()
	 * @param $params
	 */
	public static function pushLinkToUser($params)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "pushLinkToUser",
				'params'   => [
					'city_id'     => $params['city_id'],
					'link'        => $params['link'],
					'title'       => $params['title'],
					'content'     => $params['content'],
					'start_time'  => $params['start_time'],
					'end_time'    => $params['end_time'],
					'mobile'      => $params['mobile'],
					'msg_title'   => $params['msg_title'],
					'msg_content' => $params['msg_content'],
					'push_id'     => $params['push_id']
				],
			]
		));
	}


	/**
	 * 推送链接给小帮
	 * @see \console\jobs\PushJob::pushLinkToProvider()
	 * @param $title
	 * @param $content
	 * @param $province_id
	 * @param $city_id
	 * @param $area_id
	 * @param $link
	 * @param $start_time
	 * @param $end_time
	 * @param $mobile
	 * @param $msg_title
	 * @param $msg_content
	 * @param $push_id
	 */
	public static function pushLinkToProvider($params)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "pushLinkToProvider",
				'params'   => [
					'link'        => $params['link'],
					'title'       => $params['title'],
					'content'     => $params['content'],
					'province_id' => $params['province_id'],
					'city_id'     => $params['city_id'],
					'area_id'     => $params['area_id'],
					'start_time'  => $params['start_time'],
					'end_time'    => $params['end_time'],
					'mobile'      => $params['mobile'],
					'msg_title'   => $params['msg_title'],
					'msg_content' => $params['msg_content'],
					'push_id'     => $params['push_id']
				]
			]
		));
	}

	/**
	 * 推送活动给用户
	 *
	 * @see \console\jobs\PushJob::pushActivityToUser()
	 * @see \common\helpers\utils\PushHelper::userSendOneTransmission()    二级方法
	 *
	 * @param $params
	 */
	public static function pushActivityToUser($params)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "pushActivityToUser",
				'params'   => [
					'push_info'   => $params['info_content'],
					'title'       => $params['title'],
					'content'     => $params['content'],
					'city_id'     => $params['city_id'],
					'mobile'      => $params['mobile'],
					'start_time'  => $params['start_time'],
					'end_time'    => $params['end_time'],
					'msg_title'   => $params['msg_title'],
					'msg_content' => $params['msg_content'],
					'push_id'     => $params['push_id'],
				],
			]
		));
	}

	/**
	 * 分页推送消息
	 * @see PushJob::pushPageMessageToUser()
	 * @param array $list			用户列表
	 * @param string $pushInfo		推送内容
	 * @param string $title			推送标题
	 * @param string $content		推送内容
	 * @param string $msgTitle		信息中心标题
	 * @param string $msgContent	信息中心内容
	 * @param string $userType		推送用户(2.用户;3.小帮)
	 * @param string $pushType		推送类型(1.Android链接,2.APP内推送)
	 * @param string $pushId		推送ID
	 */
	public static function pushPageMessageToUser($list,$pushInfo,$title,$content,$msgTitle,$msgContent,$userType,$pushType,$pushId)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "pushPageMessageToUser",
				'params'   => [
					'list'   => $list,
					'push_info'       => $pushInfo,
					'title'     => $title,
					'content'      => $content,
					'msg_title'  => $msgTitle,
					'msg_content'    => $msgContent,
					'push_type'    => $pushType,
					'user_type'    => $userType,
					'push_id'    =>  $pushId,
				],
			]
		));
	}

	/**
	 * 推送活动给小帮
	 *
	 * @see \console\jobs\PushJob::pushActivityToProvider()
	 * @see \common\helpers\utils\PushHelper::providerSendOneTransmission()    二级方法
	 *
	 * @param $params
	 */
	public static function pushActivityToProvider($params)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
				'function' => "pushActivityToProvider",
				'params'   => [
					'push_info'   => $params['info_content'],
					'title'       => $params['title'],
					'content'     => $params['content'],
					'province_id' => $params['province_id'],
					'city_id'     => $params['city_id'],
					'area_id'     => $params['area_id'],
					'mobile'      => $params['mobile'],
					'start_time'  => $params['start_time'],
					'end_time'    => $params['end_time'],
					'msg_title'   => $params['msg_title'],
					'msg_content' => $params['msg_content'],
					'push_id'     => $params['push_id']
				],
			]
		));
	}

	/**
	 * 收货人短信推送
	 * @param $order_no
	 */
	public static function receiverSmsNotice($order_no)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\OrderJob([
				'function' => "smsToReceiver",
				'params'   => ['order_no' => $order_no],
			]
		));
	}

	/**
	 * 小帮出行推送订单 含自动取消和重复推送以及待接单提醒
	 *
	 * @see  \console\jobs\OrderJob::tripSendOrder()
	 * @see  \common\helpers\utils\PushHelper::tripSendOrder() 二级方法
	 *
	 * @param      $order_id
	 * @param bool $retry
	 * @param int  $ttl
	 */
	public static function tripOrder($order_id, $retry = false, $ttl = 0)
	{
		if ($retry) {    //重复推送
			$jobID = Yii::$app->queue->delay($ttl)->push(new \console\jobs\OrderJob([
				'function' => "tripSendOrder",
				'params'   => ['order_id' => $order_id],
			]));
		} else {

			$jobID = Yii::$app->queue->push(new \console\jobs\OrderJob([
				'function' => "tripSendOrder",
				'params'   => ['order_id' => $order_id],

			]));

			//5分钟后 提示加价
			$delay = YII_ENV_PROD ? 60 * 5 : 60 * 1;
			self::tripTipMessage($order_id, $delay, 5);

			//20分钟后 提示取消订单
			$delay = YII_ENV_PROD ? 60 * 20 : 60 * 2;
			self::tripTipMessage($order_id, $delay, 20);


			//30分钟后 取消订单
			$delay = YII_ENV_PROD ? 60 * 30 : 60 * 3;
			$jobID = Yii::$app->queue->delay($delay)->push(new \console\jobs\OrderJob([
				'function' => "autoCancelTripOrder",
				'params'   => ['order_id' => $order_id],
			]));
		}
	}

	/**
	 * 小帮出行提示信息推送
	 * @see      OrderJob::tripTipMessage()
	 * @param     $order_id
	 * @param int $ttl
	 * @param int $tip_times
	 */
	public static function tripTipMessage($order_id, $ttl = 10, $tip_times = 0)
	{

		$jobID = Yii::$app->queue->delay($ttl)->push(new \console\jobs\OrderJob([
			'function' => "tripTipMessage",
			'params'   => ['order_id' => $order_id, 'tip_times' => $tip_times],
		]));
	}

	/**
	 * 营销功能生成营销记录数据
	 * @see        PaymentJob::marketToSaveProfit();
	 * @param     $orderData
	 * @param     $agentId
	 * @param int $ttl
	 */
	public static function marketProfit($orderData, $agentId, $ttl = 30)
	{
		$jobID = Yii::$app->queue->delay($ttl)->push(new \console\jobs\PaymentJob([
			'function' => "marketToSaveProfit",
			'params'   => ['orderData' => $orderData, 'agentId' => $agentId],
		]));
	}

	/**
	 * 发送订单状态信息给用户
	 * @see        PushJob::sendWechatDispatchMessageToUser();
	 * @param      $orderData
	 * @param      $agentId
	 * @param bool $option
	 */
	public static function sendWechatDispatchMessageToUser($orderId, $cateId, $option = false)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
			'function' => "sendWechatDispatchMessageToUser",
			'params'   => ['orderId' => $orderId, 'cateId' => $cateId, 'option' => $option],
		]));
	}

	/**
	 * 发送订单状态信息给用户
	 * @see        PushJob::sendWechatDispatchMessageToProvider();
	 * @param     $orderData
	 * @param     $agentId
	 * @param int $ttl
	 */
	public static function sendWechatDispatchMessageToProvider($orderId, $cateId, $option = false)
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PushJob([
			'function' => "sendWechatDispatchMessageToProvider",
			'params'   => ['orderId' => $orderId, 'cateId' => $cateId, 'option' => $option],
		]));
	}


	/**
	 * 企业送自动扣款
	 *
	 * @see \console\jobs\PaymentJob::autoBizFreezeBalance()
	 */
	public static function autoBizFreezeBalance($data )
	{
		$jobID = Yii::$app->queue->push(new \console\jobs\PaymentJob([
				'function' => "autoBizFreezeBalance",
				'params'   => ['data' => $data],
			]
		));
	}
}