<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/6/25 23:05
 */

namespace common\components\tools;

use yii\base\Component;
use Yii;

/**
 * Class DebugComponent
 * @package common\components\tools
 */
class DebugComponent extends Component
{
	public static function error($message, $category = 'application')
	{
		if (is_array($message)) {
			$message = implode(",", $message);
		}
		Yii::error($message, $category);
	}

	public static function warn($message, $category = 'application')
	{

		Yii::warning($message, $category);
	}

	public static function info($message, $category = 'application')
	{
		Yii::info($message, $category);
	}

	public function dump($vars)
	{
		if (YII_ENV_TEST || YII_ENV_DEV || YII_DEBUG) {
			echo "<pre>";
			var_dump($vars);
			exit;
		}
	}

	/**
	 * 任务日志
	 * @param        $key
	 * @param string $message
	 */
	public function job_info($key, $message = 'can not empty')
	{
		Yii::info($key . json_encode($message), 'debug_job');    //任务job
	}

	/**
	 * 支付相关日志
	 * @param        $key
	 * @param string $message
	 */
	public function pay_info($key, $message = 'can not empty')
	{
		Yii::info($key . json_encode($message), 'debug_payment');    //和支付相关
	}

	/**
	 * 推送地图定位相关日志
	 * @param        $key
	 * @param string $message
	 */
	public function push_info($key, $message = '')
	{
		Yii::info($key . json_encode($message), 'debug_push');    //和推送相关
	}

	/**
	 * 一般记录日志
	 * @param        $key
	 * @param string $message
	 */
	public function log_info($key, $message = '')
	{
		Yii::info($key . json_encode($message), 'debug_log');
	}


}