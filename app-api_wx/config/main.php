<?php
$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id'                  => 'app-api',
	'basePath'            => dirname(__DIR__),
	'bootstrap'           => ['log'],
	'controllerNamespace' => 'api\controllers',
	'modules'             => [
		'mpv1'  => [
			'basePath'            => '@api_wx/modules/mpv1',
			'class'               => 'api_wx\modules\mpv1\Module',
			'controllerNamespace' => 'api_wx\modules\mpv1\controllers',
		],
		'biz' => [
			'basePath'            => '@api_wx/modules/biz',
			'class'               => 'api_wx\modules\biz\Module',
			'controllerNamespace' => 'api_wx\modules\biz\controllers',
		],
	],
	'components'          => [
		'user'    => [
			'identityClass'   => 'common\models\User',
			'enableAutoLogin' => false,
			'enableSession'   => false,
			'loginUrl'        => null
		],
		'request' => [
			'enableCsrfValidation'   => false,
			'enableCookieValidation' => false,
		],
		'session' => [
			// this is the name of the session cookie used for login on the frontend
			'name' => 'app-api_wx',
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
				'<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
				'<module:\w+>/<controller>/<action>'     => '<module>/<controller>/<action>'
			],
		],
	],

	'params' => $params,
];
