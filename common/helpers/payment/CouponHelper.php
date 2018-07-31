<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace common\helpers\payment;


use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\orders\OrderHelper;
use common\models\orders\Order;
use Yii;
use yii\data\Pagination;
use yii\db\Query;

class CouponHelper extends HelperBase
{
	const cardTbl     = 'bb_card';
	const cardUserTbl = 'bb_card_user';


	/**
	 * @param        $user_id
	 * @param string $type
	 *
	 * @return false|null|string
	 */
	public static function getAvailableCount($user_id, $type = '')
	{
		return self::getCardNum($user_id, $type, 'available');
	}

	public static function getList($user_id, $type = Ref::ORDER_TYPE_ERRAND, $status = null)
	{

		$result = false;
		$data   = self::getCardList($user_id, $type, $status);

		if ($status != null && is_array($data)) {
			$result = self::comb($data);
		} else {
			$result = [
				'available'   => self::comb($data['available']),
				'unavailable' => self::comb($data['unavailable'])
			];
		}

		return $result;
	}

	/**
	 * 组合优惠券列表
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private static function comb($data)
	{
		if (is_array($data)) {
			$tmp = [];
			foreach ($data as $value) {
				$value['type_name']  = self::getTypeName($value['typeid']);
				$value['type']       = $value['typeid'];
				$value['end_time']   = date("Y-m-d", $value['end_time']);
				$value['start_time'] = date("Y-m-d", $value['start_time']);
				$tmp[]               = $value;
			}

			return $tmp;
		}
	}

	/**
	 * 获取可用/不可用卡券
	 *
	 * @param        $user_id
	 * @param string $type
	 */
	/**
	 * @param      $user_id
	 * @param int  $type
	 * @param null $status
	 *
	 * @return array|false|null|string
	 */
	private static function getCardList($user_id, $type = Ref::ORDER_TYPE_ERRAND, $status = null)
	{
		$cardUser = self::cardUserTbl;
		$card     = self::cardTbl;
		$time     = time();

		$cate_id    = 0;
		$un_cate_id = 0;
		//摩的专车
		if ($type == Ref::ORDER_TYPE_TRIP) {
			$cate_id    = Ref::CATE_ID_FOR_MOTOR;
			$un_cate_id = Ref::CATE_ID_FOR_ERRAND;
		}

		//小帮快送
		if ($type == Ref::ORDER_TYPE_ERRAND) {
			$cate_id    = Ref::CATE_ID_FOR_ERRAND;
			$un_cate_id = Ref::CATE_ID_FOR_MOTOR;
		}

		$filed = "c.city,cu.end_time,c.name,c.start_time ,c.rule ,cu.price,c.int_shop,c.extend,c.instruction,cu.id as card_id,cu.uid,c.typeid ";

		$where = "cu.`uid` = {$user_id} AND cu.`end_time` > {$time} AND cu.`status` = 0 AND cu.`visible` = 1  AND c.belong_type !=" . Ref::BELONG_TYPE_BIZ;
		//可用的
		$aSql = "SELECT {$filed} FROM {$cardUser} AS cu LEFT JOIN {$card} AS c ON cu.`c_id` = c.`id` WHERE ";
		$aSql .= "{$where} AND c.`second_category` IN (0, {$cate_id})";


		//不可用的
		$uSql = "SELECT {$filed}  FROM {$cardUser} AS cu LEFT JOIN {$card} AS c ON cu.`c_id` = c.`id` WHERE ";
		$uSql .= "{$where} AND c.`second_category` = {$un_cate_id} ";

		$db = Yii::$app->db;

		if ($status == 'available') {

			return $db->createCommand($aSql)->queryAll();

		} elseif ($status == 'unavailable') {

			return $db->createCommand($uSql)->queryAll();

		} else {

			$available   = $db->createCommand($aSql)->queryAll();
			$unavailable = $db->createCommand($uSql)->queryAll();
		}

		return [
			'available'   => $available,
			'unavailable' => $unavailable
		];
	}

