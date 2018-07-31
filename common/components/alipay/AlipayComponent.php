<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/07/02 13:01
 */

/**
 * 蚂蚁金服 https://open.alipay.com
 *
 * SDK参考这个
 * https://docs.open.alipay.com/54/106370
 */

namespace common\components\alipay;

use yii\base\Component;
use Yii;

class AlipayComponent extends Component
{

	private $gatewayurl = 'https://openapi.alipay.com/gateway.do';

	public $appId;

	public $rsaPrivateKey;

	public $alipayrsaPublicKey;

	public $notify_url;

	public $signType = 'RSA2';

	public function __construct(array $config = [])
	{
		parent::__construct($config);
		//TODO 钥匙通过文件读取
	}

	private function getAop()
	{

		require_once \Yii::getAlias('@common/components/alipay/aop/AopClient.php');

		$aop                     = new \AopClient();
		$aop->gatewayUrl         = $this->gatewayurl;
		$aop->appId              = $this->appId;
		$aop->rsaPrivateKey      = $this->rsaPrivateKey;
		$aop->format             = "json";
		$aop->charset            = "UTF-8";
		$aop->signType           = $this->signType;
		$aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;

		return $aop;
	}

	public function tradeApp($params)
	{

		$aop = $this->getAop();

		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		require_once Yii::getAlias('@common/components/alipay/aop/request/AlipayTradeAppPayRequest.php');

		$request = new \AlipayTradeAppPayRequest();
		if (isset($params['notify_url']))
			$request->setNotifyUrl($params['notify_url']);
		else
			$request->setNotifyUrl($this->notify_url);

		$request->setBizContent(json_encode($params['bizcontent']));

		return $response = $aop->sdkExecute($request);
	}


	public function notify()
	{
		require_once \Yii::getAlias('@common/components/alipay/aop/AopClient.php');
		$aop                     = new \AopClient;
		$aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;

		return $aop->rsaCheckV1($_POST, null, $this->signType);
	}


	/**
	 * 支付宝无密码退款
	 * @param $data
	 *
	 * @return bool
	 */
	public function refund($data)
	{
		require_once \Yii::getAlias('@common/components/alipay/aop/request/AlipayTradeRefundRequest.php');
		$request = new \AlipayTradeRefundRequest();

		/*$data=[
					'out_trade_no'=>'20150320010101001',
					'trade_no'=>'2014112611001004680073956707',
					'refund_amount'=>'27',
					'refund_reason'=>'正常退款',
					'out_request_no'=>null

				];*/
		$biz = json_encode($data);
		$request->setBizContent($biz);
		$aop          = $this->getAop();
		$result       = $aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode   = $result->$responseNode->code;
		Yii::$app->debug->pay_info("refund",$responseNode);
		if (!empty($resultCode) && $resultCode == 10000) {
			return true;
		} else {
			return false;
		}
	}


	public static function aopclient_request_execute($request, $token = null)
	{
		$alipay_config = null;  //通过require加载
		require_once \Yii::getAlias('@pay/alipay/alipay.config.php');
		require_once \Yii::getAlias('@pay/alipay/aop/AopClient.php');

		$aop                        = new \AopClient ();
		$aop->gatewayUrl            = $alipay_config ['gatewayUrl'];
		$aop->appId                 = $alipay_config ['app_id'];
		$aop->rsaPrivateKeyFilePath = $alipay_config ['private_key_path'];
		$aop->apiVersion            = "1.0";
		$result                     = $aop->execute($request, $token);
		self::log("response: " . var_export($result, true));

		return $result;
	}

}