<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/7/10 23:41
 */

namespace console\jobs;

class DemoJob extends JobBase
{
	public $my;


	public function test1()
	{
		var_dump($this->params);
		var_dump($this->my);
	}
}
