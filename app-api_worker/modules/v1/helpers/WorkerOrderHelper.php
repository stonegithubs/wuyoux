<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api_worker\modules\v1\helpers;

use common\helpers\HelperBase;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\OrderHelper;
use common\helpers\payment\TransactionHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UtilsHelper;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderTrip;
use common\models\users\BizInfo;
use yii\data\Pagination;
use yii\db\Query;
use Yii;
use common\components\Ref;
use yii\helpers\ArrayHelper;

class WorkerOrderHelper extends HelperBase
{

	/**取商家旧订单表的数据
	 * 现在只有摩的的新订单
	 * @param $provider_id
	 * @param $status
	 * @param $page
	 * @param $page_size
	 * * @return array
	 */
	public static function oldOrderData($provider_id, $status, $page, $page_size)
	{
		$list                 = [];
		$result               = [];
		$where['shops_id']    = $provider_id;
		$where['is_bogus']    = 0;    //0为真实订单
		$where['order_style'] = 1;    //
		switch ($status) {
			case 1://进行中
				//status 订单状态 0发报中1确认2已取消 3已到达4已支付 5已评价 6待支付
				//order_style 订单类型：1.交通出行;2.配送;3.跑腿
				//trip_status 出行状态：1.抢单成功；2.已接乘客；3.安全到达
				//errand_status 跑腿状态：1等待接单，2跑男已接单，3正在配送，4已配送，5已取消
				$where['status'] = 1;
				break;
			case 2://已完成
				$where['status'] = [1, 3, 4, 5];

				break;
			case 3://已取消
				$where['status'] = 2;
				break;
			default:
				$where['status'] = [0, 1, 2, 4, 5];
				break;
		}

		$subQuery = (new Query())->select("id")->from("bb_51_orders")->where($where)->andWhere('s_sure != 6');

		if ($status == 1) {
			$subQuery->andFilterCompare('trip_status', 3, '<>');
		}

		if ($status == 2) {
			$subQuery->andWhere("trip_status=3 or errand_status =4 ");
		}

//		echo  $subQuery->createCommand()->getRawSql();	//打印SQL

		$count = $subQuery->count();

		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $page_size;

		$subQuery->offset($pagination->offset)->limit($pagination->limit)->orderBy(['create_time' => SORT_DESC]);
		$select = ['orderid', 'create_time', 'n_address', 'n_end_address', 'cate_id', 'errand_type', 'o.id', 'maybe_time', 'trip_status', 'money', 'status', 'n_mobile', "city_id", "area_id"];
		$data   = (new Query())->from(['o' => 'bb_51_orders', 't2' => $subQuery])->select($select)->where('o.id=t2.id')->orderBy(['o.create_time' => SORT_DESC])->all();

		foreach ($data as $k => $v) {
			$list[$k]['order_id']      = $v['id'];
			$list[$k]['order_no']      = $v['orderid'];
			$list[$k]['city_id']       = $v['city_id'];
			$list[$k]['area_id']       = $v['area_id'];
			$list[$k]['create_time']   = $v['create_time'];
			$list[$k]['start_address'] = $v['n_address'];
			$list[$k]['end_address']   = $v['n_end_address'];
			//$list[$k]['cate_id']        = $v['cate_id'];
			$list[$k]['second_cate_id'] = $v['errand_type'];
			$list[$k]['n_or_o']         = 'old';
			$list[$k]['maybe_time']     = $v['maybe_time'];
			$list[$k]['order_amount']   = $v['money'];  //订单金额
			$list[$k]['payment_status'] = (($v['status'] == 4) || ($v['status'] == 5)) ? Ref::PAY_STATUS_COMPLETE : Ref::PAY_STATUS_WAIT;  //支付状态
			$list[$k]['user_mobile']    = $v['n_mobile'];  //下单人电话
			$list[$k]['order_status']   = 5;    //旧订单不处理这个 @2018-5-28
			//特殊字段
			$list[$k]['order_content'] = $v['n_address'] . '-' . $v['n_end_address'];  //起点到终点
			$list[$k]['trip_status']   = $v['trip_status']; //出行状态：1.抢单成功；2.已接乘客；3.安全到达
			//如果是132的要改为帮我买，帮我送的分类ID
			if ($v['cate_id'] == Ref::CATE_ID_FOR_ERRAND) {
				switch ($v['errand_type']) {
					case Ref::ERRAND_TYPE_BUY :
						$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_BUY;  //帮我买
						break;
					case Ref::ERRAND_TYPE_SEND :
						$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_SEND; //帮我送
						break;
					default :
						break;
				}
			}
			$list[$k]['cate_id'] = $v['cate_id'];  //统一赋值
		}
		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}

