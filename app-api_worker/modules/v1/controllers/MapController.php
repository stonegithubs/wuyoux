<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/3/20
 */

namespace api_worker\modules\v1\controllers;

use api_worker\modules\v1\helpers\StateCode;
use common\components\ControllerAPI;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use common\traits\APIMapTrait;

class MapController extends ControllerAPI
{

	use APIMapTrait;

	//地区列表
	public function actionRegionList()
	{

		$parentId = SecurityHelper::getBodyParam('parent_id', -1);
		$level    = SecurityHelper::getBodyParam('level', 1);

		$data = RegionHelper::getRegionList($parentId, $level);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->_code = StateCode::OTHER_EMPTY_DATA;
		}

		return $this->response();
	}

}