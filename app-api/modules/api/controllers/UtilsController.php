<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/17
 */

namespace api\modules\api\controllers;

use common\helpers\security\SecurityHelper;
use common\helpers\utils\RegionHelper;
use Yii;
use common\helpers\sms\SmsHelper;

class UtilsController extends ControllerAccess
{
	//短信通知
	public function actionNoticeSms()
	{
		$type   = SecurityHelper::getBodyParam("type");
		$mobile = SecurityHelper::getBodyParam("mobile");
		switch ($type) {
			case "shopVerifySuccess": //小帮审核成功
				SmsHelper::sendShopVerify($mobile, 1);
				break;
			case "shopVerifyFail"://小帮审核失败
				SmsHelper::sendShopVerify($mobile, 2);
				break;
			case "BizVerifySuccess"://企业送审核成功
				SmsHelper::sendBizVerify($mobile, 1);
				break;
			case "BizVerifyFail"://企业送审核失败
				SmsHelper::sendBizVerify($mobile, 2);
				break;
			case "DrawFail"://商家提现失败
				SmsHelper::sendBailNotice($mobile, 1);
				break;
			case "BailFail"://保证金解冻失败
				SmsHelper::sendBailNotice($mobile, 2);
				break;
		}
	}


	/**
	 * 清除城市费率的缓存
	 * @return array
	 */
	public function actionCityPriceClearCache()
	{
		$data        = RegionHelper::clearCityPriceCache();
		$this->_data = $data;

		return $this->response();
	}

	/**
	 * 清除开通城市的缓存
	 * @return array
	 */
	public function actionCityOpeningClearCache()
	{
		$data        = RegionHelper::clearCityOpeningCache();
		$this->_data = $data;

		return $this->response();
	}
}