	/**
	 * 统计用户可用/不可用的卡券数量
	 *
	 * @param      $user_id
	 * @param int  $type
	 * @param null $status
	 *
	 * @return array|false|null|string
	 */
	public static function getCardNum($user_id, $type = Ref::ORDER_TYPE_ERRAND, $status = null)
	{
		$cardUser   = self::cardUserTbl;
		$card       = self::cardTbl;
		$time       = time();
		$cate_id    = 0;
		$un_cate_id = 0;
		//摩的专车
		if ($type == Ref::ORDER_TYPE_TRIP) {
			$cate_id    = Ref::CATE_ID_FOR_MOTOR;
			$un_cate_id = Ref::CATE_ID_FOR_ERRAND;
		}

		//小帮快送
		if ($type == Ref::ORDER_TYPE_ERRAND) {
			$cate_id    = Ref::CATE_ID_FOR_ERRAND;
			$un_cate_id = Ref::CATE_ID_FOR_MOTOR;
		}

		$where = "cu.`uid` = {$user_id} AND cu.`end_time` > {$time} AND cu.`status` = 0 AND cu.`visible` = 1";
		//可用的
		$aSql = "SELECT COUNT(cu.id) FROM {$cardUser} AS cu LEFT JOIN {$card} AS c ON cu.`c_id` = c.`id` WHERE ";
		$aSql .= "{$where} AND c.`second_category` IN (0, {$cate_id}) LIMIT 1";


		//不可用的
		$uSql = "SELECT COUNT(cu.id) FROM {$cardUser} AS cu LEFT JOIN {$card} AS c ON cu.`c_id` = c.`id` WHERE ";
		$uSql .= "{$where} AND c.`second_category` = {$un_cate_id} LIMIT 1";

		$db = Yii::$app->db;

		if ($status == 'available') {

			return $db->createCommand($aSql)->queryScalar();

		} elseif ($status == 'unavailable') {

			return $db->createCommand($uSql)->queryScalar();

		} else {

			$available   = $db->createCommand($aSql)->queryScalar();
			$unavailable = $db->createCommand($uSql)->queryScalar();
		}


		return [
			'available'   => $available,
			'unavailable' => $unavailable
		];
	}


	//根据优惠券id获取订单值
	public static function getCardAmount($card_id)
	{
		$amount = 0;
		if ($card_id > 0) {
			$data = (new Query())->select("price")->from(self::cardUserTbl)->where(['id' => $card_id, 'status' => Ref::CARD_STATUS_NEW])->one();

			if (is_array($data)) {
				$amount = $data['price'];
			}
			//TODO 根据不同优惠券的类型 做处理 如 折扣劵
		}

		return $amount;
	}

	/**
	 * 更新卡券
	 *
	 * @param $card_id
	 * @param $card_amount
	 *
	 * @return int
	 */
	public static function updateCard($card_id, $card_amount)
	{

		$result   = false;
		$use_time = time();
		if ($card_id > 0) {

			$sql = "update  " . self::cardUserTbl . " set actual_price=" . $card_amount;
			$sql .= ", use_time=" . $use_time;
			$sql .= " where id=" . $card_id;

			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("update card failed");
		}

		return $result;
	}

	//使用卡券
	public static function beUseCard($card_id)
	{
		$result   = false;
		$use_time = time();
		if ($card_id > 0) {

			$sql = "update  " . self::cardUserTbl . " set status=" . Ref::CARD_STATUS_USED . ", use_time=" . $use_time;
			$sql .= " where id=" . $card_id;

			Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::error("update card failed");
		}

		return $result;
	}

	/**
	 * 获取优惠劵名字
	 *
	 * @param $typeId
	 *
	 * @return string
	 */
	public static function getTypeName($typeId)
	{
		$return = '0';
		switch ($typeId) {
			case Ref::COUPON_TYPE_DISCOUNT:
				$return = '折用券';
				break;
			case Ref::COUPON_TYPE_DEDUCTIBLE:
				$return = '抵扣券';
				break;
			case Ref::COUPON_TYPE_FREE:
				$return = '免单券';
				break;
			case Ref::COUPON_TYPE_SHOP:
				$return = '商家券';
				break;
			case Ref::COUPON_TYPE_ALL:
				$return = '通用券';
				break;
		}

		return $return;
	}


	//显示下单时可用的优惠券
	public static function showOrderCard($order_no)
	{

		$result = false;
		$model  = Order::findOne(['order_no' => $order_no]);
		if ($model) {
			$city_id      = $model->city_id;
			$area_id      = $model->area_id;
			$order_amount = $model->order_amount;
			$user_id      = $model->user_id;
			$order_type   = $model->order_type;
			$data         = self::getList($user_id, $order_type, 'available');
			$new_list     = [];
			foreach ($data as $rows) {

				//判断是否满足 满减值[extend] 城市类型
				$extend = $rows['extend'];
				if ($order_amount >= $extend) {
					//城市
					$card_city = $rows['city'];
					if ($card_city > 0 && $card_city != $city_id) {    //非城市地区不可用
						continue;
					}

					$card_limit = "无门槛";
					if ($rows['extend'] > 0) {
						$card_limit = "满" . $rows['extend'] . "元可减";
					}
					$rows['extend'] = $card_limit;

					if ($card_city > 0 && ($card_city == $city_id || $area_id == $card_city)) {
						$new_list[] = $rows;
					} else {    //不限城市 肯定是通用的
						$new_list[] = $rows;
					}

				}
			}
			$result = count($new_list) > 0 ? $new_list : false;
		}

		return $result;
	}

