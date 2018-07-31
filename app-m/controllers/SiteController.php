<?php
namespace m\controllers;

use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends ControllerWeb
{
	public function actionIndex()
	{
		return $this->render('error');
	}

	public function actionError(){
		return $this->renderPartial('error');
	}
}
