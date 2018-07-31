<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/5
 */

namespace m\controllers;

use Yii;

class ApplyController extends ControllerAccess
{


	public function actionIndex(){
		echo "入驻";
		return $this->render("index");

	}


	public function actionBizSend()
	{
	}


}