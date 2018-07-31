<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/14
 */

namespace api_user\modules\v1\helpers;

use common\components\Ref;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\OrderHelper;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderTrip;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class UserOrderHelper extends OrderHelper
{

	/**
	 * 取用户旧订单表的数据
	 * 现在只有摩的
	 * @param $user_id
	 * @param $status
	 * @param $page
	 * @param $page_size
	 * @return array
	 */
	public static function oldOrderData($user_id, $status, $page, $page_size)
	{
		$list         = [];
		$result       = [];
		$where['uid'] = $user_id;
		switch ($status) {
			case 0://发布中
				$where['status'] = 0;
				break;
			case 1://进行中
				$where['status'] = [1, 3];
				break;
			case 2://完成
				$where['status'] = [4, 5];
				break;
			case 3://取消
				$where['status'] = 2;
				break;
			default:
				$where['status'] = [0, 1, 2, 4, 5];
				break;
		}

		$count                = (new Query())->from("bb_51_orders")->where($where)->andWhere('n_sure != 6')->count('id');
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $page_size;
		$subQuery             = (new Query())->from("bb_51_orders")->select('id')->where($where)->andWhere('n_sure != 6')->offset($pagination->offset)->limit($pagination->limit)->orderBy(['create_time' => SORT_DESC]);
		$select               = ['orderid', 'create_time', 'n_address','robbed', 'n_end_address', 'cate_id', 'errand_type', 'o.id', 'maybe_time', 'trip_status'];
		$data                 = (new Query())->from(['o' => 'bb_51_orders', 't2' => $subQuery])->select($select)->where('o.id=t2.id')->orderBy(['o.create_time' => SORT_DESC])->all();

		foreach ($data as $k => $v) {
			$list[$k]['order_id']      = $v['id'];
			$list[$k]['order_no']      = $v['orderid'];
			$list[$k]['create_time']   = $v['create_time'];
			$list[$k]['start_address'] = $v['n_address'];
			$list[$k]['end_address']   = $v['n_end_address'];
			//$list[$k]['cate_id']        = $v['cate_id'];
			$list[$k]['second_cate_id'] = $v['errand_type'];
			$list[$k]['n_or_o']         = 'old';
			$list[$k]['maybe_time']     = $v['maybe_time'];
			//特殊字段
			$list[$k]['trip_status'] = $v['trip_status'];  //出行状态：1.抢单成功；2.已接乘客；3.安全到达
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
	 * 新表的小帮出行订单数据
	 * @param $user_id
	 * @param $status
	 * @param $page
	 * @param $page_size
	 * @return array
	 */
	public static function tripOrderData($user_id, $status, $page, $page_size)
	{
		$list                  = [];
		$result                = [];
		$where['user_id']      = $user_id;
		$where['user_deleted'] = 0;  //0是正常，1是删除
		$where['cate_id']      = Ref::CATE_ID_FOR_MOTOR;
		if (isset($status)) {
			switch ($status) {
				case 0://发布中
					$where['order_status'] = 0;
					$where['robbed']       = Ref::ORDER_ROB_NEW;
					break;
				case 1://进行中
					$where['order_status'] = [0, 1];
					$where['robbed']       = Ref::ORDER_ROBBED;
					break;
				case 2://已经完成
					$where['order_status'] = [6, 7];
					break;
				case 3://取消
					$where['order_status'] = [3, 4, 9];
					break;
				default:
					break;
			}
		}

		$subQuery   = Order::find()->select('order_id')->where($where);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $page_size]);
		$select     = ['order_no', 'create_time', 'start_address', 'end_address', 'cate_id', 'o.order_id'];
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
			$list[$k]['cate_id']        = $v['cate_id'];
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
	 * 取用户新订单表的数据
	 * 小帮快送的
	 * @param $user_id
	 * @param $status
	 * @param $page
	 * @param $page_size
	 * @return array
	 */
	private static function newOrderData($user_id, $status, $page, $page_size)
	{
		$list                  = [];
		$result                = [];
		$where['user_id']      = $user_id;
		$where['user_deleted'] = 0;  //0是正常，1是删除
		//帮我买，帮我送，帮我办的ID
		$where['cate_id'] = [Ref::CATE_ID_FOR_ERRAND, Ref::CATE_ID_FOR_ERRAND_BUY, Ref::CATE_ID_FOR_ERRAND_SEND, Ref::CATE_ID_FOR_ERRAND_DO];
		if (isset($status)) {
			switch ($status) {
				case 0://发布中
					$where['order_status'] = 5;
					$where['robbed']       = Ref::ORDER_ROB_NEW;
					break;
				case 1://进行中
					$where['order_status'] = 5;
					break;
				case 2://已经完成
					$where['order_status'] = [6, 7];
					break;
				case 3://取消
					$where['order_status'] = [3, 4, 9];
					break;
				default:
					$where['order_status'] = [3, 4, 5, 6, 7, 9];
					break;
			}
		}

		$subQuery             = Order::find()->select('order_id')->where($where);
		$count                = $subQuery->count();
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $page_size;
		$select               = ['order_no', 'create_time', 'start_address', 'end_address', 'cate_id', 'o.order_id'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();

		foreach ($data as $k => $v) {
			switch ($status) {
				case 0 : //发布中
					$cond = ['order_id' => $v['order_id'], 'errand_status' => 1];
					break;
				case 1 : //进行中
					$cond = ['order_id' => $v['order_id'], 'errand_status' => [2, 3, 4, 5]];
					break;
				default :
					$cond = ['order_id' => $v['order_id']];
					break;
			}
			$orderErrand = OrderErrand::findOne($cond);
			if (!$orderErrand) {
				continue;
			}
			$list[$k]['order_content']  = $orderErrand->errand_content;
			$list[$k]['order_id']       = $v['order_id'];
			$list[$k]['order_no']       = $v['order_no'];
			$list[$k]['create_time']    = $v['create_time'];
			$list[$k]['start_address']  = $v['start_address'];
			$list[$k]['end_address']    = $v['end_address'];
			$list[$k]['second_cate_id'] = $orderErrand->errand_type;
			$list[$k]['n_or_o']         = 'new';
			$list[$k]['maybe_time']     = $orderErrand->maybe_time;

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

	//把新表的快送和出行订单整合到一个方法
	//取代newOrderData和tripOrderData方法
	public static function newOrderList($user_id, $status, $page, $page_size)
	{
		//$errandWhere是快送类的查询条件
		//$tripWhere  是出行类的查询条件
		$list                        = [];
		$result                      = [];
		$errandWhere['user_id']      = $tripWhere['user_id'] = $user_id;
		$errandWhere['user_deleted'] = $tripWhere['user_deleted'] = 0; //0是正常，1是删除
		$errandWhere['cate_id']      = [Ref::CATE_ID_FOR_ERRAND, Ref::CATE_ID_FOR_ERRAND_BUY, Ref::CATE_ID_FOR_ERRAND_SEND, Ref::CATE_ID_FOR_ERRAND_DO]; //快送
		$tripWhere['cate_id']        = Ref::CATE_ID_FOR_MOTOR;  //出行
		if (isset($status)) {
			switch ($status) {
				case 0://发布中
					$tripWhere['order_status']   = 0;
					$errandWhere['order_status'] = 5;
					$tripWhere['robbed']         = $errandWhere['robbed'] = Ref::ORDER_ROB_NEW;
					break;
				case 1://进行中
					$tripWhere['order_status']   = [0, 1];
					$errandWhere['order_status'] = 5;
					$tripWhere['robbed']         = $errandWhere['robbed'] = Ref::ORDER_ROBBED;
					break;
				case 2://已经完成
					$tripWhere['order_status'] = $errandWhere['order_status'] = [6, 7];
					break;
				case 3://取消
					$tripWhere['order_status'] = $errandWhere['order_status'] = [3, 4, 9];
					break;
				default:
					break;
			}
		}

		$subQuery   = Order::find()->select('order_id')->where($errandWhere)->orWhere($tripWhere);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $page_size]);
		$select     = ['order_no', 'create_time', 'start_address', 'end_address','robbed', 'cate_id', 'o.order_id'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();
		foreach ($data as $k => $v) {
			if ($v['cate_id'] == Ref::CATE_ID_FOR_MOTOR) {
				$trip                       = OrderTrip::find()->select(['trip_type', 'trip_status'])->where(['order_id' => $v['order_id']])->one();
				$list[$k]['second_cate_id'] = $trip['trip_type'];
				$list[$k]['trip_status']    = $trip['trip_status'];  //出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
			}
			if (in_array($v['cate_id'], $errandWhere['cate_id'])) {
				$orderErrand                = OrderErrand::find()->select(['errand_type', 'errand_content', 'maybe_time'])->where(['order_id' => $v['order_id']])->one();
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
			$list[$k]['order_id']      = $v['order_id'];
			$list[$k]['order_no']      = $v['order_no'];
			$list[$k]['create_time']   = $v['create_time'];
			$list[$k]['start_address'] = $v['start_address'];
			$list[$k]['end_address']   = $v['end_address'];
			$list[$k]['cate_id']       = $v['cate_id'];
			$list[$k]['n_or_o']        = 'new';
		}

		$result['list']       = $list;
		$result['pagination'] = [
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];

		return $result;
	}

	/**
	 * 用户订单列表
	 * 1.0版本
	 * 不包含小帮出行
	 * @param $params
	 * @return array
	 */
	public static function getOrderList($params)
	{
		$result       = [];
		$current_page = !empty($params['page']) ? $params['page'] : 1;
		$page         = 0;
		if ($current_page > 1) {
			$page = $current_page - 1;
		}
		$page_size     = !empty($params['page_size']) ? $params['page_size'] : 20;
		$per_page_size = ceil($page_size / 2);

		$old_order = self::oldOrderData($params['user_id'], $params['status'], $page, $per_page_size);  //旧表
		$new_order = self::newOrderData($params['user_id'], $params['status'], $page, $per_page_size);  //新表的快送订单
		//$trip_order = self::tripOrderData($params['user_id'], $params['status'], $page, $per_page_size); //新表的出行订单

		$page1 = $old_order['pagination'];  //旧表的分页信息
		$page2 = $new_order['pagination'];  //新表的快送订单的分页信息

		$page_count  = max($page1['pageCount'], $page2['pageCount']);  //比较取大的页数
		$total_count = max($page1['totalCount'], $page2['totalCount']); //总记录数

		$all_order = array_merge($old_order['list'], $new_order['list']);
		unset($old_order);
		unset($new_order);

		$list = [];
		foreach ($all_order as $key => $value) {
			$category               = CateListHelper::getCateListName($value['cate_id'], $value['second_cate_id']);
			$list[$key]['order_id'] = $value['order_id'];
			//$list[$key]['order_no']      = substr($value['order_no'], 0, 3) . '****' . substr($value['order_no'], -4, 4);
			$list[$key]['order_no']      = $value['order_no'];
			$list[$key]['create_time']   = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['start_address'] = rtrim($value['start_address'], ',');
			$list[$key]['end_address']   = rtrim($value['end_address'], ',');
			$list[$key]['cate_name']     = $category['cate_name'];
			$list[$key]['n_or_o']        = $value['n_or_o'];//区分新旧订单表

			$list[$key]['type'] = $value['cate_id'];//出行，帮我办，帮我送，帮我买的分类ID

			$list[$key]['data']['log_time']   = date("Y-m-d H:i:s", $value['create_time']); //默认值
			$list[$key]['data']['maybe_time'] = empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']);
			$list[$key]['data']['content']    = isset($value['order_content']) ? $value['order_content'] : null;
			if ($value['cate_id'] == 51) {
				//旧表订单摩的状态：1.抢单成功；2.已接乘客；3.安全到达
				$list[$key]['data']['trip_status'] = $value['trip_status'];
			}
		}
		ArrayHelper::multisort($list, 'create_time', SORT_DESC);
		$result['pagination'] = [
			'page'       => $current_page,  //当前页
			'pageSize'   => $page_size,  //每页大小
			'pageCount'  => $page_count,  //总页数
			'totalCount' => $total_count, //总记录数
		];
		$result['list']       = $list;

		return $result;
	}

	//用户订单列表
	//1.1版本
	//包含小帮出行
	public static function orderList($params)
	{
		$result       = [];
		$current_page = !empty($params['page']) ? $params['page'] : 1;
		$page         = 0;
		if ($current_page > 1) {
			$page = $current_page - 1;
		}
		$page_size     = !empty($params['page_size']) ? $params['page_size'] : 20;
		$per_page_size = ceil($page_size / 2);

		$old_order = self::oldOrderData($params['user_id'], $params['status'], $page, $per_page_size);  //旧表
		$new_order = self::newOrderList($params['user_id'], $params['status'], $page, $per_page_size);  //新表

		$page1 = $old_order['pagination'];  //旧表的分页信息
		$page2 = $new_order['pagination'];  //新表的快送订单的分页信息

		$page_count  = max($page1['pageCount'], $page2['pageCount']);  //比较取大的页数
		$total_count = max($page1['totalCount'], $page2['totalCount']); //总记录数

		$all_order = array_merge($old_order['list'], $new_order['list']);
		unset($old_order);
		unset($new_order);

		$list = [];
		foreach ($all_order as $key => $value) {
			$category               = CateListHelper::getCateListName($value['cate_id'], $value['second_cate_id']);
			$list[$key]['order_id'] = $value['order_id'];
			//$list[$key]['order_no']      = substr($value['order_no'], 0, 3) . '****' . substr($value['order_no'], -4, 4);
			$list[$key]['order_no']      = $value['order_no'];
			$list[$key]['create_time']   = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['start_address'] = rtrim($value['start_address'], ',');
			$list[$key]['end_address']   = rtrim($value['end_address'], ',');
			$list[$key]['cate_name']     = $category['cate_name'];
			$list[$key]['n_or_o']        = $value['n_or_o'];//区分新旧订单表

			$list[$key]['type'] = $value['cate_id'];//出行，帮我办，帮我送，帮我买的分类ID

			$list[$key]['data']['log_time']   = date("Y-m-d H:i:s", $value['create_time']); //默认值
			$list[$key]['data']['maybe_time'] = empty($value['maybe_time']) ? null : date("Y-m-d H:i:s", $value['maybe_time']);
			$list[$key]['data']['content']    = isset($value['order_content']) ? $value['order_content'] : null;
			if ($value['cate_id'] == 51) {
				//旧表订单摩的状态：1.抢单成功；2.已接乘客；3.安全到达
				$list[$key]['data']['trip_status'] = $value['trip_status'];
			}
		}
		ArrayHelper::multisort($list, 'create_time', SORT_DESC);
		$result['pagination'] = [
			'page'       => $current_page,  //当前页
			'pageSize'   => $page_size,  //每页大小
			'pageCount'  => $page_count,  //总页数
			'totalCount' => $total_count, //总记录数
		];
		$result['list']       = $list;

		return $result;
	}

}