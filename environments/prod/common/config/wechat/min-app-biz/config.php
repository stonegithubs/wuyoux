<?php
$logPath    = '/tmp/wechat/mini-app_' . date("Y-m-d") . '.log';
$wx_sslCert = Yii::getAlias("@common") . "/config/wechat/mini-app/cert/apiclient_cert.pem";
$wx_sslKey  = Yii::getAlias("@common") . "/config/wechat/mini-app/cert/apiclient_key.pem";

return [
	/**
	 * 账号基本信息，请从微信公众平台/开放平台获取
	 */
	'app_id'        => 'wx0803e6035308d429',         // AppID
	'secret'        => 'c1a89700c3b6b1ac8caa65e8424a404a',     // AppSecret
	'token'         => 'wuyou',          // Token
	'aes_key'       => 'xTXCN5cUY0mMjTszsXrXsM4ZNNMp8xxa',                    // EncodingAESKey，兼容与安全模式下请一定要填写！！！

	/**
	 * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
	 * 使用自定义类名时，构造函数将会接收一个 `EasyWeChat\Kernel\Http\Response` 实例
	 */
	'response_type' => 'array',

	/**
	 * 日志配置
	 *
	 * level: 日志级别, 可选为：
	 *         debug/info/notice/warning/error/critical/alert/emergency
	 * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
	 * file：日志文件位置(绝对路径!!!)，要求可写权限
	 */
	'log'           => [
		'level'      => 'debug',
		'permission' => 0777,
		'file'       => $logPath,
	],

	/**
	 * 接口请求相关配置，超时时间等，具体可用参数请参考：
	 * http://docs.guzzlephp.org/en/stable/request-config.html
	 *
	 * - retries: 重试次数，默认 1，指定当 http 请求失败时重试的次数。
	 * - retry_delay: 重试延迟间隔（单位：ms），默认 500
	 * - log_template: 指定 HTTP 日志模板，请参考：https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php
	 */
	'http'          => [
		'retries'     => 1,
		'retry_delay' => 500,
		'timeout'     => 5.0,
		// 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
	],

	/**
	 * OAuth 配置
	 *
	 * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
	 * callback：OAuth授权完成后的回调页地址
	 */
	'oauth'         => [
		'scopes'   => ['snsapi_userinfo'],
		'callback' => '/index.php?r=oauth/login',
	],

	/**
	 * 微信支付配置
	 *
	 * 以下是必须字段
	 */
	'sandbox'       => false, // 设置为 false 或注释则关闭沙箱模式
	'mch_id'        => '1498369172',
	'key'           => 'c56d86cb0c27265682a5721095d7dff1',   // API 密钥

	// 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
	'cert_path'     => $wx_sslCert, // XXX: 绝对路径！！！！
	'key_path'      => $wx_sslKey,      // XXX: 绝对路径！！！！

	'notify_url' => 'https://pay.281.com.cn/mini-biz-wxpay/notify',     // 你也可以在下单时单独设置来想覆盖它
];


