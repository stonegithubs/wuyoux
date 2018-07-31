<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/26
 * Time: 11:16
 */

namespace api_wx\modules\biz\helpers;

use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use common\helpers\utils\RegionHelper;
use yii\db\Query;
use Yii;

class BizUserHelper extends UserHelper
{
	public static function checkMobileCount($mobile){
		$result = false;
		$currentStartTime = strtotime(date('Y-m-d 00:00:00',time()));
		$currentEndTime = strtotime(date('Y-m-d 23:59:59',time()));

		$count = (new Query())
			->select('*')
			->from('wy_token')
			->where(['keys'=>$mobile])
			->andFilterWhere(['between', 'create_time', $currentStartTime, $currentEndTime])
			->count();

		if($count>4){
			$result = true;
		}
		return $result;
	}

	public static function verifyBizUserLoginInfo($params)
	{
		$result = false;
		$field  = "score,uid,is_shops,userphoto,nickname,sex,mobile,birthday,paypassword,password,status";
		$data   = (new Query())->select($field)->from("bb_51_user")->where(['mobile' => $params['mobile']])->one();
		if (!empty($data)) {
			$result = $data;
			if ($data['status'] == 1) {
				//更新登录信息
				$host = Yii::$app->request->getUserHost() ? Yii::$app->request->getUserHost() : Yii::$app->request->userIP;
				$sql  = "update  `" . self::userTbl . "` set `login_status`=1,`last_login_time`=" . time() . ",";
				$sql  .= " `last_login_ip`=\"" . strval($host) . "\"";
				$sql  .= " where `uid`=" . $data['uid'];
				Yii::$app->db->createCommand($sql)->execute();
			}
		}

		return $result;
	}

	public static function BizSignUp($params){
		$result = false;
		$insert_data = [
			'nickname'     => '帮帮用户',
			'mobile'       => $params['mobile'],
			'password'     => SecurityHelper::encryptPassword($params['password']),
			'city_id'      => RegionHelper::getRegionId($params['city_name']),
			'n_location'   => $params['user_location'],
			'reg_ip'       => Yii::$app->request->getUserIP(),
			'reg_time'     => time(),
			'status'       => 1,
			'register_src' => $params['register_src'],
		];

		$data =  Yii::$app->db->createCommand()->insert("bb_51_user", $insert_data)->execute();
		if($data){
			$result = $data;
		}
		return $result;
	}
}