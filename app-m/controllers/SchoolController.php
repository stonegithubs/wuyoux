<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2017/12/19
 */

namespace m\controllers;

use m\helpers\SchoolHelper;
use Yii;

class SchoolController extends ControllerAccess
{
	public $layout = 'school';

	/**
	 * 小帮学堂首页
	 * @return string
	 */
	public function actionIndex()
	{
		$status  = Yii::$app->request->get('status');
		$user_id = $this->user_id;
		//添加记录
		SchoolHelper::addSchoolRecord($user_id);
		//修改阅读状态
		if ($status == 'base' || $status == 'errand' || $status == 'trip' || $status == 'biz') {
			SchoolHelper::updateStatus($status, $user_id);
		}
		//查询学堂记录
		$record_status = SchoolHelper::findSchoolRecord($user_id);

		return $this->render('index', $record_status);
	}

	/**
	 * 新手入门基础
	 * @return string
	 */
	public function actionBase()
	{
		$view    = Yii::$app->request->get('view');
		$user_id = $this->user_id;
		//查询学堂记录
		$record_status = SchoolHelper::findSchoolRecord($user_id);

		return $this->render($view, $record_status);
	}

	/**
	 * 小帮出行订单
	 * @return string
	 */
	public function actionMotocycle()
	{
		$view = Yii::$app->request->get('view');

		return $this->render($view);
	}

	/**
	 * 小帮快送订单
	 * @return string
	 */
	public function actionErrand()
	{
		$view = Yii::$app->request->get('view');

		return $this->render($view);
	}

	/**
	 * 企业送订单
	 * @return string
	 */
	public function actionBiz()
	{
		$view = Yii::$app->request->get('view');

		return $this->render($view);
	}
}