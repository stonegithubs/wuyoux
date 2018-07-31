<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/19
 * Time: 18:13
 */

namespace m\helpers;

use common\models\school\School;
use Yii;
use common\helpers\HelperBase;

class SchoolHelper extends HelperBase
{
	/**
	 * 添加学堂记录
	 * @param $user_id
	 * @return bool|string
	 */
	public static function addSchoolRecord($user_id)
	{
		$result       = false;
		$school_model = School::find()->where(['uid' => $user_id])->one();
		if (!$school_model) {
			$school             = new School();
			$params             = ['uid' => $user_id];
			$school->attributes = $params;
			$school->save() ? $result = Yii::$app->getDb()->getLastInsertID() : false;
		}

		return $result;
	}

	/**
	 * 修改阅读状态
	 * @param $status
	 * @param $user_id
	 * @return bool
	 */
	public static function updateStatus($status, $user_id)
	{
		$result       = false;
		$school_model = School::find()->where(['uid' => $user_id])->one();

		if ($school_model) {
			$school_status = $school_model->status;
			if ($school_status) {
				$school_status = json_decode($school_status);
				if (!in_array($status, $school_status)) {
					$update_status            = array_merge($school_status, [$status]);
					$update_status            = json_encode($update_status);
					$params                   = ['status' => $update_status];
					$school_model->attributes = $params;
					$school_model->save() ? $result = true : false;
				}
			} else {
				$update_status            = json_encode([$status]);
				$params                   = ['status' => $update_status];
				$school_model->attributes = $params;
				$school_model->save() ? $result = true : false;
			}
		}

		return $result;
	}

	/**
	 * 查询学堂记录
	 * @param $user_id
	 * @return array|bool
	 */
	public static function findSchoolRecord($user_id)
	{
		$result       = false;
		$school_model = School::find()->where(['uid' => $user_id])->one();
		if ($school_model->status) {
			$result = [
				'base_status'    => in_array('base', json_decode($school_model->status)) ? 1 : 0,
				'trailer_status' => in_array('trip', json_decode($school_model->status)) ? 1 : 0,
				'errand_status'  => in_array('errand', json_decode($school_model->status)) ? 1 : 0,
				'biz_status'     => in_array('biz', json_decode($school_model->status)) ? 1 : 0,
			];
		} else {
			$result['base_status']    = 0;
			$result['trailer_status'] = 0;
			$result['errand_status']  = 0;
			$result['biz_status']     = 0;
		}

		return $result;
	}
}