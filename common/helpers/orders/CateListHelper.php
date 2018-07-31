<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/24
 */

namespace common\helpers\orders;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;


class CateListHelper extends HelperBase
{

	const cateListTbl = 'bb_catelist';

	public static function getCateList()
	{
		return (new Query())->from(self::cateListTbl)->where("re_sort!=''")->all();
	}

	public static function getHomeCategory()
	{
		$result = false;
		$data   = self::getCateList();
		$tmp    = [];
		foreach ($data as $k => $v) {
			$type = 0;
			$pid  = $v['pid'];
			if ($pid == 46 && $v['id'] == 132)
				$type = 3;

			if ($pid == 46 && $v['id'] != 132)
				$type = 1;

			if ($v['id'] == '133' || $v['id'] == '55' || $v['id'] == '2' || $v['id'] == '54') {
				$type = 5;
			}

			$tmp[] = [
				'id'      => $v['id'],
				'name'    => $v['name'],
				'hot'     => $v['hot'],
				'type'    => $type,
				're_sort' => $v['re_sort'],
				'catepic' => ImageHelper::getCateImageUrl($v['icon'])
			];
		}
		//TODO 临时添加的分类 后台做好功能后，可以不用这样写。
//		$tmp[] = [
//			'id'      => 1,
//			'name'    => '手机充值',
//			'hot'     => 0,
//			'type'    => 4,
//			're_sort' => 7,
//			'catepic' => "http://img01.281.com.cn/cateList/Uploads/Picture/2017-07-24/59760a934d.png"
//
//		];

		ArrayHelper::multisort($tmp, 're_sort');

		$result = $tmp;

		return $result;
	}


	public static function getHomeCate($params)
	{
		//1、查数据表
		//1.1、设置缓存
		//2、过滤数据

		$app_version_key = "app_version_" . $params['app_type'] . $params['app_version'];
		$data            = Yii::$app->cache->get($app_version_key);
		if (!$data) {
			$app_data        = (new Query())
				->from("bb_51_appupdate")
				->where(['tool' => $params['app_type'], 'versionname' => $params['app_version'], 'status' => 3])
				->orderBy("time desc")->one();
			$data['applied'] = 'false';
			if ($app_data)
				$data['applied'] = 'true';

			Yii::$app->cache->set($app_version_key, $data, 3600);
		}

		$list = self::getHomeCategory();

		if (isset($data['applied']) && $data['applied'] == 'true') {
			//过滤

			foreach ($list as $k => $v) {
				if ($v['id'] == '133' || $v['id'] == '55' || $v['id'] == '2' || $v['id'] == '54') {
					$list[$k]['type'] = 3;
					unset($list[$k]);    //移除开发中的内容
				}
			}
		}

		return $list;
	}

	public static function getCateName($ids, $split = ' ')
	{
		$result = false;
		if ($ids) {
			$ids      = explode(",", $ids);
			$listData = (new Query())->select("id,name")->from('bb_catelist')->where(['in', 'id', $ids])->all();
			if ($listData) {
				$tmp = [];
				foreach ($listData as $item) {

					$tmp[] = $item['name'];
				}

				$result = implode($split, $tmp);
			}
		}

		return $result;
	}

	/**
	 * 获取订单列表-订单分类名称
	 *
	 * @param      $cate_id
	 * @param bool $second_type
	 *
	 * @return string|bool
	 */
	public static function getOrderListCateName($cate_id, $second_type = false)
	{
		$result = "其他";

		if ($cate_id == Ref::CATE_ID_FOR_MOTOR) {//摩的 //TODO 缓存
//			$cate = (new Query())->select(['name', 'pid'])->from('bb_catelist')->where(['id' => $cate_id])->one();
//			if ($cate) {
//				$first_cate = (new Query())->select('name')->from('bb_catelist')->where(['id' => $cate['pid']])->one();
//				$result     = $first_cate['name'] . '|' . $cate['name'];
//			}

			$result = '交通出行|小帮出行';
		} else if ($cate_id = Ref::CATE_ID_FOR_ERRAND) { //小帮快送

//			$cate = (new Query())->select('name')->from('bb_catelist')->where(['id' => $cate_id])->one();
//			if ($cate['name']) {
//				switch (intval($second_type)) {
//					case 1:
//						$result = $cate['name'] . '|帮我买';
//						break;
//					case 2:
//						$result = $cate['name'] . '|帮我送';
//						break;
//					case 3:
//						$result = $cate['name'] . '|帮我办';
//						break;
//				}
//			}

			$result = '小帮快送|' . ErrandHelper::getErrandType($second_type);
		}

		return $result;
	}

