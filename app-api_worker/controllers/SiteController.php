<?php

namespace api_worker\controllers;

use common\components\ControllerAPI;

/**
 * Site controller
 */
class SiteController extends ControllerAPI
{

	public function actionError()
	{

		$this->_code    = "10000";
		$this->_message = "Interface does not exist";

		return $this->response();
	}

	/**
	 * Displays homepage.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		die("Hello Worker,it's not funny.");
	}
}