	/**
	 * 取商家新订单表的数据
	 * 帮我买，帮我送，帮我办
	 * @param $provider_id
	 * @param $status
	 * @param $page
	 * @param $page_size
	 * * @return array
	 */
	public static function newOrderData($provider_id, $status, $page, $page_size)
	{
		$list                      = [];
		$result                    = [];
		$where['provider_id']      = $provider_id;
		$where['provider_deleted'] = 0;  //0正常，1删除
		//帮我买，帮我送，帮我办的ID
		$where['cate_id'] = [Ref::CATE_ID_FOR_ERRAND, Ref::CATE_ID_FOR_ERRAND_BUY, Ref::CATE_ID_FOR_ERRAND_SEND, Ref::CATE_ID_FOR_ERRAND_DO];

		switch ($status) {
			case 1://进行中 (订单状态为5 快送状态 不为5 )
				$where['order_status'] = 5;
				break;
			case 2://已经完成
				$where['order_status'] = [5, 6, 7];
				break;
			case 3://取消
				$where['order_status'] = [3, 4, 9];
				break;
			default:
				$where['order_status'] = [3, 4, 5, 6, 7, 9];
				break;
		}

		$subQuery = Order::find()->select(Order::tableName() . '.order_id')
			->leftJoin(OrderErrand::tableName(), Order::tableName() . '.order_id=' . OrderErrand::tableName() . ".order_id")->where($where);

		if ($status == 1) {    //进行中
			$subQuery->andFilterCompare('wy_order_errand.errand_status', 5, '<>');
		}

		if ($status == 2) {    //已经完成
			$subQuery->andFilterCompare('wy_order_errand.errand_status', 5);
		}


		$count = $subQuery->count();
//		echo $subQuery->createCommand()->getRawSql();//打印SQL

		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $page_size;
		$select               = ['order_no', 'create_time', 'start_address', 'end_address', 'cate_id', 'o.order_id', 'payment_status', 'order_amount', 'user_mobile'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();

		foreach ($data as $k => $v) {
			$orderErrand = OrderErrand::findOne(['order_id' => $v['order_id']]);
			if (!$orderErrand) {
				continue;
			}
			$list[$k]['order_id']       = $v['order_id'];
			$list[$k]['order_no']       = $v['order_no'];
			$list[$k]['create_time']    = $v['create_time'];
			$list[$k]['start_address']  = $v['start_address'];
			$list[$k]['end_address']    = $v['end_address'];
			$list[$k]['second_cate_id'] = $orderErrand->errand_type;
			$list[$k]['n_or_o']         = 'new';
			$list[$k]['maybe_time']     = $orderErrand->maybe_time;
			$list[$k]['payment_status'] = $v['payment_status'];  //支付状态
			$list[$k]['order_amount']   = $v['order_amount'];  //订单金额
			$list[$k]['user_mobile']    = $v['user_mobile'];  //下单人电话

			//特殊字段
			$list[$k]['order_content'] = $orderErrand->errand_content;  //快送内容
			//如果是132的要改为帮我买，帮我送，帮我办的分类ID
			if ($v['cate_id'] == Ref::CATE_ID_FOR_ERRAND) {
				switch ($orderErrand->errand_type) {
					case Ref::ERRAND_TYPE_BUY :
						$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_BUY;  //帮我买
						break;
					case Ref::ERRAND_TYPE_SEND :
						$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_SEND; //帮我送
						break;
					case Ref::ERRAND_TYPE_DO :
						$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_DO;  //帮我办
						break;
					default :
						break;
				}
			}
			$list[$k]['cate_id'] = $v['cate_id'];  //统一赋值
		}
		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}

	//企业送订单
	public static function bizOrderData($provider_id, $status, $page, $page_size)
	{
		$list                      = [];
		$result                    = [];
		$where['provider_id']      = $provider_id;
		$where['provider_deleted'] = 0;  //0正常，1删除
		$where['robbed']           = 1;
		$where['cate_id']          = Ref::CATE_ID_FOR_BIZ_SEND;
		if (isset($status)) {
			switch ($status) {
				case 1://进行中
					$where['order_status'] = Ref::ORDER_STATUS_DEFAULT;
					break;
				case 2://已经完成
					$where['order_status'] = [1, 5, 6, 7];
					break;
				case 3://取消
					$where['order_status'] = [3, 4, 9];
					break;
				default:
					$where['order_status'] = [3, 4, 5, 6, 7, 9];
					break;
			}
		}

		$subQuery = Order::find()->select('order_id')->where($where);

		$count = $subQuery->count();

		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $page_size;
		$select               = ['order_no', 'create_time', 'start_address', 'end_address', 'cate_id', 'o.order_id', 'order_status', 'order_amount', 'payment_status', 'user_mobile'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);

		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();

		foreach ($data as $k => $v) {
			$orderErrand = OrderErrand::findOne(['order_id' => $v['order_id']]);
			if (!$orderErrand) {
				continue;
			}
			$list[$k]['order_id']       = $v['order_id'];
			$list[$k]['order_no']       = $v['order_no'];
			$list[$k]['create_time']    = $v['create_time'];
			$list[$k]['start_address']  = $v['start_address'];
			$list[$k]['end_address']    = $v['end_address'];
			$list[$k]['cate_id']        = $v['cate_id'];
			$list[$k]['second_cate_id'] = $orderErrand->errand_type;
			$list[$k]['n_or_o']         = 'new';
			$list[$k]['maybe_time']     = $orderErrand->maybe_time;
			$list[$k]['payment_status'] = $v['payment_status'];  //支付状态
			$list[$k]['order_amount']   = $v['order_amount'];  //订单金额
			$list[$k]['user_mobile']    = $v['user_mobile'];  //下单人电话

			//特殊字段
			$list[$k]['receiver_mobile'] = $orderErrand->mobile;  //收货人电话
			$list[$k]['order_status']    = $v['order_status'];  //订单状态
			$list[$k]['order_content']   = $orderErrand->errand_content;  //快送内容

		}
		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}


	//出行订单
	public static function tripOrderData($provider_id, $status, $page, $page_size)
	{
		$list                      = [];
		$result                    = [];
		$where['provider_id']      = $provider_id;
		$where['provider_deleted'] = 0;  //0正常，1删除
		$where['cate_id']          = Ref::CATE_ID_FOR_MOTOR;

		switch ($status) {
			case 1://进行中
				$where['order_status'] = 0;
				break;
			case 2://已经完成
				$where['order_status'] = [1, 6, 7];
				break;
			case 3://取消
				$where['order_status'] = [3, 4, 9];
				break;
			default:
				break;
		}

		$subQuery   = Order::find()->select('order_id')->where($where);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
//		echo $subQuery->createCommand()->getRawSql();//打印SQL

		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $page_size]);

		$select = ['order_no', 'create_time', 'start_address', 'end_address', 'cate_id', 'o.order_id', 'payment_status', 'order_amount', 'user_mobile'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();

		foreach ($data as $k => $v) {
			$trip = OrderTrip::find()->select(['trip_type', 'trip_status'])->where(['order_id' => $v['order_id']])->one();
			if (!$trip) {
				continue;
			}
			$list[$k]['order_id']       = $v['order_id'];
			$list[$k]['order_no']       = $v['order_no'];
			$list[$k]['create_time']    = $v['create_time'];
			$list[$k]['start_address']  = $v['start_address'];
			$list[$k]['end_address']    = $v['end_address'];
			$list[$k]['n_or_o']         = 'new';
			$list[$k]['payment_status'] = $v['payment_status'];  //支付状态
			$list[$k]['order_amount']   = $v['order_amount'];  //订单金额
			$list[$k]['user_mobile']    = $v['user_mobile'];  //下单人电话
			$list[$k]['cate_id']        = $v['cate_id'];  //统一赋值
			$list[$k]['second_cate_id'] = $trip['trip_type'];
			$list[$k]['trip_status']    = $trip['trip_status'];  //出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达

		}
		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}

