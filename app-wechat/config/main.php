<?php
$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id'                  => 'app-wechat',
	'basePath'            => dirname(__DIR__),
	'bootstrap'           => ['log'],
	'controllerNamespace' => 'wechat\controllers',
	'components'          => [
		'request'      => [
			'csrfParam' => '_csrf-wechat',
		],
		'user'         => [
			'identityClass'   => 'common\models\User',
			'enableAutoLogin' => true,
			'identityCookie'  => ['name' => '_identity-wechat', 'httpOnly' => true],
		],
		'session'      => [
			// this is the name of the session cookie used for login on the wechat
			'name' => 'advanced-wechat',
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
		'urlManager'   => [
			'enablePrettyUrl' => true,
			//			'enableStrictParsing' => true,
			'showScriptName'  => false,
			'rules'           => [
				'trip'                                   => 'wx/trip',
				'errand'                                 => 'wx/errand',
				'biz'                                    => 'wx/biz',
				''                                       => 'wx/trip',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
				'<module:\w+>/<controller>/<action>'     => '<module>/<controller>/<action>'
			],
		],
	],
	'params'              => $params,
];