	/**
	 * 获取订单可用优惠券数量
	 *
	 * @param $order_no
	 *
	 * @return int
	 */
	public static function getOrderCardNum($order_no)
	{
		$result = 0;
		$res    = self::showOrderCard($order_no);
		if ($res)
			$result = count($res);

		return $result;
	}

	//退款的时候，把优惠券退回,并生成新的卡券
	public static function refundUserCard($card_id, $order_id)
	{

		$result = true;
		if ($card_id) {

			//取消订单如果客户有使用优惠券，优惠券更新为退回状态，并新建一条优惠券记录
			$data = (new Query())->select("*")->from(self::cardUserTbl)->where(['id' => $card_id])->one();
			if ($data) {
				//保存
				$sql = "update  " . self::cardUserTbl . " set status=" . Ref::CARD_STATUS_RETURN;
				$sql .= " where id=" . $card_id;
				Yii::$app->db->createCommand($sql)->execute() ? $result = true : Yii::$app->debug->log_info("refundUserCard", "更新失败" . $card_id);
				//新建
				$filed        = '(uid,num,c_id,price,get_time,end_time,status,way,visible)';
				$sql          = "insert into bb_card_user   {$filed} values ";
				$sql          .= "( '{$data['uid']}','1','{$data['c_id']}','{$data['price']}','{$data['get_time']}','{$data['end_time']}',0,'{$data['way']}',1 ) ;";
				$result       &= Yii::$app->db->createCommand($sql)->execute();
				$user_card_id = Yii::$app->db->getLastInsertID();
				$result       &= OrderHelper::saveLogContent($order_id, "new_card_id", $user_card_id, "新增给用户的card_id");
			}
		}

		return $result;
	}

	/**
	 * 优惠券列表
	 * @param     $params
	 * @return bool
	 */
	public static function CardList($params)
	{
		$result       = false;
		$user_id      = $params['user_id'];
		$current_page = !empty($params['page']) ? intval($params['page']) : 1;                 //分页属性从0开始的
		$pageSize     = !empty($params['page_size']) ? intval($params['page_size']) : 20;    //每页数量
		$nowTime      = time();

		$where = "cu.end_time > {$nowTime} AND cu.status = 0 AND cu.visible = 1  AND cu.uid = {$user_id} AND c.belong_type = " . Ref::BELONG_TYPE_USER;
		$count = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")->select('cu.id')->where($where)->count();

		$page = 0;
		if ($current_page > 0) {
			$page = $current_page - 1;
		}
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $pageSize;
		$select               = "c.name,c.end_time as card_time,c.typeid,c.rule,c.extend,cu.end_time as user_card_time,cu.id,cu.price,cu.get_time";
		$cardData             = (new Query())
			->from(self::cardUserTbl . ' AS cu')
			->leftJoin(self::cardTbl . " AS c", " cu.c_id=c.id")
			->select($select)
			->where($where)
			->offset($pagination->offset)
			->orderBy(['cu.end_time' => SORT_ASC])
			->limit($pagination->limit)
			->all();

		$list = [];

		if (count($cardData) > 0) {
			foreach ($cardData as $key => $value) {
				$card_limit = "无门槛";
				if ($value['extend'] > 0) {
					$card_limit = "满" . $value['extend'] . "元可减";
				}
				$list[$key]['start_time']   = date("Y-m-d", $value['get_time']);
				$list[$key]['end_time']     = date("Y-m-d", $value['user_card_time']);
				$list[$key]['card_name']    = $value['name'];
				$list[$key]['card_money']   = $value['price'];
				$list[$key]['user_card_id'] = $value['id'];
				$list[$key]['card_type']    = self::getTypeName($value['typeid']);
				$list[$key]['card_rule']    = $value['rule'];
				$list[$key]['card_limit']   = $card_limit;
			}
			$result['pagination']
							= [
				'page'       => $current_page,
				'pageSize'   => $pageSize,
				'pageCount'  => $pagination->pageCount,
				'totalCount' => $pagination->totalCount,
			];
			$result['list'] = $list;
		}


		return $result;

	}

