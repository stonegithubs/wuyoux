<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/6/8 14:33
 */
namespace common\components;

use common\helpers\security\SecurityHelper;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\web\Response;

class ControllerAPI extends ControllerBase
{
	public $_name; //请求动作ID
	public $_code; //信息码
	public $_message;  //提示信息
	public $_data; //数据主体
	public $_starttime;  //开始访问时间
	public $_status;  //响应状态
	public $api_version;  //api版本号

	final public function init()
	{
		parent::init();
		$this->api_version = SecurityHelper::getBodyParam("api_version",'1.0');
		EventHandler::routeEvents($this);
		$this->_init();

	}

	public function _init()
	{

	}

	/**
	 * 响应的数据字段
	 * @return array
	 */
	public function response()
	{
		$result             = [];
		$result['name']     = $this->_name ? $this->_name : $this->id.'/'.$this->action->id;
		$result['code']     = $this->_code ? $this->_code : 0;
		$result['message']  = $this->_message ? $this->_message : '';
		$result['duration'] = round(microtime(true) - $this->_starttime, 4);
		$result['data']     = $this->_data ? $this->_data : '';
		$result['status']   = $this->_status ? $this->_status : 200;

		return $result;
	}

	//错误的时候的响应
	public function errorResponse()
	{
		$this->_code    = $this->_code ? $this->_code : 40001;
		$this->_message = $this->_message ? $this->_message : "";

		return $this->response();
	}

	/**
	 * @var string|array the configuration for creating the serializer that formats the response data.
	 */
	public $serializer = 'yii\rest\Serializer';

	/**
	 * 静态附加行为
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			// 命名行为，配置数组
			'contentNegotiator' => [
				'class'   => ContentNegotiator::className(),
				'formats' => [
					//指定所有action的返回格式为json或者xml
					//具体根据Request中header的accept字段进行分析
					'application/json' => Response::FORMAT_JSON,
					'application/xml'  => Response::FORMAT_XML,
				],
			],
			//用于验证http请求方式
			//动作执行前进行判断, 验证未通过，则action不会继续执行下
			'verbFilter'        => [
				'class'   => VerbFilter::className(),
				//指定action的访问规则
				'actions' => $this->verbs(),
			],
			'authenticator'     => [
				'class'       => CompositeAuth::className(),
			],
			//速率限制
			'rateLimiter'       => [
				'class' => RateLimiter::className(),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$this->_starttime = microtime(true); //开始访问的时间
		$this->_status    = 200;
		if (parent::beforeAction($action)) {
			return true;
		} else
			return false;
	}

	/**
	 *  在action之后运行，可用来过滤输出
	 * @inheritdoc
	 */
	public function afterAction($action, $result)
	{
		$result = parent::afterAction($action, $result);
		//把响应数据转换为数组
		return $this->serializeData($result);
	}

	/**
	 * Declares the allowed HTTP verbs.
	 * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
	 * @return array the allowed HTTP verbs.
	 */
	protected function verbs()
	{
		return [
			'index'  => ['GET', 'HEAD'],
			'view'   => ['GET', 'HEAD'],
			'create' => ['POST'],
			'update' => ['PUT', 'PATCH'],
			'delete' => ['DELETE'],
		];
	}

	/**
	 * Serializes the specified data.
	 * The default implementation will create a serializer based on the configuration given by [[serializer]].
	 * It then uses the serializer to serialize the given data.
	 *
	 * @param mixed $data the data to be serialized
	 *
	 * @return mixed the serialized data.
	 */
	protected function serializeData($data)
	{
		return Yii::createObject($this->serializer)->serialize($data);
	}
}
