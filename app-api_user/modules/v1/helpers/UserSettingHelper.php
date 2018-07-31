<?php
/**
 * Created by PhpStorm.
 * User: JasonLeung
 * Date: 2018/1/25
 * Time: 14:33
 */

namespace api_user\modules\v1\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\helpers\payment\CouponHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\users\BizHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use common\helpers\utils\UtilsHelper;
use common\models\users\BizInfo;
use yii\db\Query;
use Yii;
use yii\db\Exception;

class UserSettingHelper extends HelperBase
{
	const userTbl  = 'bb_51_user';
	const imageTbl = 'bb_51_image';

	/**
	 * 首页测边栏
	 * @param $user_id
	 * @return bool
	 */
	public static function sideBar($user_id)
	{
		$result   = false;
		$userInfo = UserHelper::getUserInfo($user_id, ['userphoto', 'money', 'nickname']);
		if ($userInfo) {
			$result['is_biz'] = 0;
			if ($biz_data = BizHelper::getBizData($user_id)) {
				$result['is_biz']   = $biz_data->status;
				$result['biz_name'] = $biz_data->biz_name;
			}

			$result['nickname']     = isset($userInfo['nickname']) ? $userInfo['nickname'] : "帮帮用户";
			$id_image               = isset($userInfo['userphoto']) ? $userInfo['userphoto'] : 0;
			$result['avatar_image'] = ImageHelper::getUserPhoto($id_image);
			$result['user_balance'] = $userInfo['money'];
			$result['link']         = [
				['link_name' => '成为小帮', 'link_img' => 'https://img02.281.com.cn/Uploads/51bangbang/userphotos/default.png', 'link_url' => UrlHelper::webLink('enter-provider/index')],
				['link_name' => '城市合伙人', 'link_img' => 'https://img02.281.com.cn/Uploads/51bangbang/userphotos/default.png', 'link_url' => 'http://51bangbang.com.cn/index.php?s=/Home/Cooperation/index.html'],
				//				['link_name' => '邀请有礼', 'link_img' => 'https://img02.281.com.cn/Uploads/51bangbang/userphotos/default.png', 'link_url' => 'http://www.51bangbang.com'],
			];
		}

		return $result;
	}

	/**
	 * 首页轮播图
	 * @return array|bool
	 */
	public static function homeCarouselV10()
	{
		$city_name = SecurityHelper::getBodyParam('city_name');
		$city_name = empty($city_name) ? '中山' : $city_name;
		$city_id   = RegionHelper::getCityId($city_name);
		$data      = [];
		$now       = time();

		$ad_total = (new Query())
			->from("bb_advertisement")->select(['pic', 'value', 'sort'])
			->where(['and', ['pkey' => 'NewPic', 'status' => 1, 'ad_type' => 1], ['or', ['province_id' => 0], ['city_id' => $city_id]]])->all();
		foreach ($ad_total as $k => $v) {
			$data[] = [
				'pic'   => ImageHelper::getFigureUrl($v['pic']),
				'type'  => !empty($v['value']) ? 2 : 1,
				'value' => !empty($v['value']) ? $v['value'] : '',
				'sort'  => $v['sort']
			];
		}

		if ($city_id) {
			$ad_city = (new Query())
				->from("bb_advertisement")->select(['pic', 'value', 'sort'])
				->where(['pkey' => 'NewPic', 'city_id' => $city_id, 'ad_type' => 2, 'status' => 1])
				->andWhere(['<', 'start_time', $now])
				->andWhere(['>', 'end_time', $now])
				->all();
			foreach ($ad_city as $k => $v) {
				$data[] = [
					'pic'   => ImageHelper::getFigureUrl($v['pic']),
					'type'  => !empty($v['value']) ? 2 : 1,
					'value' => !empty($v['value']) ? $v['value'] : '',
					'sort'  => $v['sort']
				];
			}
		}

		//数组排序
		if ($data) {
			$data = UtilsHelper::multi_array_sort($data, 'sort', 1);
		}

		//数据为空，显示默认图片
		if (empty($data)) {
			$data[] = [
				'pic'   => "https://img02.281.com.cn/Uploads/Common/ad_pic.png",
				'type'  => 1,
				'value' => '',
			];
		}

		$result = $data;

		return $result;
	}

	/**
	 * 获取企业送信息
	 * @param $user_id
	 * @return bool
	 */
	public static function getBizInfo($user_id)
	{
		$result    = false;
		$user_info = UserHelper::getUserInfo($user_id, 'money');
		$bizInfo   = BizInfo::findOne(['user_id' => $user_id, 'status' => 1]);
		if ($bizInfo) {
			$result['user_money']      = $user_info['money'];
			$result['biz_name']        = $bizInfo->biz_name;
			$result['biz_mobile']      = $bizInfo->biz_mobile;
			$result['biz_address']     = $bizInfo->biz_address;
			$result['biz_address_ext'] = $bizInfo->biz_address_ext;
			$result['card_count']      = CouponHelper::getCardCount($user_id);
		}

		return $result;
	}

	/**
	 * 更新用户信息
	 * @param $userId  int 用户id
	 * @param $params  array 用户表要更新的数据
	 * @return bool|int  结果
	 */
	public static function updateUserInfo($userId, $params)
	{
		$result      = false;
		$transaction = Yii::$app->db->beginTransaction();
		try {
			if ($params['userphoto'] > 0) {
				Yii::$app->db->createCommand()->update(self::imageTbl, ['uid' => $userId], ['id' => $params['userphoto']])->execute();
			} else {
				unset($params['userphoto']);   //app端不传头像id过来则默认为0，表示没有更改头像,不用更新用户表该字段
			}
			$params['update_time'] = time();
			$result                = Yii::$app->db->createCommand()->update(self::userTbl, $params, ['uid' => $userId])->execute();
			if ($result) {
				$transaction->commit();
			}
		}
		catch (Exception $e) {
			$transaction->rollBack();
		}

		return $result;
	}

}