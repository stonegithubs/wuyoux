<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/5
 */
namespace m\controllers;

use m\helpers\RuleHelper;
use Yii;

/**
 * 规则
 * Class RuleController
 * @package m\controllers
 */
class RuleController extends ControllerWeb
{

	//价格规则 小帮出行规则
	public function actionPriceTripBike()
	{
		$params = [
			'city_id' => Yii::$app->request->get('city_id'),
			'area_id' => Yii::$app->request->get('area_id')
		];

		$cityPrice = RuleHelper::getPriceTripBike($params);
		$city    = RuleHelper::getCity($params['city_id']);
		$data      = ['cityPrice' => $cityPrice, 'city' => $city];

		return $this->renderPartial("price-trip-bike", $data);
	}
}