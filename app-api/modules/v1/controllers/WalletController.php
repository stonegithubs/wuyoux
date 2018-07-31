<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api\modules\v1\controllers;


use common\components\Ref;
use common\helpers\payment\WalletHelper;
use Yii;

class WalletController extends ControllerAccess
{
	public function actionRechargeIndex()
	{
		$data        = WalletHelper::rechargeIndex($this->user_id, Ref::BELONG_TYPE_USER);
		$this->_data = $data;

		return $this->response();
	}
}

