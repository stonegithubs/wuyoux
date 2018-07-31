<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/4
 */

namespace common\helpers\orders;

use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\HelperBase;
use common\helpers\utils\UrlHelper;
use common\models\orders\Order;
use Yii;
use yii\db\Exception;
use yii\db\Query;

class EvaluateHelper extends HelperBase
{

	private $evaluateStarTbl = 'bb_evaluate_star';
	private $evaluateTagTbl  = 'bb_evaluate_tag';


	//获取评价信息
	public static function getEvaluateList($params)
	{
		$result = false;
		//检查订单
		$data = Order::find()->select('cate_id')->where(['order_no' => $params['order_no']])->one();
		if (!empty($data)) {
			$cate_id = $data['cate_id'];

			if ($cate_id == Ref::CATE_ID_FOR_ERRAND) {
//				$cate_id = Ref::CATE_ID_FOR_MOTOR;
			}

			if ($cate_id == Ref::CATE_ID_FOR_ERRAND_BUY
				|| $cate_id == Ref::CATE_ID_FOR_ERRAND_SEND
				|| $cate_id == Ref::CATE_ID_FOR_ERRAND_DO
				|| $cate_id == Ref::CATE_ID_FOR_BIZ_SEND) {
				$cate_id = Ref::CATE_ID_FOR_ERRAND;
			}

			if (!$cate_id) return $result;

			$sql       = "select star_id,star_num,star_title from bb_evaluate_star where cate_id={$cate_id}  limit 5 ";
			$star_list = Yii::$app->db->createCommand($sql)->queryAll();

			if (empty($star_list)) return $result;

			$star_ids = implode(', ', array_column($star_list, 'star_id'));
			$sql      = "select tag_id,star_id,tag_name from bb_evaluate_tag where star_id in ({$star_ids})  ";
			$tag_list = Yii::$app->db->createCommand($sql)->queryAll();

			if (empty($tag_list)) return false;

			$list = [];
			foreach ($star_list as $key => $value) {
				$list['evaluate_star'][$key] = $value;
				foreach ($tag_list as $tk => $tv) {
					if ($value['star_id'] == $tv['star_id']) {
						$list['evaluate_star'][$key]['evaluate_tag'][] = $tv;
					}
				}
			}

			$result = $list;
		}

		return $result;
	}

	/**
	 * @param $params
	 *
	 * @return array |bool
	 */
	public static function saveEvaluate($params)
	{

		$result    = false;
		$sql       = "select count(eva_id) as counts from bb_evaluate where orderid ={$params['order_no']} ";
		$eva_count = Yii::$app->db->createCommand($sql)->queryScalar();

		if ($eva_count) return $result;

		//查看评价星级ID和标签是否正确
		if ($params['tag_ids']) {

			$sql     = "select tag_id from bb_evaluate_tag where star_id ='{$params['star_id']}'";
			$tag_arr = Yii::$app->db->createCommand($sql)->queryAll();

			$tag_ids = explode(',', $params['tag_ids']);
			$tag_arr = array_column($tag_arr, 'tag_id');
			foreach ($tag_ids as $key => $value) {
				if (!in_array($value, $tag_arr))
					return $result;
			}
		}

		$transaction = Yii::$app->db->beginTransaction();
		try {

			$time  = time();
			$filed = '( star_id,tag_ids,orderid,eva_content,create_time )';
			$sql   = "insert into bb_evaluate   {$filed} values ";
			$sql   .= "( '{$params['star_id']}','{$params['tag_ids']}','{$params['order_no']}','{$params['eva_content']}','{$time}'  ) ;";

			$res = Yii::$app->db->createCommand($sql)->execute();

			$res ? $result = true : Yii::error("评价错误!");

			$result &= OrderHelper::updateEvaluate($params['order_no']);

			if ($result) {

				$transaction->commit();
				$result = self::getShareLink($params['order_no']); //查看是否有活动分享
			}
		}
		catch (Exception $e) {

			$transaction->rollBack();
		}

		return $result;
	}

	/**
	 * 获取已评价的评价
	 *
	 * @param $order_no
	 *
	 * @return array|bool
	 */
	public static function getEvaluateInfo($order_no)
	{
		$result = false;
		//订单评价表
		$sql             = "select tag_ids,eva_content,star_id from bb_evaluate where orderid ='{$order_no}'";
		$orderEvaluation = Yii::$app->db->createCommand($sql)->queryOne();
		if ($orderEvaluation) {
			$data                 = [
				'eval_content'      => null,
				'eval_tag'          => null,
				'eval_star'         => null,
				'eval_star_content' => null,
			];
			$data['eval_content'] = isset($orderEvaluation['eva_content']) ? $orderEvaluation['eva_content'] : null;
			$star_id              = $orderEvaluation['star_id'];

			//评价标签
			if (!empty($orderEvaluation['tag_ids'])) {
				$sql     = "select tag_name from bb_evaluate_tag where tag_id in ({$orderEvaluation['tag_ids']}) ";
				$tag_arr = Yii::$app->db->createCommand($sql)->queryAll();

				$tmp = [];
				if ($tag_arr) {
					foreach ($tag_arr as $k => $v) {
						$tmp[] = ['tag_name' => $v['tag_name']];
					}
					$data['eval_tag'] = $tmp;
				}
			}

			//评价星级
			if ($star_id) {
				$sql                       = "select star_num,star_title from bb_evaluate_star where star_id = {$star_id}";
				$starData                  = Yii::$app->db->createCommand($sql)->queryOne();
				$data['eval_star']         = count($starData) > 0 ? $starData['star_num'] : 5;
				$data['eval_star_content'] = count($starData) > 0 ? $starData['star_title'] : "对本次服务非常满意";
			}
			$result = $data;
		}

		return $result;
	}

	//获取评价时间
	public static function getEvaluateTime($order_no)
	{
		$result = false;
		$data   = (new Query())->select("create_time")->from("bb_evaluate")->where(['orderid' => $order_no])->one();

		if ($data) {
			$result = $data['create_time'];
		}

		return $result;
	}

	//获取分享链接信息
	public static function getShareLink($order_no)
	{
		$url    = UrlHelper::wxLink(['lottery/index', 'order_no' => $order_no]);
		$result = ['is_share' => 0, 'share_url' => $url, 'share_title' => '518无忧帮帮周年大礼包', 'share_content' => '无忧帮帮-让网约更方便！帮我买、帮我送、帮我办，手机下单，越下越优惠！', 'share_pic' => Yii::$app->params['wx_domain'] . '/static/lottery/img/img_link.png'];
		$order  = Order::findOne(['order_no' => $order_no]);
		if ($order && $order->cate_id != Ref::CATE_ID_FOR_MOTOR  && $order->create_time > 1526572800 ) {

			ActivityHelper::createTmpActivityRecord($order_no);

			$result['is_share'] =1;
		}
		return $result;
	}
}
