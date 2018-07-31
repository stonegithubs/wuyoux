<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Ted(林添欢)
 * Date: Ted 2017/7/24
 */

namespace common\helpers\payment;


use common\helpers\HelperBase;
use common\helpers\utils\RegionHelper;
use common\models\agent\AgentApprovalForm;
use common\models\agent\CityPriceAgent;
use common\models\agent\CityPriceTmp;
use common\models\payment\CityPrice;
use yii\base\Exception;
use yii\db\Query;
use Yii;


class CityPriceHelper extends HelperBase
{


	/****更新状态******/
	const IS_UPDATE_NEW    = 0;    //待更新
	const IS_UPDATE_START  = 1;    //已经更新
	const IS_UPDATE_FINISH = 2;    //回落更新

	/******执行状态******/
	const STATUS_WAITE = 0;        //等待审批
	const STATUS_ALLOW = 1;        //允许执行
	const STATUS_STOP  = 2;        //中途终止

	/******类型******/
	const TYPE_NORMAL = 0;    //常规
	const TYPE_TEMP   = 1;    //临时

	/**
	 * 临时调价开始时间点更新
	 */
	public static function updateStarTmpCityPrice()
	{
		$result = 0;
		$model  = CityPriceTmp::find()->where(['is_update' => self::IS_UPDATE_NEW, 'status' => self::STATUS_ALLOW])
			->andFilterWhere(['<', 'start_time', time()])->all();

		foreach ($model as $item) {

			$item->is_update = self::IS_UPDATE_START;
			$data            = $item->attributes;
			$params          = self::_setParams($data);
			$res             = self::updateCityPrice($params);
			if ($res && $item->save()) {
				$result++;
			};
		}

		return $result;

	}

	/**
	 * 临时调价结束时间点更新
	 */
	public static function updateEndTmpCityPrice()
	{

		$result = 0;
		$model  = CityPriceTmp::find()->where(['is_update' => self::IS_UPDATE_START, 'status' => self::STATUS_ALLOW])
			->andFilterWhere(['<', 'end_time', time()])->all();

		foreach ($model as $item) {
			$item->is_update = self::IS_UPDATE_FINISH;
			$updateRes       = self::_saveTmpNormalBackDown($item->approval_id, $item->city_price_id);
			if ($updateRes && $item->save()) {
				$result++;
			};
		}

		return $result;
	}

	/**
	 * 附加临时调价结束时，通过常规表更新数据，价格回落
	 *
	 * @param $approval_id
	 * @param $id_city_price
	 * @return bool
	 */
	private static function _saveTmpNormalBackDown($approval_id, $id_city_price)
	{
		$result = false;
		$model  = CityPriceAgent::findOne(['approval_id' => $approval_id, 'city_price_id' => $id_city_price]);
		if ($model) {
			$model->is_update = self::IS_UPDATE_FINISH;
			$data             = $model->attributes;
			$params           = self::_setParams($data);
			$updateRes        = self::updateCityPrice($params);
			if ($updateRes && $model->save()) {
				$result = true;
			};

		}

		return $result;
	}


	/**
	 * 常规调价执行更新
	 * @param int $is_update
	 * @param int $type
	 * @return int
	 */
	public static function updateNormalCityPrice($is_update = self::IS_UPDATE_NEW, $type = self::TYPE_NORMAL)
	{
		$result = 0;
		$model  = CityPriceAgent::find()->where(['is_update' => $is_update, 'status' => self::STATUS_ALLOW, 'type' => $type])
			->andFilterWhere(['<', 'start_time', time()])->all();

		foreach ($model as $item) {

			$item->is_update = self::IS_UPDATE_FINISH;
			$data            = $item->attributes;
			$params          = self::_setParams($data);
			$updateRes       = self::updateCityPrice($params);
			if ($updateRes && $item->save()) {
				$result++;
			};
		}

		return $result;
	}

	/**
	 * 设置更新字段
	 * @param $data
	 * @return array
	 */
	private static function _setParams($data)
	{
		return [
			'id'                     => $data['city_price_id'],
			'day_time'               => $data['day_time'],
			'day_time_init'          => $data['day_time_init'],
			'day_time_init_price'    => $data['day_time_init_price'],
			'day_time_unit_price'    => $data['day_time_unit_price'],
			'day_range_init'         => $data['day_range_init'],
			'day_range_init_price'   => $data['day_range_init_price'],
			'day_range_unit_price'   => $data['day_range_unit_price'],
			//			'day_service_fee'        => $data['day_service_fee'],	//白天服务费
			//			'day_busy_fee'           => $data['day_busy_fee'],		//白天高峰加价
			'night_time'             => $data['night_time'],
			'night_time_init'        => $data['night_time_init'],
			'night_time_init_price'  => $data['night_time_init_price'],
			'night_time_unit_price'  => $data['night_time_unit_price'],
			'night_range_init'       => $data['night_range_init'],
			'night_range_init_price' => $data['night_range_init_price'],
			'night_range_unit_price' => $data['night_range_unit_price'],
			'night_service_fee'      => $data['night_service_fee'],        //夜间服务费
			//			'night_busy_fee'         => $data['night_busy_fee'],		//夜间高峰加价
		];
	}

	/**
	 * 更新城市价格表
	 * @param $param
	 * @return bool
	 */
	public static function updateCityPrice($param)
	{
		$result = false;
		$modal  = CityPrice::findOne(['id' => $param['id']]);

		if ($modal) {
			$modal->attributes = $param;
			($modal->save()) ? ($result = true) : (Yii::$app->debug->log_info("city_price", $modal->getErrors()));
		}

		return $result;
	}

}