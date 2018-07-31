<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/2/8
 */

namespace m\controllers;

use yii\db\Query;
use Yii;

class NoticeController extends ControllerWeb
{
	public function actionIndex($doc)
	{

		if ($doc == '20180208') {
			return $this->renderPartial("20180208");
		}

		if ($doc == '20180301') {
			return $this->renderPartial("20180301");
		}

		if ($doc == '20180307') {
			return $this->renderPartial("20180307");
		}

		if($doc == '20180320'){
			return $this->renderPartial('20180320');
		}

		if($doc == '20180323'){
			return $this->renderPartial('20180323');
		}

		if($doc == '20180326'){
			return $this->renderPartial('20180326');
		}

		return $this->renderPartial("index");
	}

	/**
	 * 文章显示
	 * @return string
	 */
	public function actionView()
	{
		$id   = Yii::$app->request->get('id');
		$data = (new Query())->select('title,content')->where(['id' => $id])->from("wy_posts")->one();

		if ($data) {
			return $this->renderPartial("view", $data);
		}

		return $this->renderPartial("index");
	}
}