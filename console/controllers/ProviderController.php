<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/12/10 12:40
 */

namespace console\controllers;

use common\helpers\users\UserHelper;
use yii\console\Controller;
use Yii;

class ProviderController extends Controller
{

	/**
	 * 初始化接单数
	 */
	public function actionInitPickOrder()
	{
		$sql
			  = "SELECT count(*) as counts,provider_id FROM `wy_order` 
				WHERE `order_status` IN (6,7) and robbed =1 and provider_id >0 group by provider_id  ";
		$data = Yii::$app->getDb()->createCommand($sql)->queryAll();

		if (count($data) > 0) {
			foreach ($data as $item) {

				$now   = time();
				$upSql = "update  bb_51_shops set `pick_order_num` = '{$item['counts']}' , `update_time` ={$now} where id = {$item['provider_id']}";
				Yii::$app->db->createCommand($upSql)->execute();
			}
		}
	}

	/**
	 *
	 */
	public function actionPickOrder()
	{

		$startTime = time() - 86400;
		$endTime   = time();

		$sql
			  = "SELECT count(*) as counts,provider_id FROM `wy_order` 
				WHERE `order_status` IN (6,7) and robbed =1 and provider_id >0 and  finish_time  > {$startTime} and finish_time < {$endTime} group by provider_id  ";
		$data = Yii::$app->getDb()->createCommand($sql)->queryAll();
		if (count($data) > 0) {
			foreach ($data as $item) {

				$provider = UserHelper::getShopInfo($item['provider_id'], 'pick_order_num');

				if ($provider) {
					$pick_order_num = isset($provider['pick_order_num']) ? $provider['pick_order_num'] : 0;
					$pick_order_num += $item['counts'];
					$now            = time();
					$upSql          = "update  bb_51_shops set `pick_order_num` = '{$pick_order_num}' , `update_time` ={$now} where id = {$item['provider_id']}";
					Yii::$app->db->createCommand($upSql)->execute();
				}
			}
		}
	}
}