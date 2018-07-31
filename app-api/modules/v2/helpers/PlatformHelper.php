<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/7
 */

namespace api\modules\v2\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\OrderHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\RegionHelper;
use Yii;
use common\models\orders\Order;
use yii\data\Pagination;
use common\helpers\orders\ErrandHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\payment\TransactionHelper;

class PlatformHelper extends HelperBase
{
	//订单列表
	//$params['user_id', 'status', 'page','page_size']
	public static function orderList($params)
	{
		$result = [];
		$list   = [];
		$page   = 0;
		if ($params['page'] >= 1) {
			$page = $params['page'] - 1;  //YII分页的页码从0开始
		}
		$status                = in_array($params['status'], [1, 2, 3, 4, 5]) ? $params['status'] : 1;
		$where['user_id']      = $params['user_id'];
		$where['user_deleted'] = 0;  //0是正常，1是删除
		$where['cate_id']      = Ref::CATE_ID_FOR_ERRAND_SEND;  //帮我送

		switch ($status) {
			case 1:  //全部
				$where['order_status'] = [3, 4, 5, 6, 7, 9];
				break;
			case 2: //发布中
				$where['order_status'] = 5;
				$where['robbed']       = Ref::ORDER_ROB_NEW;
				break;
			case 3: //进行中
				$where['order_status'] = 5;
				$where['robbed']       = Ref::ORDER_ROBBED;
				break;
			case 4: //已完成
				$where['order_status'] = [6, 7];
				break;
			case 5: //取消
				$where['order_status'] = [3, 4, 9];
				break;
			default:
				break;
		}

		$subQuery   = Order::find()->select('order_id')->where($where);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $params['page_size']]);

		$select = ['order_no', 'start_address', 'end_address', 'order_amount', 'create_time as order_time', 'order_status as status', 'robbed'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();
		if ($data) {
			foreach ($data as $v) {
				$v['order_time'] = date('Y-m-d H:i:s', $v['order_time']);
				$v['status']     = self::getStatusText($v['status'], $v['robbed']);
				unset($v['robbed']);
				$list[] = $v;
			}
		}

		$result['pagination'] = [
			'page'       => $params['page'],
			'pageSize'   => $params['page_size'],
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];
		$result['list']       = $list;

		return $result;
	}


	//创建订单
	public static function createOrder($params)
	{
		$result     = false;
		$userInfo   = UserHelper::getUserInfo($params['user_id'], 'city_id');
		$userCityId = isset($userInfo['city_id']) ? $userInfo['city_id'] : 0;
		$priceInfo  = ErrandHelper::getRangePriceDataForAMap($params['start_location'], $params['end_location'], $userInfo, Ref::CATE_ID_FOR_BIZ_SEND); //注意TODO 这里获取的是企业送的价格体系
		$balanceRes = WalletHelper::checkUserMoney($params['user_id'], $priceInfo['price']);  //检查余额
		if (!$balanceRes) {
			return $result; //余额不足不能创建订单
		}
		$regionArr  = RegionHelper::getAddressIdByLocation($params['start_location'], $userCityId);
		$InsertData = [
			'base'     => [
				'cate_id'     => Ref::CATE_ID_FOR_ERRAND_SEND,
				'city_id'     => $regionArr['city_id'],
				'region_id'   => $regionArr['region_id'],
				'area_id'     => $regionArr['area_id'],
				'order_from'  => Ref::ORDER_FROM_OPEN_PLATFORM,
				'user_mobile' => $params['user_mobile'],        //发货人电话
				'order_type'  => Ref::ORDER_TYPE_ERRAND,
				'user_id'     => $params['user_id'],
			],
			'amount'   => [
				'order_amount' => $priceInfo['price'],
			],
			'location' => [
				'user_location'  => $params['user_location'],
				'user_address'   => $params['user_address'],
				'start_location' => $params['start_location'],
				'start_address'  => $params['start_address'],
				'end_location'   => $params['end_location'],
				'end_address'    => $params['end_address'],
			],
			'errand'   => [
				'service_price'  => $priceInfo['price'],
				'service_time'   => time(),
				'maybe_time'     => $params['receiver_time'],        //预约收货时间
				'errand_type'    => Ref::ERRAND_TYPE_SEND,
				'errand_content' => "大客户" . $params['content'],
				'mobile'         => (string)$params['receiver_mobile'],    //收货人电话
				'service_qty'    => 1,  //服务时长（单位：小时）
			]
		];

		$orderHelper = new OrderHelper();
		$orderHelper->setOrderParams($InsertData);
		if ($orderHelper->checkErrandParams()) {
			return $result;
		}
		$res = $orderHelper->save();

		if (is_array($res)) {
			$paymentId            = Ref::PAYMENT_TYPE_BALANCE; //余额支付
			$param2['payment_id'] = $paymentId;
			$param2['order_no']   = $res['order_no'];
			$orderRes             = $orderHelper->updatePrepayment($param2);
			$trade_no             = date("YmdHis");
			$isSuccess            = TransactionHelper::successOrderTrade($orderRes['transaction_no'], $trade_no, $paymentId, $orderRes['fee'], "余额支付");
			if ($isSuccess) {
				QueueHelper::errandSendOrder($orderRes['order_id']);
				$result['order_no']     = $orderRes['order_no'];  //成功则返回订单号
				$result['order_amount'] = $orderRes['fee'];
			}
		}

		return $result;
	}

	//获取订单信息
	public static function orderDetail($order_no)
	{
		$fields = ['order_no', 'start_address', 'end_address', 'order_amount', 'order_status as status', 'robbed'];
		$order  = Order::find()->select($fields)->where(['order_no' => $order_no, 'user_deleted' => 0])->asArray()->one();
		if ($order) {
			$order['status'] = self::getStatusText($order['status'], $order['robbed']);
			unset($order['robbed']);

			return $order;
		} else {
			return false;
		}

	}

	//获取价格
	public static function getRangePrice($params)
	{
		$userInfo  = UserHelper::getUserInfo($params['user_id'], 'city_id');
		$priceInfo = ErrandHelper::getRangePriceDataForAMap($params['start_location'], $params['end_location'], $userInfo, Ref::CATE_ID_FOR_ERRAND_SEND); //获取价格信息
		unset($priceInfo['distance_text']);

		return $priceInfo;
	}

	//取消订单
	public static function orderCancel($order_no)
	{
		$res               = ErrandHelper::platformCancel($order_no);
		$result['success'] = $res ? 1 : 0;  //0失败，1成功

		return $result;
	}

	//获取状态信息
	public static function getStatusText($status, $robbed)
	{
		switch ($status) {
			case 5:
				$res = $robbed ? '进行中' : '发布中';
				break;
			case 6:
			case 7:
				$res = '已完成';
				break;
			case 3:
			case 4:
			case 9:
				$res = '已取消';
				break;
			default:
				$res = OrderHelper::getOrderType($status);
				break;
		}

		return $res;
	}

}

