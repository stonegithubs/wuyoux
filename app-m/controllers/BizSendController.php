<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/1/3
 */

namespace m\controllers;

use common\helpers\utils\UrlHelper;
use m\helpers\BizSendHelper;
use Yii;

class BizSendController extends ControllerAccess
{

	/**
	 * 计价明细
	 * @return string
	 */
	public function actionValuationDetail()
	{
		$params['order_no'] = Yii::$app->request->get('order_no');
		$result             = BizSendHelper::getCalc($params);

		return $this->renderPartial('valuation', $result);
	}


	/**
	 *用过订单号取计价规则
	 * @return string
	 */
	public function actionValuationRule()
	{
		$order_no = Yii::$app->request->get('order_no');
		$result   = BizSendHelper::getRule($order_no);

		return $this->renderPartial('rule', $result);
	}

	/**
	 * 计价规则
	 * 通过User_id取城市规则
	 * @return string
	 */
	public function actionCalculationRule()
	{
		$result = BizSendHelper::getRuleByUserId($this->user_id);

		return $this->renderPartial('rule', $result);
	}

}