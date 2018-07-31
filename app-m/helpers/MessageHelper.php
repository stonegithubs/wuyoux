<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/24
 */

namespace m\helpers;


use common\helpers\HelperBase;

class MessageHelper extends HelperBase
{
	/**
	 * 返回json数据
	 */
	public static function response($status = 0, $msg = '', $data = '')
	{
		$json = [
			'status' => $status,
			'msg'    => $msg,
			'data'   => $data
		];
		echo json_encode($json);
		exit();
	}
}