	/**
	 * 企业送优惠券列表
	 * @param $user_id
	 * @return bool
	 */
	public static function bizCardDataByAuto($user_id, $price, $card_list = [])
	{
		$nowTime = time();
		$select  = ['c.id as parent_id', 'cu.end_time', 'cu.price', 'cu.extend', 'cu.get_time', 'c.name', 'c.typeid', 'c.rule', 'cu.id as card_id', 'c.city'];
		$where   = "cu.end_time > {$nowTime} and cu.uid = {$user_id} ";// AND belong_type=" . Ref::CARD_BELONG_BIZ;
		$where   .= " and cu.status = 0 and cu.visible = 1   and cu.price <=" . $price;
		if ($card_list) {
			$where .= " and cu.id not in (" . implode(",", $card_list) . ")";
		}
		$cardData = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")
			->select($select)->where($where)->orderBy("cu.price desc,cu.end_time")->one();

		return $cardData ? $cardData : false;
	}


	/**
	 * 企业送可用优惠券组
	 * @param $user_id
	 * @return array
	 */
	public static function bizCardAvailableData($user_id)
	{
		$result         = [];
		$nowTime        = time();
		$select         = "c.name,c.typeid,c.rule,c.extend,cu.end_time as user_card_time,cu.id,cu.price,cu.get_time,count(cu.c_id) as card_count,c.id as parent_id,c.belong_type";
		$where          = "cu.end_time > {$nowTime} AND cu.status = 0 AND cu.visible = 1  AND cu.uid = {$user_id} ";
		$available_card = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")->select($select)->where($where)->groupBy(['cu.c_id'])->all();
		if ($available_card) {
			foreach ($available_card as $k => $v) {
				$card_limit = "无门槛";
				if ($v['extend'] > 0) {
					$card_limit = "满" . $v['extend'] . "元可减";
				}
				$result[$k]['card_name']   = $v['name'];
				$result[$k]['start_time']  = date("Y-m-d", $v['get_time']);
				$result[$k]['end_time']    = date("Y-m-d", $v['user_card_time']);
				$result[$k]['card_type']   = self::getTypeName($v['typeid']);
				$result[$k]['card_money']  = $v['price'];
				$result[$k]['card_rule']   = $v['rule'];
				$result[$k]['card_limit']  = $card_limit;
				$result[$k]['card_count']  = $v['card_count'];
				$result[$k]['parent_id']   = $v['parent_id'];
				$result[$k]['belong_type'] = $v['belong_type'];
			}
		}

		return $result;
	}

	/**
	 * 企业送不可用优惠券组
	 */
	public static function bizCardUnavailableData($user_id)
	{

		$result           = false;
		$nowTime          = time();
		$select           = "c.name,c.typeid,c.rule,c.extend,cu.end_time as user_card_time,cu.id,cu.price,cu.get_time,count(cu.c_id) as card_count,c.id as parent_id";
		$un_where         = "cu.end_time < {$nowTime} AND cu.status = 0 AND cu.visible = 1  AND cu.uid = {$user_id} ";
		$unavailable_card = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")->select($select)->where($un_where)->groupBy(['cu.c_id'])->all();
		if ($unavailable_card) {
			foreach ($unavailable_card as $k => $v) {
				$card_limit = "无门槛";
				if ($v['extend'] > 0) {
					$card_limit = "满" . $v['extend'] . "元可减";
				}
				$result[$k]['card_name']  = $v['name'];
				$result[$k]['start_time'] = date("Y-m-d", $v['get_time']);
				$result[$k]['end_time']   = date("Y-m-d", $v['user_card_time']);
				$result[$k]['card_type']  = self::getTypeName($v['typeid']);
				$result[$k]['card_money'] = $v['price'];
				$result[$k]['card_rule']  = $v['rule'];
				$result[$k]['card_limit'] = $card_limit;
				$result[$k]['card_count'] = $v['card_count'];
				$result[$k]['parent_id']  = $v['parent_id'];
			}
		}

		return $result;
	}

	/**
	 * 企业送优惠券列表
	 * @param $user_id
	 * @return bool
	 */
	public static function bizCardList($user_id)
	{
		$result = false;

		//可用的优惠券
		$available = self::bizCardAvailableData($user_id);
		$available ? $result['available'] = $available : null;

		//不可用优惠券
		$available = self::bizCardUnavailableData($user_id);
		$available ? $result['unavailable'] = $available : null;

		return $result;
	}

