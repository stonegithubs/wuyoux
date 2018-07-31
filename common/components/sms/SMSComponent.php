<?php

/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/1/16
 */

namespace common\components\sms;


use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use yii\base\Component;

class SMSComponent extends Component
{
	/**
	 * 开发者的AK
	 * @var string
	 */
	public $accessKey;

	/**
	 * 密钥
	 * @var string
	 */
	public $accessKeySecret;

	/**
	 * 短信签名
	 * @var string
	 */
	public $signName;

	/**
	 * client
	 * @var null
	 */
	static $acsClient = null;

	public function __construct(array $config = [])
	{
		Config::load();
		parent::__construct($config);
	}

	/**
	 * 发送短信
	 * @return stdClass
	 * 参考 https://help.aliyun.com/document_detail/55451.html?spm=5176.doc63795.2.8.QCankv
	 */
	public function send($mobile, $params)
	{

		if (!YII_ENV_PROD) {
			if (substr($mobile, 0, 3) == '128') {
				return true;
			};
		}
		// 初始化SendSmsRequest实例用于设置发送短信的参数
		$request = new SendSmsRequest();
		//短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码,
		//批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
		if (is_array($mobile))
			$phone = implode(",", $mobile);
		else
			$phone = $mobile;

		// 必填，设置短信接收号码
		$request->setPhoneNumbers($phone);

		// 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
		$request->setSignName($this->signName);

		// 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
		$request->setTemplateCode($params['tpl']);

		// 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
		if (isset($params['data'])) {
			$request->setTemplateParam(json_encode($params['data'], JSON_UNESCAPED_UNICODE));
		}

		// 可选，设置流水号
		//$request->setOutId("yourOutId");

		// 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
		//$request->setSmsUpExtendCode("1234567");

		// 发起访问请求
		$acsResponse = $this->getAcsClient()->getAcsResponse($request);

		return $acsResponse;

	}

	/**
	 * 取得AcsClient
	 *
	 * @return DefaultAcsClient
	 */
	private function getAcsClient()
	{
		//产品名称:云通信流量服务API产品,开发者无需替换
		$product = "Dysmsapi";

		//产品域名,开发者无需替换
		$domain = "dysmsapi.aliyuncs.com";

		// 暂时不支持多Region
		$region = "cn-hangzhou";

		// 服务结点
		$endPointName = "cn-hangzhou";


		if (static::$acsClient == null) {

			//初始化acsClient,暂不支持region化
			$profile = DefaultProfile::getProfile($region, $this->accessKey, $this->accessKeySecret);

			// 增加服务结点
			DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

			// 初始化AcsClient用于发起请求
			static::$acsClient = new DefaultAcsClient($profile);
		}

		return static::$acsClient;
	}

	/**
	 * 短信发送记录查询
	 * @return stdClass
	 */
	public function query($mobile, $date, $page = 1, $pageSize = 10)
	{

		// 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
		$request = new QuerySendDetailsRequest();

		// 必填，短信接收号码
		$request->setPhoneNumber($mobile);

		// 必填，短信发送日期，格式Ymd，支持近30天记录查询
		$request->setSendDate($date);

		// 必填，分页大小
		$request->setPageSize($pageSize);

		// 必填，当前页码
		$request->setCurrentPage($page);

		// // 选填，短信发送流水号
		// $request->setBizId("yourBizId");

		// 发起访问请求
		$acsResponse = $this->getAcsClient()->getAcsResponse($request);

		return $acsResponse;
	}


}
