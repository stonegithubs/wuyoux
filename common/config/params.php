<?php

if (YII_DEBUG == true && !defined('PROJECT_VERSION')) {
	define('PROJECT_VERSION', time());
} elseif (YII_DEBUG == false && !defined('PROJECT_VERSION')) {
	define('PROJECT_VERSION', '20170930');
}

return [
	'adminEmail'                    => 'admin@example.com',
	'supportEmail'                  => 'support@example.com',
	'user.passwordResetTokenExpire' => 3600,
	'APP_WECHAT'                    => require(__DIR__ . '/wechat/app/config.php'),
	'MP_WECHAT'                     => require(__DIR__ . '/wechat/mp/config.php'),
	'WORKER_APP_WECHAT'             => require(__DIR__ . '/wechat/worker-app/config.php'),
	'USER_APP_WECHAT'               => require(__DIR__ . '/wechat/user-app/config.php'),
	'MINI_BIZ_WECHAT'               => require(__DIR__ . '/wechat/min-app-biz/config.php'),
];