	/**
	 * 获取企业送或者普通用户的优惠券数
	 * @param $user_id
	 * @param $type
	 */
	public static function getCardCount($user_id, $type = null)
	{

		$nowTime = time();
		$select  = "count(cu.c_id) as card_count";
		$where   = "cu.end_time > {$nowTime} AND cu.status = 0 AND cu.visible = 1  AND cu.uid = {$user_id}";
		if ($type) {

			$where .= " AND belong_type=" . $type;
		}
		//可用的优惠券
		$available_card = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")->select($select)->where($where)->one();

		return $available_card['card_count'];
	}

	/**
	 * 显示企业送订单可用优惠券
	 * @param $order_no
	 * @param $user_id
	 * @return bool
	 */
	public static function showBizOrderCard($order_no, $user_id)
	{

		$result = false;
		$model  = Order::findOne(['order_no' => $order_no, 'user_id' => $user_id, 'cate_id' => Ref::CATE_ID_FOR_BIZ_SEND]);
		if ($model) {
			$city_id      = $model->city_id;
			$area_id      = $model->area_id;
			$order_amount = $model->order_amount;

			$now_time  = time();
			$select    = ['cu.id', 'cu.end_time', 'cu.price', 'cu.extend', 'cu.get_time', 'c.name', 'c.typeid', 'c.rule', 'cu.c_id', 'c.city', 'count(cu.id) as card_count'];
			$where     = "cu.end_time > {$now_time} AND cu.status = 0 AND cu.visible = 1  AND cu.uid = {$user_id}  AND c.second_category in(0," . Ref::CATE_ID_FOR_BIZ_SEND . ")";
			$card_data = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")->select($select)->where($where)->orderBy(['cu.end_time' => SORT_ASC])->groupBy(['cu.c_id'])->all();

			$new_list = [];
			foreach ($card_data as $rows) {
				//判断是否满足 满减值[extend] 城市类型
				$extend = $rows['extend'];
				if ($order_amount > $extend) {
					//城市
					$card_city = $rows['city'];
					if ($card_city > 0 && $card_city != $city_id) {    //非城市地区不可用
						continue;
					}
					if ($card_city > 0 && ($card_city == $city_id || $area_id == $card_city)) {
						$new_list[] = $rows;
					} else {    //不限城市 肯定是通用的
						$new_list[] = $rows;
					}

				}
			}

			if (count($new_list) > 0) {
				foreach ($new_list as $k => $v) {
					$card_limit = "无门槛";
					if ($v['extend'] > 0) {
						$card_limit = "满" . $v['extend'] . "元可减";
					}
					$result[$k]['card_id']    = $v['id'];
					$result[$k]['card_name']  = $v['name'];
					$result[$k]['start_time'] = date("Y-m-d", $v['get_time']);
					$result[$k]['end_time']   = date("Y-m-d", $v['end_time']);
					$result[$k]['card_type']  = self::getTypeName($v['typeid']);
					$result[$k]['card_money'] = $v['price'];
					$result[$k]['card_rule']  = $v['rule'];
					$result[$k]['card_limit'] = $card_limit;
					$result[$k]['card_count'] = $v['card_count'];
				}
			}

		}

		return $result;
	}

	/**
	 * 查找选择出来的卡券
	 * @param $selectCard
	 * @param $user_id
	 * @return array
	 */
	public static function bizCardListForSmart($selectCard, $user_id)
	{
		$result  = [];
		$nowTime = time();
		if ($selectCard)
			foreach ($selectCard as $item) {
				$parent_id = $item['parent_id'];
				$limit     = $item['num'];

				$select   = ['c.id as parent_id', 'cu.price', 'cu.id as card_id'];
				$where    = "cu.end_time > {$nowTime} and cu.uid = {$user_id} "; // AND belong_type=" . Ref::CARD_BELONG_BIZ;
				$where    .= " and cu.status = 0 and cu.visible = 1   and c.id =" . $parent_id;
				$cardData = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")
					->select($select)->where($where)->limit($limit)->all();
				if ($cardData) {
					$result = array_merge($result, $cardData);
				}
			}

		return $result;
	}


