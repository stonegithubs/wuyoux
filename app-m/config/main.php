<?php
$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id'                  => 'app-backend',
	'basePath'            => dirname(__DIR__),
	'controllerNamespace' => 'm\controllers',
	'bootstrap'           => ['log'],
	'modules'             => [],
	'components'          => [
		'request'      => [
			'csrfParam' => '_csrf-m',
		],
		'user'         => [
			'identityClass'   => 'common\models\User',
			'enableAutoLogin' => true,
			'identityCookie'  => ['name' => '_identity-backend', 'httpOnly' => true],
		],
		'session'      => [
			// this is the name of the session cookie used for login on the backend
			'name' => 'advanced-m',
		],
		'log'          => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets'    => [
				[
					'class'  => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],

		'urlManager' => [
			'enablePrettyUrl' => true,
			//			'enableStrictParsing' => true,
			'showScriptName'  => false,
			'rules'           => [
				''                                       => 'site/index',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				's/<order_no:\w+>'                       => 'schedule/index',
				'ms/<market_code:\w+>'                   => 'market-share/introduce',
				'mk/qr'                                  => 'market-share/open-introduce',
				'<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
				'<module:\w+>/<controller>/<action>'     => '<module>/<controller>/<action>'
			],
		],
	],
	'params'              => $params,
];