	/**
	 * 企业送临时订单列表
	 * @param $provider_id
	 * @param $status
	 * @param $page
	 * @param $page_size
	 * @return array
	 */
	public static function bizTmpOrderList($provider_id, $status, $page, $page_size)
	{
		$list   = [];
		$result = [];
		//共同条件
		$errandWhere['provider_id'] = $tripWhere['provider_id'] = $bizWhere['provider_id'] = $provider_id;
		//差异条件
		$errandWhere['cate_id']    = Ref::CATE_ID_FOR_BIZ_SEND; //企业送
		$errandWhere['tmp_status'] = Ref::BIZ_TMP_STATUS_PICKED; //订单已抢
		$errandWhere['robbed']     = 1; //订单已抢

		$subQuery   = BizTmpOrder::find()->select('order_id')->where($errandWhere);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $page_size]);
		$select     = ['order_no' => "tmp_no", "delivery_area", "receiver_mobile" => "provider_mobile", "order_content" => "content", 'create_time', 'start_address', 'cate_id', 'order_id' => "tmp_id", 'user_mobile', "city_id", "area_id", 'order_status' => "tmp_status"];
		$data       = BizTmpOrder::find()->select($select)->where($errandWhere)->limit($pagination->limit)->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->asArray()->all();

		foreach ($data as $key => $value) {
			$list[$key]                   = $value;
			$list[$key]['delivery_area']  = $value['delivery_area'] ? $value['delivery_area'] : "无";
			$list[$key]['n_or_o']         = 'new';
			$list[$key]['payment_status'] = 0;//匹配其他订单字段
			$list[$key]['cate_id']        = Ref::CATE_ID_FOR_BIZ_SEND_TMP;//重新定义类型
			$list[$key]['second_cate_id'] = false;//匹配其他订单字段
			$list[$key]['order_amount']   = 0;//匹配其他订单字段
		}

		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}

	//新表的订单
	//包括小帮快送，企业送，小帮出行
	public static function newOrderList($provider_id, $status, $page, $page_size)
	{
		//$errandWhere是快送类的查询条件
		//$tripWhere  是出行类的查询条件
		//$bizWhere   是企业送的查询条件
		$list   = [];
		$result = [];
		//共同条件
		$errandWhere['provider_id']      = $tripWhere['provider_id'] = $bizWhere['provider_id'] = $provider_id;
		$errandWhere['provider_deleted'] = $tripWhere['provider_deleted'] = $bizWhere['provider_deleted'] = 0; //0是正常，1是删除
		//差异条件
		$errandWhere['cate_id'] = [Ref::CATE_ID_FOR_ERRAND, Ref::CATE_ID_FOR_ERRAND_BUY, Ref::CATE_ID_FOR_ERRAND_SEND, Ref::CATE_ID_FOR_ERRAND_DO]; //快送
		$errandStatus           = [];
		$tripWhere['cate_id']   = Ref::CATE_ID_FOR_MOTOR;  //出行
		$bizWhere['cate_id']    = Ref::CATE_ID_FOR_BIZ_SEND;  //企业送
		$bizWhere['robbed']     = 1;

		switch ($status) {
			case 1://进行中
				$errandWhere['order_status'] = 5;  //快送
				$errandStatus                = ['<>', 'errand_status', 5]; //快送状态：1.等待接单 2.小帮已接单 3.联系客服 4.正在服务 5.服务完成

				$tripWhere['order_status'] = 0;  //出行

				$bizWhere['order_status'] = 0;  //企业送
				break;
			case 2://已经完成
				$errandWhere['order_status'] = [5, 6, 7];  //快送
				$errandStatus                = ['errand_status' => 5];

				$tripWhere['order_status'] = [1, 6, 7];  //出行

				$bizWhere['order_status'] = [1, 5, 6, 7];  //企业送
				break;
			case 3://取消
				$tripWhere['order_status'] = $errandWhere['order_status'] = $bizWhere['order_status'] = [3, 4, 9];
				break;
			default:
				break;
		}

		$subQuery   = Order::find()->select('order_id')->where($errandWhere)->orWhere($tripWhere)->orWhere($bizWhere);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $page_size]);
		$select     = ['order_no', 'create_time', 'start_address', 'end_address', 'cate_id', 'o.order_id', 'payment_status', 'order_amount', 'user_mobile', 'order_status', "city_id", "area_id"];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();
		foreach ($data as $k => $v) {
			//快送
			if (in_array($v['cate_id'], $errandWhere['cate_id'])) {
				if ($status == 1 || $status == 2) {
					//进行中或者已完成
					$orderErrand = OrderErrand::find()->select(['errand_type', 'errand_content', 'maybe_time'])->where(['order_id' => $v['order_id']])->andWhere($errandStatus)->one();
				} else {
					$orderErrand = OrderErrand::find()->select(['errand_type', 'errand_content', 'maybe_time'])->where(['order_id' => $v['order_id']])->one();
				}
				if (!$orderErrand) {
					continue;
				}
				$list[$k]['order_content']  = $orderErrand['errand_content'];
				$list[$k]['second_cate_id'] = $orderErrand['errand_type'];
				$list[$k]['maybe_time']     = $orderErrand['maybe_time'];

				if ($v['cate_id'] == Ref::CATE_ID_FOR_ERRAND) {
					switch ($orderErrand['errand_type']) {
						case Ref::ERRAND_TYPE_BUY :
							$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_BUY;  //帮我买
							break;
						case Ref::ERRAND_TYPE_SEND :
							$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_SEND; //帮我送
							break;
						case Ref::ERRAND_TYPE_DO :
							$v['cate_id'] = Ref::CATE_ID_FOR_ERRAND_DO;  //帮我办
							break;
						default :
							break;
					}
				}

			}
			//出行
			if ($v['cate_id'] == Ref::CATE_ID_FOR_MOTOR) {
				$trip                       = OrderTrip::find()->select(['trip_type', 'trip_status'])->where(['order_id' => $v['order_id']])->one();
				$list[$k]['second_cate_id'] = $trip['trip_type'];
				$list[$k]['trip_status']    = $trip['trip_status'];  //出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
			}
			//企业送
			if ($v['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND) {
				$orderErrand                 = OrderErrand::find()->select(['errand_type', 'errand_content', 'maybe_time', 'mobile'])->where(['order_id' => $v['order_id']])->one();
				$list[$k]['order_content']   = $orderErrand['errand_content'];
				$list[$k]['second_cate_id']  = $orderErrand['errand_type'];
				$list[$k]['maybe_time']      = $orderErrand['maybe_time'];
				$list[$k]['receiver_mobile'] = $orderErrand['mobile'];  //收货人电话
			}

			$list[$k]['order_id']       = $v['order_id'];
			$list[$k]['order_no']       = $v['order_no'];
			$list[$k]['city_id']        = $v['city_id'];
			$list[$k]['area_id']        = $v['area_id'];
			$list[$k]['create_time']    = $v['create_time'];
			$list[$k]['start_address']  = $v['start_address'];
			$list[$k]['end_address']    = $v['end_address'];
			$list[$k]['cate_id']        = $v['cate_id'];
			$list[$k]['payment_status'] = $v['payment_status'];  //支付状态
			$list[$k]['order_amount']   = $v['order_amount'];  //订单金额
			$list[$k]['user_mobile']    = $v['user_mobile'];  //下单人电话
			$list[$k]['n_or_o']         = 'new';
			$list[$k]['order_status']   = $v['order_status'];
		}

		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}


	//小帮订单列表
	//1.0版本
	public static function getProviderList($params)
	{

		$result       = [];
		$current_page = !empty($params['page']) ? $params['page'] : 1;
		$page         = 0;
		if ($current_page > 1) {
			$page = $current_page - 1;
		}
		$page_size = !empty($params['page_size']) ? $params['page_size'] : 20;
//		$per_page_size = ceil($page_size / 2);

		$old_order = WorkerOrderHelper::oldOrderData($params['user_id'], $params['status'], $page, $page_size);
		$new_order = WorkerOrderHelper::newOrderData($params['user_id'], $params['status'], $page, $page_size); //新表的快送订单
		$biz_order = WorkerOrderHelper::bizOrderData($params['user_id'], $params['status'], $page, $page_size);
		//$trip_order = WorkerOrderHelper::tripOrderData($params['user_id'], $params['status'], $page, $page_size); //新表的出行订单

		$page1 = $old_order['pagination']; //旧表的分页信息
		$page2 = $new_order['pagination']; //新表的快送的分页信息
		$page3 = $biz_order['pagination'];  //企业送的分页信息

		$page_count  = max($page1['pageCount'], $page2['pageCount'], $page3['pageCount']);  //比较取大的页数
		$total_count = max($page1['totalCount'], $page2['totalCount'], $page3['totalCount']); //总记录数

		$all_order = array_merge($old_order['list'], $new_order['list'], $biz_order['list']);
		unset($old_order);
		unset($new_order);
		unset($biz_order);
		ArrayHelper::multisort($all_order, 'create_time', SORT_DESC);  //排序
		$list = [];
		foreach ($all_order as $key => $value) {
			$category                     = CateListHelper::getCateListName($value['cate_id'], $value['second_cate_id']);
			$list[$key]['order_id']       = $value['order_id'];
			$list[$key]['order_no']       = $value['order_no'];
			$list[$key]['create_time']    = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['start_address']  = rtrim($value['start_address'], ',');
			$list[$key]['end_address']    = rtrim($value['end_address'], ',');
			$list[$key]['cate_name']      = $category['cate_name'];
			$list[$key]['n_or_o']         = $value['n_or_o'];//新还是旧的订单
			$list[$key]['type']           = $value['cate_id'];//出行，帮我办，帮我送，帮我买,企业送的分类ID
			$list[$key]['order_amount']   = $value['order_amount'];  //订单金额
			$list[$key]['payment_status'] = $value['payment_status'];  //支付状态
			$list[$key]['user_mobile']    = $value['user_mobile'];  //支付状态

			//特殊字段
			$list[$key]['data']['log_time']   = date("Y-m-d H:i:s", $value['create_time']); //默认值
			$list[$key]['data']['maybe_time'] = empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']);
			$list[$key]['data']['content']    = isset($value['order_content']) ? $value['order_content'] : null;

			if ($value['cate_id'] == Ref::CATE_ID_FOR_MOTOR) {
				//旧表订单摩的状态：1.抢单成功；2.已接乘客；3.安全到达
				//新表订单出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
				$list[$key]['data']['trip_status'] = $value['trip_status'];
			}

			if ($value['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND) {
				$list[$key]['data']['receiver_mobile'] = $value['receiver_mobile'];  //企业送的收货人电话
			}
		}
		$result['pagination'] = [
			'page'       => $current_page,  //当前页
			'pageSize'   => $page_size,  //每页大小
			'pageCount'  => $page_count,  //总页数
			'totalCount' => $total_count, //总记录数
		];
		$result['list']       = $list;

		return $result;
	}

	//小帮订单类别
	//1.1版本
	public static function providerListV11($params)
	{

		$result       = [];
		$current_page = !empty($params['page']) ? $params['page'] : 1;
		$page         = 0;
		if ($current_page > 1) {
			$page = $current_page - 1;
		}
		$page_size = !empty($params['page_size']) ? $params['page_size'] : 20;
//		$per_page_size = ceil($page_size / 2);

		$old_order = WorkerOrderHelper::oldOrderData($params['user_id'], $params['status'], $page, $page_size);
		$new_order = WorkerOrderHelper::newOrderList($params['user_id'], $params['status'], $page, $page_size);

		$page1 = $old_order['pagination']; //旧表的分页信息
		$page2 = $new_order['pagination']; //新表的分页信息

		$page_count  = max($page1['pageCount'], $page2['pageCount']);  //比较取大的页数
		$total_count = max($page1['totalCount'], $page2['totalCount']); //总记录数

		$all_order = array_merge($old_order['list'], $new_order['list']);
		unset($old_order);
		unset($new_order);
		if ($params['status'] == 2) {
			//已完成的订单，未支付的要优先
			ArrayHelper::multisort($all_order, ['payment_status', 'create_time'], [SORT_ASC, SORT_DESC]);  //排序
		} else {
			ArrayHelper::multisort($all_order, 'create_time', SORT_DESC);  //排序
		}
		$list = [];
		foreach ($all_order as $key => $value) {
			$category                     = CateListHelper::getCateListName($value['cate_id'], $value['second_cate_id']);
			$list[$key]['order_id']       = $value['order_id'];
			$list[$key]['order_no']       = $value['order_no'];
			$list[$key]['create_time']    = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['start_address']  = rtrim($value['start_address'], ',');
			$list[$key]['end_address']    = rtrim($value['end_address'], ',');
			$list[$key]['cate_name']      = $category['cate_name'];
			$list[$key]['n_or_o']         = $value['n_or_o'];//新还是旧的订单
			$list[$key]['type']           = $value['cate_id'];//出行，帮我办，帮我送，帮我买,企业送的分类ID
			$list[$key]['order_amount']   = $value['order_amount'];  //订单金额
			$list[$key]['payment_status'] = $value['payment_status'];  //支付状态
			$list[$key]['user_mobile']    = $value['user_mobile'];  //用户手机号码

			$list[$key]['payment_status_text'] = self::getPaymentStatusText($value['payment_status'], $value['cate_id'], $value['order_status']);  //支付状态


			//特殊字段
			$list[$key]['data']['log_time']   = date("Y-m-d H:i:s", $value['create_time']); //默认值
			$list[$key]['data']['maybe_time'] = empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']);
			$list[$key]['data']['content']    = isset($value['order_content']) ? $value['order_content'] : null;

			if ($value['cate_id'] == Ref::CATE_ID_FOR_MOTOR) {
				//旧表订单摩的状态：1.抢单成功；2.已接乘客；3.安全到达
				//新表订单出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
				$list[$key]['data']['trip_status'] = $value['trip_status'];
			}

			if ($value['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND) {
				$list[$key]['data']['receiver_mobile'] = $value['receiver_mobile'];  //企业送的收货人电话
			}
		}
		$result['pagination'] = [
			'page'       => $current_page,  //当前页
			'pageSize'   => $page_size,  //每页大小
			'pageCount'  => $page_count,  //总页数
			'totalCount' => $total_count, //总记录数
		];
		$result['list']       = $list;

		return $result;
	}

	/**
	 * 小帮订单类别
	 * @version 1.2
	 * @param $params
	 * @return array
	 */
	public static function providerListV12($params)
	{
		$result       = [];
		$current_page = !empty($params['page']) ? $params['page'] : 1;
		$page         = 0;
		if ($current_page > 1) {
			$page = $current_page - 1;
		}
		$page_size = !empty($params['page_size']) ? $params['page_size'] : 20;
//		$per_page_size = ceil($page_size / 2);

		$old_order = WorkerOrderHelper::oldOrderData($params['user_id'], $params['status'], $page, $page_size);
		$new_order = WorkerOrderHelper::newOrderList($params['user_id'], $params['status'], $page, $page_size);
		if ($params['status'] == 1) {
			//企业送临时订单
			$biz_tmp_order = WorkerOrderHelper::bizTmpOrderList($params['user_id'], $params['status'], $page, $page_size);
		}

		$page1 = $old_order['pagination']; //旧表的分页信息
		$page2 = $new_order['pagination']; //新表的分页信息

		$page_count  = isset($biz_tmp_order) ? max($page1['pageCount'], $page2['pageCount'], $biz_tmp_order['pagination']['pageCount']) : max($page1['pageCount'], $page2['pageCount']);  //比较取大的页数
		$total_count = isset($biz_tmp_order) ? max($page1['totalCount'], $page2['totalCount'], $biz_tmp_order['pagination']['totalCount']) : max($page1['totalCount'], $page2['totalCount']); //总记录数

		$all_order = isset($biz_tmp_order) ? array_merge($old_order['list'], $new_order['list'], $biz_tmp_order['list']) : array_merge($old_order['list'], $new_order['list']);
		unset($old_order);
		unset($new_order);
		if ($params['status'] == 2) {
			//已完成的订单，未支付的要优先
			ArrayHelper::multisort($all_order, ['payment_status', 'create_time'], [SORT_ASC, SORT_DESC]);  //排序
		} else {
			ArrayHelper::multisort($all_order, 'create_time', SORT_DESC);  //排序
		}
		$list = [];
		foreach ($all_order as $key => $value) {
			$providerTakeMoney            = WalletHelper::getProviderTakeMoney([
				'cate_id'     => $value['cate_id'],
				'city_id'     => $value['city_id'],
				'area_id'     => $value['area_id'],
				'provider_id' => $params['user_id'],
			], $value['order_amount']);
			$category                     = CateListHelper::getCateListName($value['cate_id'], $value['second_cate_id']);
			$list[$key]['order_id']       = $value['order_id'];
			$list[$key]['order_no']       = $value['order_no'];
			$list[$key]['create_time']    = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['start_address']  = rtrim($value['start_address'], ',');
			$list[$key]['end_address']    = isset($value['end_address']) ? rtrim($value['end_address'], ',') : "";
			$list[$key]['cate_name']      = $category['cate_name'];
			$list[$key]['n_or_o']         = $value['n_or_o'];//新还是旧的订单
			$list[$key]['type']           = $value['cate_id'];//出行，帮我办，帮我送，帮我买,企业送的分类ID
			$list[$key]['order_amount']   = $value['order_amount'];  //订单金额
			$list[$key]['payment_status'] = $value['payment_status'];  //支付状态
			$list[$key]['user_mobile']    = $value['user_mobile'];  //用户手机号码

			$list[$key]['payment_status_text'] = self::getPaymentStatusText($value['payment_status'], $value['cate_id'], $value['order_status']);  //支付状态

			//特殊字段
			$list[$key]['data']['log_time']   = date("Y-m-d H:i:s", $value['create_time']); //默认值
			$list[$key]['data']['maybe_time'] = empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']);
			$list[$key]['data']['content']    = isset($value['order_content']) ? $value['order_content'] : null;
			if ($value['cate_id'] == Ref::CATE_ID_FOR_MOTOR) {
				//旧表订单摩的状态：1.抢单成功；2.已接乘客；3.安全到达
				//新表订单出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
				$list[$key]['data']['trip_status'] = $value['trip_status'];
			}

			if ($value['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND) {
				$list[$key]['data']['receiver_mobile'] = $value['receiver_mobile'];  //企业送的收货人电话
			}
			if ($value['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND_TMP) {
				$list[$key]['data']['delivery_area'] = $value['delivery_area'] ? $value['delivery_area'] : "其他";  //企业送的收货人电话
			}
		}
		$result['pagination'] = [
			'page'       => $current_page,  //当前页
			'pageSize'   => $page_size,  //每页大小
			'pageCount'  => $page_count,  //总页数
			'totalCount' => $total_count, //总记录数
		];
		$result['list']       = $list;

		return $result;
	}


	/**
	 * 获取商家今日的数据
	 * @param $provider_id
	 * @return array
	 */
	public static function getTodayData($provider_id)
	{
		$start_time = strtotime(date("Y-m-d", time()));
		$end_time   = $start_time + 86399;

		//已接订单数-旧订单
		$old_order = (new Query())->from("bb_51_orders")->where(['shops_id' => $provider_id, 'status' => [4, 5]])->andWhere(['between', 'create_time', $start_time, $end_time])->count();

		//已接订单数-快送类
		$errand_order = (new Query())->from("wy_order AS wo")
			->leftJoin("wy_order_errand AS woe", "wo.order_id = woe.order_id")
			->where(['wo.provider_id' => $provider_id, 'wo.order_status' => [5, 6, 7], 'woe.errand_status' => 5])
			->andWhere(['between', 'wo.create_time', $start_time, $end_time])->count();

		//已接订单数-出行类
		$trip_order = (new Query())->from("wy_order AS wo")
			->leftJoin("wy_order_trip AS wot", "wo.order_id = wot.order_id")
			->where(['wo.provider_id' => $provider_id, 'wo.order_status' => [1, 6, 7], 'wot.trip_status' => 5])
			->andWhere(['between', 'wo.create_time', $start_time, $end_time])->count();

		$order_count = $old_order + $errand_order + $trip_order;
		//收入
		$income_amount = (new Query())->from("bb_51_income_shop")->where(['shops_id' => $provider_id, 'account_type' => 1, 'type' => 1])->andWhere(['between', 'create_time', $start_time, $end_time])->sum("money");

		//未完成订单-旧订单
		$old_not_finish = (new Query())->from("bb_51_orders")->where(['shops_id' => $provider_id, 'status' => 1])->andWhere(['between', 'create_time', $start_time, $end_time])->andWhere(['<>', 'trip_status', 3])->count();

		//未完成订单-快送类
		$errand_not_finish = (new Query())->from("wy_order AS o")
			->leftJoin("wy_order_errand AS oe", "o.order_id = oe.order_id")
			->where(['o.provider_id' => $provider_id])
			->andWhere("( o.cate_id IN(135 , 136 , 137) AND o.order_status = 5) or( o.cate_id=138 AND o.order_status = 0)")//小帮快送未完成和企业送未完成
			->andWhere(['<>', 'oe.errand_status', Ref::ERRAND_STATUS_FINISH])
			->count();

		//未完成订单-出行类
		$trip_not_finish = (new Query())->from("wy_order AS o")
			->leftJoin("wy_order_trip AS wot", "o.order_id = wot.order_id")
			->where(['o.provider_id' => $provider_id])
			->andWhere("( o.cate_id=51 AND o.order_status = 0)")//企业送
			->andWhere(['<>', 'wot.trip_status', Ref::TRIP_STATUS_END])
			->count();


		//未完成的企业送订单
		$tmp_data = self::judgeBizOrder($provider_id);
		$tmp_data ? $biz_not_finish = $tmp_data['tmp_no'] : $biz_not_finish = '';
		$not_finish_count = $old_not_finish + $errand_not_finish + $trip_not_finish;

		$result = [
			'order_count'      => $order_count,
			'income_amount'    => empty($income_amount) ? 0 : $income_amount,
			'not_finish_count' => $not_finish_count,        //没问出
			'biz_not_finish'   => $biz_not_finish,     //企业送未完成
		];

		return $result;
	}

	/**
	 * 获取商家今日的数据
	 * @param $provider_id
	 * @return array
	 */
	public static function getTodayDataV11($provider_id)
	{
		$start_time = strtotime(date("Y-m-d", time()));
		$end_time   = $start_time + 86399;

		//已接订单数-旧订单
		$old_order = (new Query())->from("bb_51_orders")->where(['shops_id' => $provider_id, 'status' => [4, 5]])->andWhere(['between', 'create_time', $start_time, $end_time])->count();

		//已接订单数-快送类
		$errand_order = (new Query())->from("wy_order AS wo")
			->leftJoin("wy_order_errand AS woe", "wo.order_id = woe.order_id")
			->where(['wo.provider_id' => $provider_id, 'wo.order_status' => [5, 6, 7], 'woe.errand_status' => 5])
			->andWhere(['between', 'wo.create_time', $start_time, $end_time])->count();

		//已接订单数-出行类
		$trip_order = (new Query())->from("wy_order AS wo")
			->leftJoin("wy_order_trip AS wot", "wo.order_id = wot.order_id")
			->where(['wo.provider_id' => $provider_id, 'wo.order_status' => [1, 6, 7], 'wot.trip_status' => 5])
			->andWhere(['between', 'wo.create_time', $start_time, $end_time])->count();

		$order_count = $old_order + $errand_order + $trip_order;
		//收入
		$income_amount = (new Query())->from("bb_51_income_shop")->where(['shops_id' => $provider_id, 'account_type' => 1, 'type' => 1])->andWhere(['between', 'create_time', $start_time, $end_time])->sum("money");

		//未完成订单-旧订单
		$old_not_finish = (new Query())->from("bb_51_orders")->where(['shops_id' => $provider_id, 'status' => 1])->andWhere(['between', 'create_time', $start_time, $end_time])->andWhere(['<>', 'trip_status', 3])->count();

		//未完成订单-快送类
		$errand_not_finish = (new Query())->from("wy_order AS o")
			->leftJoin("wy_order_errand AS oe", "o.order_id = oe.order_id")
			->where(['o.provider_id' => $provider_id])
			->andWhere("( o.cate_id IN(135 , 136 , 137) AND o.order_status = 5) or( o.cate_id=138 AND o.order_status = 0)")//小帮快送未完成和企业送未完成
			->andWhere(['<>', 'oe.errand_status', Ref::ERRAND_STATUS_FINISH])
			->count();

		//未完成订单-出行类
		$trip_not_finish = (new Query())->from("wy_order AS o")
			->leftJoin("wy_order_trip AS wot", "o.order_id = wot.order_id")
			->where(['o.provider_id' => $provider_id])
			->andWhere("( o.cate_id=51 AND o.order_status = 0)")//企业送
			->andWhere(['<>', 'wot.trip_status', Ref::TRIP_STATUS_END])
			->count();


		//未完成的企业送订单
		$biz_not_finish = BizTmpOrder::find()->where(['tmp_status' => Ref::BIZ_TMP_STATUS_PICKED, 'provider_id' => $provider_id, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND])
			->orderBy(['tmp_id' => SORT_DESC])->count();

		//总未完成订单数
		$not_finish_count = $old_not_finish + $errand_not_finish + $trip_not_finish + $biz_not_finish;

		$result = [
			'order_count'      => $order_count,
			'income_amount'    => empty($income_amount) ? 0 : $income_amount,
			'not_finish_count' => $not_finish_count,        //没问出
		];

		return $result;
	}

	//旧表订单
	private static function orderSeaForOldOrder($shopInfo)
	{
		$result    = [];
		$field_arr = ['n_address_location', 'errand_type', 'n_end_address', 'n_location', 'n_address', 'order_style', 'int_shops', 'orderid', 'way', 'content', 'cate_id', 'create_time', 'money', 'maybe_time', 'area_id', 'city_id'];
		$time24    = time() - 86400;
		$order     = (new Query())->select($field_arr)->from('bb_51_orders')
			->where(['status' => 0, 'robbed' => 0, 'city_id' => $shopInfo['city_id']])
			->andWhere(['!=', 'uid', $shopInfo['uid']])
			->andWhere(['>', 'create_time', $time24])
			->andWhere("cate_id = 51 ")//in ({$shopInfo['type_second']})")//TODO 判断字符空值
			->orderBy(['create_time' => SORT_DESC])->limit(10)->all();

		if (!empty($order)) {
			foreach ($order as $key => $value) {
				$result[$key]['order_no']      = $value['orderid'];
				$result[$key]['user_address']  = $value['n_address'];
				$result[$key]['start_address'] = $value['n_address'];
				$result[$key]['end_address']   = $value['n_end_address'];
				$result[$key]['cate_id']       = $value['cate_id'];
				$result[$key]['create_time']   = $value['create_time'];
				$result[$key]['content']       = $value['content'];
				$result[$key]['maybe_time']    = $value['maybe_time'];
				$result[$key]['city_id']       = $value['city_id'];
				$result[$key]['area_id']       = $value['area_id'];
				$result[$key]['errand_type']   = $value['errand_type'];//TODO 迭代订单海后这字段可以删除

				if ($value['order_style'] == 3) {//3：快送;其他：摩的
					$result[$key]['start_location'] = AMapHelper::convert_baidu2Amap($value['n_address_location']);
				} else {
					$result[$key]['start_location'] = AMapHelper::convert_baidu2Amap($value['n_location']);
				}

				$result[$key]['order_amount'] = $value['money'];
			}
		}

		return $result;

	}

	//新表订单
	private static function orderSeaForNewOrder($shopInfo)
	{
		$result = [];
		$field  = "o.order_no ,o.user_id, o.user_address , o.order_amount , o.cate_id , o.create_time , o.start_address , o.end_address , o.end_location , o.start_location , o.city_id , o.area_id ,";
		$field  .= "oe.service_qty ,oe.service_time, oe.total_fee , oe.errand_content, oe.starting_distance, oe.errand_type,oe.service_price,oe.maybe_time";
		$where  = "o.order_status = 5 AND o.robbed = 0 AND o.city_id = {$shopInfo['city_id']}  AND oe.errand_status = 1 ";

		//小帮的分类
		$cate_arr = explode(",", $shopInfo['type_second']);
		if (in_array(Ref::CATE_ID_FOR_ERRAND, $cate_arr)) {
			$errand   = [
				Ref::CATE_ID_FOR_ERRAND_BUY,
				Ref::CATE_ID_FOR_ERRAND_SEND,
				Ref::CATE_ID_FOR_ERRAND_DO
			];
			$cate_arr = \common\helpers\utils\ArrayHelper::merge($cate_arr, $errand);
		}

		$cate_str = implode(",", $cate_arr);

		if ($cate_str) {
			$where .= " and o.cate_id in (" . $cate_str . ")";
		}

		//24小时内的订单
		$lessTime24 = time() - 86400;
		if ($cate_str) {
			$where .= " and o.create_time >  " . $lessTime24;
		}
		//TODO 城市分类有bug

		$sql   = "SELECT {$field} FROM wy_order o LEFT JOIN wy_order_errand oe ON o.order_id = oe.order_id WHERE {$where} ORDER BY o.create_time DESC LIMIT 20";
		$order = Yii::$app->db->createCommand($sql)->queryAll();

		if (!empty($order)) {
			foreach ($order as $key => $value) {
				$result[$key]['order_no']       = $value['order_no'];
				$result[$key]['user_id']        = $value['user_id'];
				$result[$key]['user_address']   = $value['user_address'];
				$result[$key]['start_address']  = $value['start_address'];
				$result[$key]['end_address']    = $value['end_address'];
				$result[$key]['cate_id']        = $value['cate_id'];
				$result[$key]['create_time']    = $value['create_time'];
				$result[$key]['content']        = $value['errand_content'];
				$result[$key]['start_location'] = $value['start_location'];
				$result[$key]['order_amount']   = $value['order_amount'];
				$result[$key]['city_id']        = $value['city_id'];
				$result[$key]['area_id']        = $value['area_id'];
				$result[$key]['maybe_time']     = $value['maybe_time'];
				$result[$key]['errand_type']    = $value['errand_type'];//TODO 迭代订单海后这字段可以删除
				//特殊字段
				$result[$key]['service_qty']   = $value['service_qty'];
				$result[$key]['service_price'] = $value['service_price'];
				$result[$key]['total_fee']     = $value['total_fee'];
				$result[$key]['service_time']  = $value['service_time'];
			}
		}

		return $result;
	}

	//企业送订单
	private static function orderSeaForBizTmpOrder($shopInfo)
	{
		$result = [];
		$order  = BizTmpOrder::find()->where(['tmp_status' => Ref::BIZ_TMP_STATUS_WAITE, 'city_id' => $shopInfo['city_id']])->asArray()->all();
		if (!empty($order)) {
			foreach ($order as $key => $value) {
				$result[$key]['order_no']       = $value['tmp_no'];
				$result[$key]['user_id']        = $value['user_id'];
				$result[$key]['user_address']   = $value['user_address'];
				$result[$key]['start_address']  = $value['start_address'];
				$result[$key]['end_address']    = $value['user_address'];//企业送临时订单没有结束地址，用user_address代替
				$result[$key]['cate_id']        = $value['cate_id'];
				$result[$key]['create_time']    = $value['create_time'];
				$result[$key]['content']        = $value['content'];
				$result[$key]['start_location'] = $value['start_location'];
				$result[$key]['city_id']        = $value['city_id'];
				$result[$key]['area_id']        = $value['area_id'];
				$result[$key]['order_amount']   = 0;
				$result[$key]['maybe_time']     = null;
				//特殊字段
				$result[$key]['tmp_qty']       = $value['tmp_qty'];
				$result[$key]['delivery_area'] = $value['delivery_area'];
			}
		}

		return $result;
	}


	//出行订单海
	public static function orderSeaForTrip($shopInfo)
	{

		$result = [];
		$field  = "o.order_no,o.user_id , o.user_address , o.order_amount , o.cate_id , o.create_time , o.start_address , o.end_address , o.end_location , o.start_location , o.city_id , o.area_id ,";
		$field  .= "ot.amount_ext";
		$where  = "o.order_status = 0 AND o.robbed = 0 AND o.city_id = {$shopInfo['city_id']}  AND ot.trip_status = 1 and o.cate_id= " . Ref::CATE_ID_FOR_MOTOR;


		$allowCate = explode(",", $shopInfo['type_second']);  //小帮允许接单的业务分类
		//如果出行在接单业务内
		if (in_array(Ref::CATE_ID_FOR_MOTOR, $allowCate)) {
			//24小时内的订单
			$lessTime24 = time() - 86400;
			$where      .= " and o.create_time >  " . $lessTime24;

			$sql   = "SELECT {$field} FROM wy_order o LEFT JOIN wy_order_trip ot ON o.order_id = ot.order_id WHERE {$where} ORDER BY o.create_time DESC LIMIT 20";
			$order = Yii::$app->db->createCommand($sql)->queryAll();

			if (!empty($order)) {
				foreach ($order as $key => $value) {

					$result[$key]['order_no']       = $value['order_no'];
					$result[$key]['user_id']        = $value['user_id'];
					$result[$key]['user_address']   = $value['user_address'];
					$result[$key]['start_address']  = $value['start_address'];
					$result[$key]['end_address']    = $value['end_address'];
					$result[$key]['cate_id']        = $value['cate_id'];
					$result[$key]['create_time']    = $value['create_time'];
					$result[$key]['start_location'] = $value['start_location'];
					$result[$key]['order_amount']   = $value['order_amount'];
					$result[$key]['city_id']        = $value['city_id'];
					$result[$key]['area_id']        = $value['area_id'];
					$result[$key]['content']        = $value['start_address'] . '--' . $value['end_address'];
					$result[$key]['order_amount']   += $value['amount_ext'];
				}
			}
		}

		return $result;
	}

	/**
	 * 订单海v1.0
	 * @param $shopInfo
	 * @return bool
	 */
	public static function getOrderSeaV10($shopInfo)
	{

		$result        = false;
		$shop_location = '[' . $shopInfo['shops_location_lng'] . ',' . $shopInfo['shops_location_lat'] . ']';
		$shop_location = AMapHelper::convert_baidu2Amap($shop_location, 'baidu', false);
		$old_orders    = self::orderSeaForOldOrder($shopInfo);
		$new_orders    = self::orderSeaForNewOrder($shopInfo);

		$order = array_merge($old_orders, $new_orders);
		foreach ($order as $key => $value) {
			$start_location = AMapHelper::coordToStr($value['start_location']);
			$amap_data      = AMapHelper::bicycling($shop_location, $start_location);
			if ($amap_data) {
				$range = self::getProviderRange($shopInfo, $value['cate_id']);

				if ($amap_data['distance'] > $range * 1000) {
					continue;
				}
			} else {
				continue;
			}
			$distance = $amap_data['distance'];
			if ($value['cate_id'] == Ref::CATE_ID_FOR_ERRAND_BUY
				|| $value['cate_id'] == Ref::CATE_ID_FOR_ERRAND_SEND
				|| $value['cate_id'] == Ref::CATE_ID_FOR_ERRAND_DO
			) {
				$value['cate_id'] = Ref::CATE_ID_FOR_ERRAND;
			}

			$type        = $value['cate_id'];
			$errand_type = $value['errand_type'];
			if ($value['errand_type']) {
				$type .= "-" . $errand_type;
			}

			$result[$key]['order_no']      = $value['order_no'];
			$result[$key]['user_address']  = $value['user_address'];
			$result[$key]['category']      = CateListHelper::getOrderListCateName($value['cate_id'], $value['errand_type']);
			$result[$key]['type']          = $type;
			$result[$key]['start_address'] = $value['start_address'];
			$result[$key]['end_address']   = $value['end_address'];
			$over_time                     = time() - $value['create_time'];
			$result[$key]['over_time']     = UtilsHelper::durationLabel($over_time);
			$result[$key]['distance']      = $distance;
			$result[$key]['distance_text'] = UtilsHelper::distanceLabel($distance);
			$result[$key]['content']       = $value['content'];
			$result[$key]['create_time']   = date("Y-m-d H:i:s", $value['create_time']);
			$result[$key]['order_amount']  = sprintf("%.2f", $value['order_amount']);   //订单总金额


			$result[$key]['data']['log_time']   = date("Y-m-d H:i:s", $value['create_time']); //默认值
			$result[$key]['data']['maybe_time'] = empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']);
			if ($errand_type == Ref::ERRAND_TYPE_DO) {

				$service_price                          = isset($value['service_price']) ? $value['service_price'] : 0;
				$service_qty                            = isset($value['service_qty']) ? $value['service_qty'] : 0;
				$total_fee                              = isset($value['total_fee']) ? $value['total_fee'] : 0.00;
				$result[$key]['data']['service_detail'] = $service_price * $service_qty . '元-' . $service_qty . '小时,小费:' . $total_fee . '元';
			}

		}

		if ($result) {
			$flag = [];
			foreach ($result as $value) {
				$flag[] = $value['create_time'];
			}
			array_multisort($flag, SORT_DESC, $result);
		}

		return $result;
	}


	/**
	 * 订单海v1.1
	 * @param $shopInfo
	 * @return bool
	 */
	public static function getOrderSeaV11($shopInfo)
	{
		$result        = false;
		$shop_location = '[' . $shopInfo['shops_location_lng'] . ',' . $shopInfo['shops_location_lat'] . ']';
		$shop_location = AMapHelper::convert_baidu2Amap($shop_location, 'baidu', false);
		$old_orders    = self::orderSeaForOldOrder($shopInfo);
		$new_orders    = self::orderSeaForNewOrder($shopInfo);
		$biz_orders    = self::orderSeaForBizTmpOrder($shopInfo);
		$order         = array_merge($old_orders, $new_orders, $biz_orders);
		unset($old_orders);
		unset($new_orders);
		unset($biz_orders);

		foreach ($order as $key => $value) {

			$start_location = AMapHelper::coordToStr($value['start_location']);
			$amap_data      = AMapHelper::bicycling($shop_location, $start_location);
			if ($amap_data) {

				$range = self::getProviderRange($shopInfo, $value['cate_id']);
				if ($amap_data['distance'] > $range * 1000) {
					continue;
				}
			} else {
				continue;
			}
			$distance     = $amap_data['distance'];
			$over_time    = time() - $value['create_time'];
			$result[$key] = [
				'order_no'      => $value['order_no'],
				'user_address'  => $value['user_address'],
				'category'      => CateListHelper::NameByCateId($value['cate_id']),
				'type'          => $value['cate_id'],
				'start_address' => $value['start_address'],
				'end_address'   => $value['end_address'],
				'over_time'     => UtilsHelper::durationLabel($over_time),
				'distance'      => $distance,
				'distance_text' => UtilsHelper::distanceLabel($distance),
				'content'       => $value['content'],
				'create_time'   => date("Y-m-d H:i:s", $value['create_time']),
				'order_amount'  => sprintf("%.2f", $value['order_amount']),
			];

			$result[$key]['data'] = [
				'log_time'   => date("Y-m-d H:i:s", $value['create_time']),//默认值 Android空数据会报错
				'maybe_time' => !isset($value['maybe_time']) && empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']),
			];

			if ($value['cate_id'] == Ref::CATE_ID_FOR_ERRAND_DO) {

				$service_price                          = isset($value['service_price']) ? $value['service_price'] : 0;
				$service_qty                            = isset($value['service_qty']) ? $value['service_qty'] : 0;
				$total_fee                              = isset($value['total_fee']) ? $value['total_fee'] : 0.00;
				$result[$key]['data']['service_detail'] = $service_price * $service_qty . '元-' . $service_qty . '小时,小费:' . $total_fee . '元';
			}

			if ($value['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND) {    //企业送

				$result[$key]['data']['tmp_qty'] = $value['tmp_qty'];    //订单数量
			}
		}

		if ($result) {
			$flag = [];
			foreach ($result as $value) {
				$flag[] = $value['create_time'];
			}
			array_multisort($flag, SORT_DESC, $result);
		}

		return $result;
	}

	//订单海1.2
	//增加了小帮出行
	public static function getOrderSeaV12($shopInfo)
	{
		$result        = [];
		$shop_location = AMapHelper::coordToStr($shopInfo['shops_location']);
		$old_orders    = self::orderSeaForOldOrder($shopInfo);
		$new_orders    = self::orderSeaForNewOrder($shopInfo);
		$biz_orders    = self::orderSeaForBizTmpOrder($shopInfo);
		$trip_orders   = self::orderSeaForTrip($shopInfo);
		$order         = array_merge($old_orders, $new_orders, $biz_orders, $trip_orders);

		unset($old_orders);
		unset($new_orders);
		unset($biz_orders);
		unset($trip_orders);

		foreach ($order as $key => $value) {

			$start_location = AMapHelper::coordToStr($value['start_location']);
			$aMap_data      = AMapHelper::bicycling($shop_location, $start_location);
			if ($aMap_data) {

				$range = self::getProviderRange($shopInfo, $value['cate_id']);
				if ($aMap_data['distance'] > $range * 1000) {
					continue;
				}
			} else {
				continue;
			}

			$distance          = $aMap_data['distance'];
			$over_time         = time() - $value['create_time'];
			$providerTakeMoney = WalletHelper::getProviderTakeMoney([
				'cate_id'     => $value['cate_id'],
				'provider_id' => $shopInfo['id'],
				'city_id'     => $value['city_id'],
				'area_id'     => $value['area_id'],
			], $value['order_amount']);
			$result[$key]      = [
				'order_no'      => $value['order_no'],
				'user_address'  => $value['user_address'],
				'category'      => CateListHelper::NameByCateId($value['cate_id']),
				'type'          => $value['cate_id'],
				'start_address' => $value['start_address'],
				'end_address'   => $value['end_address'],
				'over_time'     => UtilsHelper::durationLabel($over_time),
				'distance'      => $distance,
				'distance_text' => UtilsHelper::distanceLabel($distance),
				'content'       => $value['content'],
				'create_time'   => date("Y-m-d H:i:s", $value['create_time']),
				'order_amount'  => $providerTakeMoney,//sprintf("%.2f", $value['order_amount']),
			];

			$result[$key]['data'] = [
				'log_time'   => date("Y-m-d H:i:s", $value['create_time']),//默认值 Android空数据会报错
				'maybe_time' => !isset($value['maybe_time']) && empty($value['maybe_time']) ? null : date("Y-m-d H:i", $value['maybe_time']),
			];

			if ($value['cate_id'] == Ref::CATE_ID_FOR_ERRAND_DO) {
				$service_price                          = isset($value['service_price']) ? $value['service_price'] : 0;
				$service_qty                            = isset($value['service_qty']) ? $value['service_qty'] : 0;
				$total_fee                              = isset($value['total_fee']) ? $value['total_fee'] : 0.00;
				$result[$key]['data']['service_detail'] = $service_price * $service_qty . '元-' . $service_qty . '小时,小费:' . $total_fee . '元';
				$result[$key]['data']['service_qty']    = "{$service_qty}小时";//服务时长
				$result[$key]['data']['maybe_time']     = (isset($value['service_time']) && !empty($value['service_time'])) ? date("Y/m/d H:i", $value['service_time']) : "无";//服务时间
			}

			if ($value['cate_id'] == Ref::CATE_ID_FOR_BIZ_SEND) {    //企业送
				$biz                                   = BizInfo::findOne(['user_id' => $value['user_id']]);
				$biz_name                              = $biz->biz_name;
				$result[$key]['data']['biz_name']      = $biz_name ? $biz_name : "无忧企业";
				$result[$key]['data']['tmp_qty']       = $value['tmp_qty'];    //订单数量
				$result[$key]['data']['delivery_area'] = isset($value['delivery_area']) ? $value['delivery_area'] : "其他";
			}
		}
		if ($result) {
			ArrayHelper::multisort($result, 'create_time', SORT_DESC);  //排序
		}

		return $result;
	}


	/**
	 * 检查是否有进行中的企业送订单(返回最后一张订单)
	 * @param $provider_id
	 * @return array|bool|null|\yii\db\ActiveRecord
	 */
	public static function judgeBizOrder($provider_id)
	{
		$result = false;
		$data   = BizTmpOrder::find()->select("tmp_no")->where(['tmp_status' => Ref::BIZ_TMP_STATUS_PICKED, 'provider_id' => $provider_id, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND])->orderBy(['tmp_id' => SORT_DESC])->asArray()->one();
		if ($data) {
			$result = $data;
		}

		return $result;
	}

	public static function getPaymentStatusText($payment_status, $cate_id, $order_status)
	{

		$text = "待支付";
		if ($payment_status == Ref::PAY_STATUS_COMPLETE) {
			if ($cate_id == Ref::CATE_ID_FOR_BIZ_SEND && $order_status == Ref::ORDER_STATUS_DOING) {
				$text = "已扣款";

			} else {

				$text = "已支付";
			}
		}

		return $text;

	}

	//获取小帮的接单范围
	public static function getProviderRange($shopInfo, $cate_id)
	{
		$range = $shopInfo['range'];
		//非出行类的中山小帮推送范围是10公里
		if ($cate_id != Ref::CATE_ID_FOR_MOTOR) {

			$city_id = $shopInfo['city_id'];
			$arr     = [95, 85, 83, 90, 337];    //中山,茂名,江门,韶关,遂宁
			if (in_array($city_id, $arr)) {
				$range = 10;
			}

			$area_id  = $shopInfo['area_id'];
			$area_arr = [748];        //佛山-顺德
			if (in_array($area_id, $area_arr)) {
				$range = 10;
			}
		}

		return $range;
	}
}

