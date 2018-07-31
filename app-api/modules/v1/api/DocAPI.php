<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/31
 */

namespace api\modules\v1\api;

use api\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use yii\db\Query;

class DocAPI extends HelperBase
{

	/***
	 * 小帮入驻说明
	 */
	public static function getApplyDocument()
	{
		$city_id = RegionHelper::getCityId(SecurityHelper::getBodyParam('cityname'));//TODO 备用:看市场需求
		$apply_type = SecurityHelper::getBodyParam('apply_type');//入驻类型 TODO	备用:看市场需求
		$data = DocumentHelper::getDocument(Ref::DOCUMENT_APPLY_INFO,0);
	}


}