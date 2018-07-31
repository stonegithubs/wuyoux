<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace api_worker\modules\v1\controllers;


use api_worker\modules\v1\helpers\StateCode;
use common\components\ControllerAPI;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\DocumentHelper;
use common\traits\APIDocumentTrait;
use Yii;

class DocumentController extends ControllerAPI
{
	use APIDocumentTrait;

}