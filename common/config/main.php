<?php
return [
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => [
		'debug'             => [
			'class' => 'common\components\tools\DebugComponent',
		],
		'app_wechat'        => [
			'class' => 'common\components\wechat\AppWeChat',
			// 'userOptions' => []  # user identity class params
			// 'sessionParam' => '' # wechat user info will be stored in session under this key
			// 'returnUrlParam' => '' # returnUrl param stored in session
		],
		'mp_wechat'         => [
			'class'          => 'common\components\wechat\MpWeChat',
			//'sessionParam'   => '_wechatUser_mp', # wechat user info will be stored in session under this key
			//'returnUrlParam' => '_wechatReturnUrl_mp' # returnUrl param stored in session
		],
		'worker_app_wechat' => [
			'class'          => 'common\components\wechat\WorkerAppWeChat',
			//'sessionParam'   => '_wechatUser_worker_app', # wechat user info will be stored in session under this key
			//'returnUrlParam' => '_wechatReturnUrl_worker_app' # returnUrl param stored in session
		],
		'user_app_wechat' => [
			'class'          => 'common\components\wechat\UserAppWeChat',
			//'sessionParam'   => '_wechatUser_user_app', # wechat user info will be stored in session under this key
			//'returnUrlParam' => '_wechatReturnUrl_user_app' # returnUrl param stored in session
		],
		'mini_biz' => [
			'class'          => 'common\components\wechat\MiniBizWeChat',
			//'sessionParam'   => '_wechatUser_user_app', # wechat user info will be stored in session under this key
			//'returnUrlParam' => '_wechatReturnUrl_user_app' # returnUrl param stored in session
		]
	],
	'aliases'    => [
		'@Aliyun' => '@common/components/sms/ApiSdk/lib',
	],
];