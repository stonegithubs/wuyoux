<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/7/31
 */

namespace api_worker\modules\v1\api;

use common\components\Ref;
use common\helpers\activity\ActivityHelper;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\orders\CateListHelper;
use common\helpers\payment\WalletHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\AMapHelper;
use common\helpers\utils\UtilsHelper;
use common\models\activity\Activity;
use common\models\activity\ActivityGift;
use common\models\activity\ActivityGiftRecord;
use common\models\orders\Order;
use Yii;

class ActivityAPI extends HelperBase
{

	/**
	 * 生成临时礼包记录
	 * @param $provider_id
	 * @param $app_data
	 * @return array|bool|string
	 */
	public static function getPackageV10($provider_id, $user_id)
	{
		//1.查询活动池是否有记录，无则返回错误码
		//2.生成对应的未领取记录
		$result                = [];
		$params['order_no']    = SecurityHelper::getBodyParam('order_no');
		$params['provider_id'] = $provider_id;
		$params['user_id']     = $user_id;

		#判断是否已存在记录，存在则返回当前记录，防止记录过剩
		$existRecord = ActivityGiftRecord::findOne(['order_no'=>$params['order_no'],"user_id"=>$user_id]);
		if(!$existRecord){
			$add 					= ActivityHelper::getPackage($params);
			if($add)	$result['record_id'] = $add;
		}elseif ($existRecord->status == 0 && $existRecord){
			$result['record_id'] 	= $existRecord->id;
		}
		return $result?$result:false;
	}


	/**
	 * 打开红包
	 * @param $provider_id
	 * @param $app_data
	 * @return array
	 */
	public static function openPackageV10($provider_id, $user_id)
	{
		//1.查询对应的领取记录，无责返回错误码
		//2.修改对应的领取记录表
		$record_id = SecurityHelper::getBodyParam('record_id', 0);
		return ActivityHelper::openPackage($record_id,$provider_id,$user_id);
	}

}