	//优惠券列表
	//1.1版本
	public static function CardListV11($params)
	{
		$result       = false;
		$user_id      = $params['user_id'];
		$current_page = !empty($params['page']) ? intval($params['page']) : 1;                 //分页属性从0开始的
		$pageSize     = !empty($params['page_size']) ? intval($params['page_size']) : 20;    //每页数量
		$nowTime      = time();

		$where = "cu.end_time > {$nowTime} AND cu.status = 0 AND cu.visible = 1  AND cu.uid = {$user_id} AND c.belong_type = " . Ref::BELONG_TYPE_USER;
		$count = (new Query())->from(self::cardUserTbl . ' AS cu')->leftJoin(self::cardTbl . " AS c", "cu.c_id=c.id")->select('cu.id')->where($where)->count();

		$page = 0;
		if ($current_page > 0) {
			$page = $current_page - 1;
		}
		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->page     = $page;
		$pagination->pageSize = $pageSize;
		$select               = "c.name,c.end_time as card_time,c.typeid,c.rule,c.extend,cu.end_time as user_card_time,cu.id,cu.price,cu.get_time";
		$cardData             = (new Query())
			->from(self::cardUserTbl . ' AS cu')
			->leftJoin(self::cardTbl . " AS c", " cu.c_id=c.id")
			->select($select)
			->where($where)
			->offset($pagination->offset)
			->orderBy(['cu.end_time' => SORT_ASC])
			->limit($pagination->limit)
			->all();

		$list = [];

		if (count($cardData) > 0) {
			foreach ($cardData as $key => $value) {
				$card_limit = "无门槛";
				if ($value['extend'] > 0) {
					$card_limit = "满" . $value['extend'] . "元可减";
				}
				$list[$key]['start_time']   = date("Y-m-d", $value['get_time']);
				$list[$key]['end_time']     = date("Y-m-d", $value['user_card_time']);
				$list[$key]['card_name']    = $value['name'];
				$list[$key]['card_money']   = $value['price'];
				$list[$key]['user_card_id'] = $value['id'];
				$list[$key]['card_type']    = self::getTypeName($value['typeid']);
				$list[$key]['card_rule']    = $value['rule'];
				$list[$key]['card_limit']   = $card_limit;
			}

		}
		$result['pagination']
						= [
			'page'       => $current_page,
			'pageSize'   => $pageSize,
			'pageCount'  => $pagination->pageCount,
			'totalCount' => $pagination->totalCount,
		];
		$result['list'] = $list;

		return $result;

	}


	/**
	 * 用户领取卡券
	 * @param $card_id
	 * @param $user_id
	 * @return bool
	 */
	public static function userGetCard($user_id, $card_id, $way = false)
	{
		$result = false;
		//查询对应的卡券记录
		$card_modal      = self::cardTbl;
		$user_card_modal = self::cardUserTbl;
		$sql             = "SELECT * FROM {$card_modal} where id = {$card_id} limit 1";
		$card            = Yii::$app->getDb()->createCommand($sql)->queryOne();
		if ($card) {
			$now            = time();
			$effective_time = $now + $card['effective_time'];
			$way            = (!empty($way) ? $way : 2);
			//添加记录

			$value = "'{$user_id}','{$card_id}','{$now}','{$effective_time}','{$card['price']}','{$way}'";
			$sql   = "INSERT INTO {$user_card_modal} (`uid`,`c_id`,`get_time`,`end_time`,`price`,`way`) VALUE ($value)";
			$add   = Yii::$app->getDb()->createCommand($sql)->execute();
			if ($add) $result = ['card_amount' => $card['price']];
		}

		return $result;
	}


	/**
	 * 获取最大的可用的优惠券金额
	 * @param int $userId 用户id
	 * @param int $cateId 分类id
	 * @param int $orderAmount 订单金额
	 * @return int|mixed 最大优惠金额
	 */
	public static function maxCoupon($userId, $cateId, $orderAmount)
	{
		$discount = 0;         //优惠金额，没有优惠券则为0
		$coupon   = CouponHelper::getList($userId, $cateId, 'available');   //获取可用的优惠券
		//如果有优惠券的话
		if (isset($coupon[0])) {
			$discountArr = [];
			foreach ($coupon as $value) {
				//满足优惠券使用门槛的话
				if ($orderAmount >= $value['extend']) {
					array_push($discountArr, $value['price']);
				}
			}
			if (isset($discountArr[0])) {
				rsort($discountArr);    //按优惠金额降序，取最大优惠金额
				$discount = $discountArr[0];
			}
		}

		if ($discount > 0) {    //只返回最优惠方案

			if ($discount > $orderAmount) {
				$discount = $orderAmount;
			}
		}

		return $discount;
	}

}