<?php
/**
 * Created by PhpStorm.
 * User: ryanChan
 * Date: 2018/3/2
 * Time: 16:54
 */

namespace api_user\modules\v1\api;

use api_user\modules\v1\helpers\UserSettingHelper;
use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use Yii;
use common\helpers\images\ImageHelper;
use common\helpers\users\UserHelper;

class UserAPI extends HelperBase
{

	const IMAGE_TBL = 'bb_51_image';

//更新用户信息
	public static function updateUserInfo($userId)
	{
		//返回信息
		$result = [
			'status'  => false,
			'message' => '',
			'data'    => null,   //status为true才有数据
		];
		$params = [
			"nickname"  => SecurityHelper::getBodyParam("nickname"),  //昵称
			"birthday"  => SecurityHelper::getBodyParam("birthday"),  //生日
			"sex"       => SecurityHelper::getBodyParam("sex", 0), //性别0和1为正常
			"userphoto" => SecurityHelper::getBodyParam('image_id', 0), //头像id
		];
		//TODO 需要对nickname进行过滤
		if (empty($params['nickname']) || empty($params['birthday'])) {
			$result['message'] = "请填写正确的信息";

			return $result;
		}
		$res = UserSettingHelper::updateUserInfo($userId, $params);
		//更新成不成功都返回用户信息
		$useInfo           = UserHelper::getUserInfo($userId, 'nickname, birthday, sex, userphoto');
		$info['image_url'] = ImageHelper::getUserPhoto($useInfo['userphoto']);   //头像url
		$useInfo           = array_merge($useInfo, $info);
		$result['data']    = $useInfo;
		if ($res) {
			$result['message'] = "更新成功";
		} else {
			$result['message'] = "更新失败";
		}

		return $result;
	}
}