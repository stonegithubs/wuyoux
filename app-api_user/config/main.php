<?php
$params = array_merge(
	require(__DIR__.'/../../common/config/params.php'),
	require(__DIR__.'/../../common/config/params-local.php'),
	require(__DIR__.'/params.php'),
	require(__DIR__.'/params-local.php')
);

return [
	'id'                  => 'app-api_user',
	'basePath'            => dirname(__DIR__),
	'bootstrap'           => ['log'],
	'controllerNamespace' => 'api_user\controllers',
	'modules'             => [
		'v1' => [
			'basePath'            => '@api_user/modules/v1',
			'class'               => 'api_user\modules\v1\Module',
			'controllerNamespace' => 'api_user\modules\v1\controllers',
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
			'name' => 'advanced-api',
		],

		'errorHandler' => [
			'errorAction' => 'site/error',
		],

		'urlManager' => [
			'enablePrettyUrl'     => true,
//			'enableStrictParsing' => true,
			'showScriptName'      => false,
			'rules'               => [
				''                                       => 'site/index',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
				'<module:\w+>/<controller>/<action>'     => '<module>/<controller>/<action>'
			],
		],
	],

	'params' => $params,
];
