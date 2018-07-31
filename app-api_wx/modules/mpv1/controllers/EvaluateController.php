<?php

namespace api_wx\modules\mpv1\controllers;

use api_wx\modules\mpv1\helpers\StateCode;
use common\components\ControllerAPI;
use common\components\Ref;
use common\helpers\orders\EvaluateHelper;
use common\helpers\payment\AlipayHelper;
use common\helpers\security\SecurityHelper;
use yii\web\Controller;
use Yii;

/**
 * Evaluate controller for the `v1` module
 */
class EvaluateController extends ControllerAccess
{
	/**
	 * Renders the index view for the module
	 * @return string
	 */

	/**
	 * 获取评价信息
	 * @return array
	 */
	public function actionGet()
	{
		$params['order_no'] = SecurityHelper::getBodyParam('order_no');
		$data               = EvaluateHelper::getEvaluateList($params);
		if ($data) {
			$this->_data = $data;
		} else {

			$this->setCodeMessage(StateCode::OTHER_EVALUATE);
		}

		return $this->response();
	}

	/**
	 * 保存评价信息
	 * @return array
	 */
	public function actionSave()
	{
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['star_id']     = SecurityHelper::getBodyParam('star_id');
		$params['tag_ids']     = SecurityHelper::getBodyParam('tag_ids');
		$params['eva_content'] = SecurityHelper::getBodyParam('eva_content');

		$data = EvaluateHelper::saveEvaluate($params);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->setCodeMessage(StateCode::OTHER_EVALUATE_SAVE);
		}

		return $this->response();
	}
}