	public static function NameByCateId($cate_id)
	{
		switch ($cate_id) {
			case Ref::CATE_ID_FOR_MOTOR:
				$result = "小帮出行";
				break;
			case Ref::CATE_ID_FOR_ERRAND_BUY:
				$result = "帮我买";
				break;
			case Ref::CATE_ID_FOR_ERRAND_SEND:
				$result = "帮我送";
				break;
			case Ref::CATE_ID_FOR_ERRAND_DO:
				$result = "帮我办";
				break;
			case Ref::CATE_ID_FOR_BIZ_SEND:
				$result = "企业送";
				break;
			default:
				$result = "其他";
				break;
		}

		return $result;
	}

	public static function getCateListName($cate_id, $second_type)
	{
		$result              = [];
		$result['cate_name'] = "其他";
		$result['cate_id']   = 0;
		if ($cate_id == Ref::CATE_ID_FOR_MOTOR) {//摩的 //TODO 缓存
			$result['cate_name'] = '交通出行|小帮出行';
			$result['cate_id']   = Ref::CATE_ID_FOR_MOTOR;

		} else if ($cate_id == Ref::CATE_ID_FOR_ERRAND) { //小帮快送
			$result['cate_name'] = '小帮快送|' . ErrandHelper::getErrandType($second_type);
			switch ($second_type) {
				case 1:
					$result['cate_id'] = Ref::CATE_ID_FOR_ERRAND_BUY;
					break;
				case 2:
					$result['cate_id'] = Ref::CATE_ID_FOR_ERRAND_SEND;
					break;
				case 3:
					$result['cate_id'] = Ref::CATE_ID_FOR_ERRAND_DO;
					break;
				default:
			}
		} elseif ($cate_id == Ref::CATE_ID_FOR_ERRAND_BUY) {
			$result['cate_name'] = '小帮快送|帮我买';
			$result['cate_id']   = Ref::CATE_ID_FOR_ERRAND_BUY;
		} elseif ($cate_id == Ref::CATE_ID_FOR_ERRAND_SEND) {
			$result['cate_name'] = '小帮快送|帮我送';
			$result['cate_id']   = Ref::CATE_ID_FOR_ERRAND_SEND;
		} elseif ($cate_id == Ref::CATE_ID_FOR_ERRAND_DO) {
			$result['cate_name'] = '小帮快送|帮我办';
			$result['cate_id']   = Ref::CATE_ID_FOR_ERRAND_DO;
		} elseif ($cate_id == Ref::CATE_ID_FOR_BIZ_SEND) {
			$result['cate_name'] = '小帮快送|企业送';
			$result['cate_id']   = Ref::CATE_ID_FOR_BIZ_SEND;
		} elseif ($cate_id == Ref::CATE_ID_FOR_BIZ_SEND_TMP) {
			$result['cate_name'] = '小帮快送|企业送';
			$result['cate_id']   = Ref::CATE_ID_FOR_BIZ_SEND_TMP;
		}

		return $result;
	}

	public static function getWyCateName($cate_id)
	{
		$result = "其他";
		switch ($cate_id) {
			case Ref::CATE_ID_FOR_ERRAND_BUY:
				$result = "帮我买";
				break;
			case Ref::CATE_ID_FOR_ERRAND_SEND:
				$result = "帮我送";
				break;
			case Ref::CATE_ID_FOR_ERRAND_DO:
				$result = "帮我办";
				break;
			case Ref::CATE_ID_FOR_MOTOR:
				$result = "小帮出行";
				break;
			default:

		}

		return $result;
	}

	/**
	 * 获取分类名称
	 */
	public static function getCategoryName($cate_id)
	{
		$cate = (new Query())->select("name")->from('bb_catelist')->where(['id' => intval($cate_id)])->one();

		return $cate['name'] ? $cate['name'] : "未知";
	}


}
