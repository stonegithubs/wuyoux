<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/10/9
 */

namespace api_wx\modules\mpv1\helpers;

use common\components\Ref;
use common\helpers\orders\CateListHelper;
use common\helpers\orders\OrderHelper;
use common\models\orders\BizTmpOrder;
use common\models\orders\Order;
use common\models\orders\OrderErrand;
use common\models\orders\OrderTrip;
use Yii;
use yii\data\Pagination;

class WxOrderHelper extends OrderHelper
{
	public static function getList($params)
	{
		$result = [];

		$where                 = [];
		$where['user_id']      = $params['user_id'];
		$where['user_deleted'] = 0;  //0是正常,1是删除
		$where['cate_id']      = [Ref::CATE_ID_FOR_ERRAND_BUY, Ref::CATE_ID_FOR_ERRAND_SEND, Ref::CATE_ID_FOR_ERRAND_DO];
		if (isset($params['status'])) {
			switch ($params['status']) {
				case 1://发布
					$where['order_status'] = 5;
					$where['robbed']       = 0;
					break;
				case 2://进行中
					$where['order_status'] = 5;
					$where['robbed']       = 1;
					break;
				case 3://已经完成
					$where['order_status'] = [6, 7];
					break;
				case 4://取消
					$where['order_status'] = [3, 4, 9];
					break;
				default:
					$where['order_status'] = [3, 4, 5, 6, 7, 9];
					break;
			}
		}

		$current_page = !empty($params['page']) ? intval($params['page']) : 1;                 //分页属性从0开始的
		$pageSize     = !empty($params['pageSize']) ? intval($params['pageSize']) : 20;    //每页数量
		$select       = !empty($params['select']) ? $params['select'] : "*";
		$subQuery     = Order::find()->select('wy_order.order_id')->join("INNER JOIN", "wy_order_errand", "wy_order.order_id = wy_order_errand.order_id")->where($where);
		$count        = $subQuery->count();
		$page         = 0;
		if ($current_page > 0) {
			$page = $current_page - 1;
		}
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $pageSize;
		$result['pagination']
							  = [
			'page'       => $current_page,
			'pageSize'   => $pageSize,
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount
		];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();

		$list = [];
		foreach ($data as $key => $value) {
			$order_status = self::getOrderStatus($value['order_status'], $value['robbed']);
			$orderErrand  = OrderErrand::findOne(['order_id' => $value['order_id']]);
			if (!$orderErrand) {
				continue;
			}
			$cate_data                    = CateListHelper::getCateListName($value['cate_id'], $orderErrand->errand_type);
			$list[$key]['order_no']       = $value['order_no'];
			$list[$key]['order_id']       = $value['order_id'];
			$list[$key]['start_address']  = $value['start_address'];
			$list[$key]['end_address']    = $value['end_address'];
			$list[$key]['order_time']     = date('Y-m-d H:i:s', $value['create_time']);
			$list[$key]['order_amount']   = $value['order_amount'];
			$list[$key]['status']         = isset($order_status['status']) ? $order_status['status'] : 0;
			$list[$key]['status_message'] = isset($order_status['status_message']) ? $order_status['status_message'] : null;
			$list[$key]['cate_name']      = isset($cate_data['cate_name']) ? $cate_data['cate_name'] : null;
			$list[$key]['cate_id']        = isset($cate_data['cate_id']) ? $cate_data['cate_id'] : null;
			$list[$key]['content']        = $orderErrand->errand_content;
		}


		$result['list'] = $list;

		return $result;
	}

	public static function getOrderStatus($status, $robbed)
	{
		$result = [];
		if ($status == 5 && $robbed == 0) {
			$result['status']         = 1;
			$result['status_message'] = '发布中';
		} elseif ($status == 5 && $robbed == 1) {
			$result['status']         = 2;
			$result['status_message'] = '进行中';
		} elseif ($status == 6) {
			$result['status']         = 3;
			$result['status_message'] = '未评价';
		} elseif ($status == 7) {
			$result['status']         = 4;
			$result['status_message'] = '已评价';
		} elseif ($status == 3 || $status == 4 || $status == 9) {
			$result['status']         = 5;
			$result['status_message'] = '取消';
		}

		return $result;
	}

	//小帮出行的订单列表
	public static function getTripList($params)
	{

		$page                  = ($params['page'] >= 1) ? $params['page'] - 1 : 0;
		$page_size             = !empty($params['page_size']) ? $params['page_size'] : 20;
		$status                = in_array($params['status'], [0, 1, 2, 3]) ? $params['status'] : 0;
		$where['user_id']      = $params['user_id'];
		$where['user_deleted'] = 0; //0是正常，1是删除
		$where['cate_id']      = Ref::CATE_ID_FOR_MOTOR;  //出行
		$list                  = [];

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

		$subQuery   = Order::find()->select('order_id')->where($where)->orWhere($where);
		$countQuery = clone $subQuery;
		$count      = $countQuery->count();
		$pagination = new Pagination(['totalCount' => $count, 'page' => $page, 'pageSize' => $page_size]);
		$select     = ['o.order_id', 'order_no', 'cate_id', 'order_status', 'start_address', 'end_address', 'create_time'];
		$subQuery->offset($pagination->offset)->orderBy(['create_time' => SORT_DESC])->limit($pagination->limit);
		$data = Order::find()->select($select)->from(['o' => Order::tableName(), 't2' => $subQuery])->where('o.order_id=t2.order_id')->orderBy(['o.create_time' => SORT_DESC])->asArray()->all();
		if ($data) {
			foreach ($data as $k => $v) {
				$trip = OrderTrip::find()->select(['trip_type', 'trip_status'])->where(['order_id' => $v['order_id']])->one();
				if (!$trip) {
					continue;
				}
				//$list[$k]['order_id']      = $v['order_id'];
				$list[$k]['order_no']      = $v['order_no'];
				$list[$k]['cate_id']       = $v['cate_id'];
				$list[$k]['start_address'] = $v['start_address'];
				$list[$k]['end_address']   = $v['end_address'];
				$list[$k]['create_time']   = date("Y-m-d H:i:s", $v['create_time']);
				$list[$k]['status']        = $status;
				$list[$k]['trip_status']   = $trip['trip_status'];  //出行状态：1.等待接单 2.小帮已接单 3.到达上车点 4.接到乘客 5.安全到达
				$list[$k]['status_text']   = self::getTripStatus($v['order_status'], $trip['trip_status']);

			}
		}

		$result['pagination'] = [
			'page'       => $params['page'],
			'pageSize'   => $page_size,
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];
		$result['list']       = $list;

		return $result;
	}

	//获取出行状态信息
	public static function getTripStatus($orderStatus, $tripStatus)
	{
		switch ($orderStatus) {
			case 0:
				$status = ($tripStatus > 1) ? '正在出行' : '等待接驾';
				break;
			case 1:
				$status = '待支付';
				break;
			case 6:
				$status = '待评价';
				break;
			case 7:
				$status = '已评价';
				break;
			case 3:
			case 4:
			case 9:
				$status = '已取消';
				break;
			default:
				$status = OrderHelper::getOrderType($orderStatus);
				break;
		}

		return $status;
	}


}