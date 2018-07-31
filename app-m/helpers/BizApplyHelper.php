<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/1/3
 */

namespace m\helpers;

use common\components\Ref;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use Yii;
use common\helpers\HelperBase;
use common\models\users\BizInfo;
use common\models\util\BizTag;
use yii\db\Query;

class BizApplyHelper extends HelperBase
{
	/**
	 * 获取经营品类
	 * @return array
	 */
	public static function getTagList()
	{
		$result = [];
		$tag    = BizTag::find()->where(['status' => 1])->orderBy(['orders' => SORT_ASC])->all();
		foreach ($tag as $key => $value) {
			$result[$key]['tag_id']   = $value->id;
			$result[$key]['tag_name'] = $value->tag_name;
		}

		return $result;
	}

	/**
	 * 检查用户是否为企业送用户
	 * @param $user_id
	 * @return array|int
	 */
	public static function ApplyCheck($user_id)
	{
		$result   = [];
		$biz_info = BizInfo::findOne(['user_id' => $user_id]);
		if ($biz_info) {
			$result = $biz_info->status;
		}

		return $result;
	}

	/**
	 * 入驻信息处理
	 * @param $user_id
	 * @return mixed
	 */
	public static function Apply($user_id)
	{
		$result['biz_name']        = Yii::$app->request->get('biz_name');
		$result['biz_address_ext'] = Yii::$app->request->get('biz_address_ext');
		$result['biz_mobile']      = Yii::$app->request->get('biz_mobile');
		$result['address']         = Yii::$app->request->get('address');
		$result['location']        = Yii::$app->request->get('location');
		$result['category_tag']    = Yii::$app->request->get('category_tag');
		if ($result['biz_mobile'] == null) {
			$data                 = (new Query())->select('mobile')->from('bb_51_user')->where(['uid' => $user_id])->one();
			$result['biz_mobile'] = $data['mobile'];
		}

		$result['url']      = UrlHelper::webLink('biz-apply/apply-save');
		$result['jump_url'] = UrlHelper::webLink('biz-apply/apply-success');
		$result['map_url']  = UrlHelper::webLink('biz-apply/map');

		return $result;
	}

	/**
	 * 企业送入驻
	 * @param $params
	 * @return bool
	 */
	public static function applySave($params)
	{
		$user_info            = UserHelper::getUserInfo($params['user_id'], 'city_id');
		$address              = RegionHelper::getAddressIdByLocation($params['biz_location'], $user_info['city_id']);
		$address['city_name'] = RegionHelper::getAddressNameById($address['city_id']);
		$address['area_name'] = RegionHelper::getAddressNameById($address['area_id']);

		$biz_data = [
			'biz_name'        => $params['biz_name'],
			'biz_mobile'      => $params['biz_mobile'],
			'biz_address'     => $params['biz_address'],
			'biz_address_ext' => $params['biz_address_ext'],
			'biz_location'    => $params['biz_location'],
			'city_id'         => $address['city_id'],
			'area_id'         => $address['area_id'],
			'city_name'       => $address['city_name'],
			'area_name'       => $address['area_name'],
			'status'          => Ref::ORDER_STATUS_DEFAULT,
			'tag_id'          => $params['biz_tag_id'],
		];

		//如果是之前审核失败的则修改
		$biz_info = BizInfo::findOne(['user_id' => $params['user_id'], 'status' => 2]);
		if ($biz_info) {
			$biz_data['update_time'] = time();
			$biz_info->attributes    = $biz_data;
			$result                  = $biz_info->save();
		} else {
			$biz_info                = new BizInfo();
			$biz_data['create_time'] = time();
			$biz_data['user_id']     = $params['user_id'];
			$biz_info->attributes    = $biz_data;
			$result                  = $biz_info->save();
		}


		$result ? : Yii::$app->debug->log_info("biz_send_apply", $biz_info->getErrors());
		$result ? : Yii::$app->debug->log_info("biz_send_apply_savedata", $biz_data);

		return $result;

	}

	/**
	 * 获取企业送状态(判断是否企业送)
	 * @param $user_id
	 * @return array
	 */
	public static function getBizStatus($user_id)
	{
		$result   = [];
		$biz_info = BizInfo::findOne(['user_id' => $user_id, 'status' => [0, 1]]);
		if ($biz_info) {
			$result['status'] = $biz_info->status;
		}

		return $result;
	}
}