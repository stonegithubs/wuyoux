<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/13
 */

namespace common\components\push\getui;

use yii\base\Component;
use Yii;

class GeTuiComponent extends Component
{

	public $appId;

	public $appKey;

	public $appSecret;

	public $masterSecret;

	private $api_url = "https://restapi.getui.com/v1/";

	public function __construct(array $config = []) { parent::__construct($config); }

	private $auth_key;


	public function init()
	{
		parent::init();
		$this->api_url  = $this->api_url.$this->appId."/";
		$this->auth_key = "auth_token".$this->appId;
	}


	/**
	 * 获取authtoken,从缓存中获取
	 * 有效时间是1天，如果超时则重新获取
	 * 为了保险起见，保存时间为23小时，超时刷新
	 * @return mixed
	 */
	public function getAuthToken()
	{
		$token = Yii::$app->cache->get($this->auth_key);
		if (!$token) {

			$res = $this->refreshAuthToken();
			if ($res['result'] == 'ok') {
				$token = $res['auth_token'];
				Yii::$app->cache->set($this->auth_key, $token, 82800);
			}
		}

		return $token;
	}

	/**
	 * 刷新auth token
	 * @return mixed
	 */
	private function refreshAuthToken()
	{
		$appKey       = $this->appKey;
		$masterSecret = $this->masterSecret;
		$timestamp    = time() * 1000;        // 获取毫秒数 秒数*1000
		$sign         = strtolower(hash('sha256', $appKey.$timestamp.$masterSecret, false));

		$data    = [
			'sign'      => $sign,
			'timestamp' => $timestamp,
			'appkey'    => $appKey,
		];
		$content = json_encode($data);
		$header  = [
			'Content-Type: application/json',
		];

		$url = $this->api_url.'auth_sign';

		return $this->http($url, $header, $content);
	}


	/**
	 * 关闭鉴权
	 * @return bool|mixed
	 */
	public function closeAuthToken()
	{
		$authToken = $this->getAuthToken();
		if (!$authToken) {
			return false;
		}

		$header = [
			'Content-Type: application/json',
			'authtoken:'.$authToken
		];

		$url = $this->api_url.'auth_close';

		$res = $this->http($url, $header);
		if ($res['result'] == 'ok') {
			Yii::$app->cache->delete($this->auth_key);
		}

		return $res;
	}

	/**
	 *  向某个用户推送消息
	 *
	 * @param        $clientID
	 * @param string $title
	 * @param string $text
	 * @param string $transmission_content
	 *
	 * @return mixed
	 */
	public function sendToOneNotification($clientID, $title = '', $text = '', $transmission_content = '')
	{
		$appKey    = $this->appKey;
		$authToken = $this->getAuthToken();
		$content   = [
			'message'      => [
				"appkey"     => $appKey,
				"is_offline" => false,
				"msgtype"    => "notification"
			],
			'notification' => [

				'style' => [
					'type'  => 0,
					'text'  => $text,
					'title' => $title
				],

				"transmission_type"    => true,
				"transmission_content" => $transmission_content
			],
			"cid"          => $clientID,
			"requestid"    => "".time()
		];
		$content   = json_encode($content);
		$header    = [
			'Content-Type: application/json',
			'authtoken:'.$authToken
		];

		$url = $this->api_url.'push_single';

		return $this->http($url, $header, $content);
	}

