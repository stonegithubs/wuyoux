<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/12/19
 */

namespace common\helpers\users;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\utils\RegionHelper;
use common\models\users\BizInfo;
use common\models\users\BizInfoLog;
use common\models\util\BizTag;
use Yii;

class BizHelper extends HelperBase
{

	public static function getTagNameById($id)
	{
		$tag_info = BizTag::findOne(['id' => $id]);
		$result   = empty($tag_info->tag_name) ? "其他" : $tag_info->tag_name;

		return $result;
	}

	public static function getBizData($user_id)
	{
		return BizInfo::findOne(['user_id' => $user_id]);
	}


	/**企业送入驻
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
		$bizInfo = BizInfo::findOne(['user_id' => $params['user_id'], 'status' => 2]);
		if ($bizInfo) {
			$bizInfo->update_time = time();
		} else {
			$bizInfo              = new BizInfo();
			$bizInfo->create_time = time();
			$bizInfo->user_id     = $params['user_id'];
		}

		$bizInfo->attributes = $biz_data;
		$result              = $bizInfo->save();
		$result ? : Yii::$app->debug->log_info("biz_send_apply", $bizInfo->getErrors());
		$result ? : Yii::$app->debug->log_info("biz_send_apply_savedata", $bizInfo);

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

	/**
	 * 入驻首页(获取标签列表)
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
	 * 修改标签首页
	 * @param $user_id
	 */
	public static function tagIndex($user_id)
	{
		$result['list']        = self::getTagList();
		$bizInfo               = BizInfo::findOne(['user_id' => $user_id]);
		$result['selected_id'] = $bizInfo->tag_id;
		$result['update']      = false;
		if (self::judgeUpdateTag($user_id)) {
			$result['update'] = true;
		}

		return $result;
	}

	/**
	 * 判断是否能修改标签
	 * @param $user_id
	 * @return bool
	 */
	public static function judgeUpdateTag($user_id)
	{
		$result     = false;
		$limit_time = time() - 86400 * 90;
		$bizInfo    = BizInfo::findOne(['user_id' => $user_id]);
		$bizInfoLog = BizInfoLog::find()->where(['biz_id' => $bizInfo->id])->andWhere(['or', ['=', 'log_key', 'biz_verify'], ['=', 'log_key', 'biz_update']])->orderBy(['create_time' => SORT_DESC])->one();
		if (!$bizInfoLog) {
			return $result = true;
		}
		if ($bizInfoLog->create_time <= $limit_time) {
			$result = $bizInfo->id;
		}

		return $result;
	}

	/**
	 * 修改标签
	 * @param $user_id
	 * @param $tag_id
	 */
	public static function updateTag($user_id, $tag_id)
	{
		$result = false;

		if ($biz_info_id = self::judgeUpdateTag($user_id)) {
			$result                 = BizInfo::updateAll(['tag_id' => $tag_id, 'update_time' => time()], ['user_id' => $user_id]);
			$bizInfoLog             = new BizInfoLog();
			$insert_data            = [
				'biz_id'      => $biz_info_id,
				'log_key'     => 'biz_update',
				'log_value'   => $tag_id,
				'remark'      => '修改标签',
				'create_time' => time(),
			];
			$bizInfoLog->attributes = $insert_data;
			$result                 &= $bizInfoLog->save();
		}

		return $result;
	}

}