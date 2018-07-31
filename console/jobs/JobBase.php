<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/7/2 23:40
 */

namespace console\jobs;

use yii\base\BaseObject;
use Yii;
use yii\queue\Job;

abstract class JobBase extends BaseObject implements Job
{

	public $function;
	public $params;

	public function execute($queue)
	{

		if ($this->hasMethod($this->function)) {
			call_user_func([$this, $this->function], $this->params);
			echo $msg = "call function '".$this->function."'";
		} else {
			echo $msg = "no this function '".$this->function."'";
			Yii::$app->debug->job_info("error_no_this_function", $msg);
		};
	}

}