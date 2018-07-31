<?php

namespace api\controllers;

use common\components\ControllerAPI;

/**
 * Site controller
 */

use common\helpers\orders\ErrandHelper;
use Yii;

class SiteController extends ControllerAPI
{

	/**
	 * Displays homepage.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		die("Hello wx,it's not funny.");
	}
}
