<?php
if (!defined('WY_KEY_PREFIX')) {
	define('WY_KEY_PREFIX', "production");
}
$redis_hostname = 'wy-master01.redis.rds.aliyuncs.com';
$redis_port     = 6379;
$redis_password = "AliyunRedis2018";

return [
	'components' => [
		'db' => [
			'class'       => 'yii\db\Connection',
			'dsn'         => 'mysql:host=rm-wz9fmw8s74q6t632z482.mysql.rds.aliyuncs.com:3288;dbname=bangbang',
			'username'    => 'bangbang',
			'password'    => 'BBangang5188#@!**^#()%',
			'charset'     => 'utf8',
			'tablePrefix' => 'wy_' //新架构数据表都是这个前缀
		],

		'mailer' => [
			'class'            => 'yii\swiftmailer\Mailer',
			'useFileTransport' => false,//这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
			'transport'        => [
				'class'      => 'Swift_SmtpTransport',
				'host'       => 'smtp.qq.com',  //每种邮箱的host配置不一样
				'username'   => '519525543@qq.com',
				'password'   => 'zaq12wsx',
				'port'       => '465',
				'encryption' => 'ssl'
			],
			'messageConfig'    => [
				'charset' => 'UTF-8',
				'from'    => ['519525543@qq.com' => 'admin']
			],
		],
		'lbs'    => [
			'class'       => 'common\components\baidu\LBSComponent',
			'ak'          => 'UE9WEXxx4GHcTHuQwfYPf7O9KxIGLhCo',//'n8GGUlebGBdilwCSPUSedUsucj7NU5CS',
			'geotable_id' => '171146',//'169965',
			'coord_type'  => 3,
			'default_way' => 'riding'
		],
		'amap'   => [
			'class'       => 'common\components\amap\AMapComponent',
			'key'         => '9cdd089ab9084fe1c026cacbce5fb634',
			'secret'      => '5b173bad8f24224f457195f93e3eb73c',
			'tableid'     => '59b3cee4afdf521e86ba90df',
		],
		'alipay' => [
			'class'              => 'common\components\alipay\AlipayComponent',
			'appId'              => '2017070407638595',
			'alipayrsaPublicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoNuob+mTcEv9lwOX7YHWJKXgPIeQEQCtg2i9isB4aWdSuuCPBDHtsNJ98nDCI2r32pSQmFNRgKLf54lykBzQBQVwTiddri1ULNxFvmKcILd/lvRKwpI/yfGxVTa1iKXaTjg+XE840htjOgSck7PXefdSqffZk75Cz6XwJ8Of7nAr2X1z2ncpfmlP7Mz7VoJd3Balr96MHpScKyFhuzFYYFpGZt+OLv9XV0TDdodM4oThu2/Ju6KUgpvtbHzsmg423cdSvZPbv+IvDDezAdtE9vdvs2i6IDUieZVKVuNPnkLYhkkX+Wi2irnsW95L0HsA1U3n1UiHPZLyX8tnfhqDZwIDAQAB',
			'rsaPrivateKey'      => 'MIIEpAIBAAKCAQEAvKf4+/uUISNOQj6mf87tj5PupEHZqcUWsJ7bNOkJabTDAAbaGNtakGWqPt3GiVtyIJkv4ZRFWpz5ZZwIBRWMklvqMSXyIgDef+CaulH4jcYXs1Ev5op5EGakMrDjBFFSMf8evMhVTK5EAzfRHOtNQ5LLio4zSvFiXl5Fi5WmaCEIFfRLIiuROWCMw6RNF+KWn3sZglHIAYWAEn6y6rdnO9upVK4GckW/3oi39QNYdys2CRBFVlZ5FKwgBC/XvzPOetJ3FofCvfFxIswkXZ0ZMvjpluOhbILvdzF8eIjDq3lLOuAlnD6mLupWMIdXHimZcvZaYfXa2jrlhGoUIVbCAwIDAQABAoIBAApRJUyidXdadu0of/J9XoB7lpFlCIJARP1jspkyJVGikQdvWH4DgPBknurRNgpM9fDSb8vmT+SksP7diZK9mvJmFGibqqIxafmUTuHhmbYnHySvS+9Po09CTbh38/JIgDY7vXKVghSvcwi6BLWQKKmJXZg2Lpfqm3aNsT/w7yjCjZXeccDSDexQr6LENvmT8Bzu0RLDkhYWjFiXxgWIxr0LuhXHvthzLBVBSFsJ9rsnl77zXxTHcoA1QBxzriCJXrQvwpH3qEmrvsa+P+eU2lndj6gnRhBOWJDfHKGbN8ZumYiADsBfSEGx3kCxBtwOyxnvy/hm/CpHKhlBMsg4MAECgYEA699PP8gKecO9GLMIDtLaU1nAnJIU/So45Wx3Pn0wWHHVjRE1n0j3820ddjtBDEj7kDoo/6OHIpZ/yOXHXZnEmkVq8Wgzcd+2c6h7+r8i6SGbu0U8b/KyYCXOIhJh9TGvCqzWZuQkmis8vxkdpfmBXDSn7YgaoH7C6tPHK6dETM8CgYEAzME2yR5twLgGBz8xQv+ubX7yoWWeu0tBAGyXjcNT5Tr9md7EpNyWniB22yGX4qwFJ6BlIIt8iDtVddq1b48vQhjxU0WZTDOoGd8Y1KGQ6CjMgDJNCO82yIRErKIpkKTDZcMROh302/HP4THAAn6KA3oZbo0S5taozztCDGAlTI0CgYEAzU1Cel6Ql6mCpsP1yIlIlPHzD5SrBixA95gwNkja0rDKUo3fIXRutQeNdEJg/ONtavzkgJYCYl2ifedaXyMfF7RCQDsVRBGXihXlS7wgE9E37ol18G7LSZq+T7O2ZafIFr+XolkDbtkhd6fRwYxpRyth2wLzQizzVVfd7SYa8csCgYEAm4gxC3i3ueqPebXAv7nkT3xc5ciBWJgDWkUioia7dabnuJgFG44MQzU5056i9yXgDIfECYKrJ/iaJBnsND+5kCXOOojlt4KNsIlNHUdVAWTsYnzTE3RSUuJWICRWGm9tlcElNtZGh0QZul/GEJ76S0XWR6mVsA9iz3ed5jSqYIkCgYAveOGp5BxaRPAiRW6MzuTaDCvZBp6NF+6WzOG2tSnV9PLnjDifXnf6JDKIlgEHKAEdGXXAAseh/pNu3c8kf5gq153PZ8k9YIGzBj4BUSaFwk9hmeDLCJSRvLd0YCkcUA76uCczv6W6f5TijFn2qOZl69OThpm5z0Cbe6ar0aRTsg==',
			'notify_url'         => 'https://pay.281.com.cn/alipay/notify'
		],

		//		正式环境
		'GTPush'     => [	//旧版推送
							 'class'        => 'common\components\push\getui\GeTuiComponent',
							 'appId'        => 'CQLx1lvJVy5WCnPUy8szG2',
							 'appKey'       => 'h3WvDfgSdH9gE3xuMuYS65',
							 'appSecret'    => 'yZMFeypc9i7UmoSktaf6E6',
							 'masterSecret' => 'rJ6IARZmcO90c2a0V13V'
		],
		'GTPushUser'     => [	//新版用户版
								 'class'        => 'common\components\push\getui\GeTuiComponent',
								 'appId'        => '4OJmTsuPJs5SAh2v6IwEi8',
								 'appKey'       => 'jQPP6fBC1A9ENorRzCk5Q5',
								 'appSecret'    => 'mtpd0eIEZZ9KnKAK4FqQC9',
								 'masterSecret' => 'H6Ziwe7W9BAIU7PDEabIe1'
		],
		'GTPushProvider' => [	//新版小帮版
								 'class'        => 'common\components\push\getui\GeTuiComponent',
								 'appId'        => 'f66H7Hxfk28lLnO27oV6h',
								 'appKey'       => 'XFHcOUR8prAdpO0NcyhYU8',
								 'appSecret'    => 'tBUMNeGcmx8eFnAlGE3LC2',
								 'masterSecret' => '0Lvls33S1D8RuZPrqUCT46'
		],
		'redis'          => [
			'class'    => 'yii\redis\Connection',
			'hostname' => $redis_hostname,
			'port'     => $redis_port,
			'password' => $redis_password,
			'database' => 31,
		],
		'queue'          => [
			'class'   => \yii\queue\redis\Queue::class,
			'redis'   =>
				[
					'class'    => 'yii\redis\Connection',
					'hostname' => $redis_hostname,
					'port'     => $redis_port,
					'password' => $redis_password,
					'database' => 32,
				], // connection ID
			'channel' => 'queue_' . WY_KEY_PREFIX, // queue channel
			'as log'  => \yii\queue\LogBehavior::class,
		],
		'cache'          => [
			'class'     => 'yii\redis\Cache',
			'redis'     =>
				[
					'hostname' => $redis_hostname,
					'port'     => $redis_port,
					'password' => $redis_password,
					'database' => 33,
				],
			'keyPrefix' => 'cache_' . WY_KEY_PREFIX . '_',
		],
		'session'        => [
			'class'     => 'yii\redis\Session',
			'redis'     =>
				[
					'hostname' => $redis_hostname,
					'port'     => $redis_port,
					'password' => $redis_password,
					'database' => 34,
				],
			'keyPrefix' => 'skey_' . WY_KEY_PREFIX . '_',
			'name'      => 'sname_' . WY_KEY_PREFIX . '_',
			'timeout'   => 86400, //24hrs
		],
		'log'    => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets'    => [
				[
					'class'  => 'yii\log\FileTarget',
					'levels' => ['error', 'warning', 'info'],
				],
				[
					'class'  => 'common\helpers\logs\DbTarget',
					'categories' => ['debug_*'],
					'levels' => ['error', 'warning', 'info'],
				],
				[
					'class'      => 'yii\log\EmailTarget',
					'levels'     => ['error', 'warning'],
					'categories' => ['yii\db\*'],
					'message'    => [
						'from'    => ['519525543@qq.com' => 'sysadmin'],
						'to'      => [ 'hyd@51bangbang.com.cn', 'lth@51bangbang.com.cn'],
						'subject' => '无忧帮帮新API数据库报错',
					],
				],
			],
		],
		'sms'            => [
			'class'           => 'common\components\sms\SMSComponent',
			'accessKey'       => 'LTAIp6ikha5CxnBS',
			'accessKeySecret' => '0lCtcxbc4JWkX0Jsb49nFTeCu2hqHW',
			'signName'        => '无忧帮帮',
		],
	],
];
