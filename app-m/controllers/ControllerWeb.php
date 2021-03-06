<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/5
 */

namespace m\controllers;

use common\components\ControllerBase;
use common\helpers\security\SecurityHelper;
use common\helpers\users\UserHelper;
use m\helpers\StateCode;
use Yii;
use yii\web\UnauthorizedHttpException;

class ControllerWeb extends ControllerBase
{

	public $_name; //请求动作ID
	public $_code; //信息码
	public $_message;  //提示信息
	public $_data; //数据主体
	public $_startTime;  //开始访问时间
	public $_status;  //响应状态
	public $api_version;  //api版本号

	public function init()
	{
		parent::init(); // TODO: Change the autogenerated stub
		$this->enableCsrfValidation = false;
		$this->_startTime           = microtime(true); //开始访问的时间
	}

	/**
	 * 响应的数据字段
	 * @return array
	 */
	public function response()
	{
		$result             = [];
		$result['name']     = $this->_name ? $this->_name : $this->id . '/' . $this->action->id;
		$result['code']     = $this->_code ? $this->_code : 0;
		$result['message']  = $this->_message ? $this->_message : '';
		$result['duration'] = round(microtime(true) - $this->_startTime, 4);
		$result['data']     = $this->_data ? $this->_data : '';
		$result['status']   = $this->_status ? $this->_status : 200;

		return json_encode($result);
	}

	/**
	 * 设置状态码提示信息
	 * @param $code
	 */
	public function setCodeMessage($code)
	{
		$this->_code    = $code;
		$this->_message = StateCode::get($code);
	}
}