	/**
	 * 参考 http://docs.getui.com/server/rest/template/
	 *
	 * @param      $clientID
	 * @param      $content
	 * @param null $inFormContent
	 * @param null $inFormTitle
	 *
	 * @return mixed
	 */
	public function sendToOneTransmission($clientID, $content, $inFormContent=null, $inFormTitle=null)
	{

		$start = date("Y-m-d H:i:s");
		$end   = date("Y-m-d H:i:s", time() + 1000);

		$authToken = $this->getAuthToken();
		$content   = [
			'message'      => [
				"appkey"     => $this->appKey,
				"is_offline" => true,
				"msgtype"    => "transmission"
			],
			"transmission" => [
				"transmission_type"    => false,
				"transmission_content" => json_encode($content),
//				"duration_begin"       => $start,
//				"duration_end"         => $end
			],

			"push_info" =>
				[
					"aps"     => [
						'alert'             => [
							'title' => $inFormTitle,
							'body'  => $inFormContent,
						],
						'autoBade'          => '+1',
						'content-available' => 1,
						'sound'=>'dingdong.wav',
					],
					'payload' => json_encode($content)
				],
			"cid"       => $clientID,
			"requestid" => "".time()
		];

		$content = json_encode($content);
		$header  = [
			'Content-Type: application/json',
			'authtoken:'.$authToken
		];

		$url = $this->api_url.'push_single';

		return $this->http($url, $header, $content);
	}

	/**
	 * 单用户网页通知(仅仅通用)
	 * @param $clientID
	 * @param $title
	 * @param $content
	 * @param $url
	 * @return mixed
	 */
	public function sendToOneHtmlLink($clientID,$title, $content,$url)
	{
		$start = date("Y-m-d H:i:s");
		$end   = date("Y-m-d H:i:s", time() + 1000);

		$authToken = $this->getAuthToken();
		$content   = [
			'message'      => [
				"appkey"     => $this->appKey,
				"is_offline" => true,
				"msgtype"    => "link"
			],
			"link" => [
				'style' => [
					'type'  => 0,
					'text'  => $content,
					'title' => $title
				],
				'url'=>$url,
				'duration_begin'=>$start,
				'duration_end'=>$end
			],
			"cid"       => $clientID,
			"requestid" => "".time()
		];

		$content = json_encode($content);
		$header  = [
			'Content-Type: application/json',
			'authtoken:'.$authToken
		];

		$url = $this->api_url.'push_single';

		return $this->http($url, $header, $content);
	}

	/**
	 * 群发消息
	 *
	 * @param $message
	 *
	 * @return mixed
	 */
	public function sendToAllTransmission($message)
	{
		$appKey    = $this->appKey;
		$authToken = $this->getAuthToken();
		$content   = [
			'message'      => [
				"appkey"     => $appKey,
				"is_offline" => false,
				"msgtype"    => "transmission"
			],
			'transmission' => [
				"transmission_type"    => false,
				"transmission_content" => $message,
			],
			'requestid'    => "".time(),
		];
		$content   = json_encode($content);
		$header    = [
			'Content-Type: application/json',
			'authtoken:'.$authToken
		];

		$url = $this->api_url.'push_app';

		return $this->http($url, $header, $content);
	}

	/**
	 * 群发消息
	 * - 向所有的app发送notification消息
	 *
	 * @param string $title
	 * @param string $text
	 * @param string $transmission_content
	 *
	 * @return mixed
	 */
	public function sendToAllNotification($title = '', $text = '', $transmission_content = '')
	{
		$appKey    = $this->appKey;
		$authToken = $this->getAuthToken();
		$content   = [
			'message'      => [
				"appkey"     => $appKey,
				"is_offline" => false,
				"msgtype"    => "notification"
			],
			'notification' => [
				'style'                => [
					'type'  => 0,
					'text'  => $text,
					'title' => $title
				],
				"transmission_type"    => true,
				"transmission_content" => $transmission_content
			],
			'requestid'    => "".time(),
		];
		$content   = json_encode($content);
		$header    = [
			'Content-Type: application/json',
			'authtoken:'.$authToken
		];

		$url = $this->api_url.'push_app';

		return $this->http($url, $header, $content);
	}


	private function http($url, $headers, $data = '')
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
		if ($data != '') {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包

		}
		curl_setopt($curl, CURLOPT_TIMEOUT, 20); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		if (YII_ENV_DEV) {
			Yii::error("getui push info".json_encode($data));
		}

		$tmpInfo = curl_exec($curl);

		curl_close($curl);

		return json_decode($tmpInfo, true);
	}
}

