<?php
return yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/main.php'),
	require(__DIR__ . '/main-local.php'),
	require(__DIR__ . '/test.php'),
	[
		'components' => [
			'db' => [
				'dsn' => 'mysql:host=localhost;dbname=wuyoubangbang_test',
				'username'    => 'root',
				'password'    => '123456',
				'charset'     => 'utf8',
				'tablePrefix' => 'wy_' //新架构数据表都是这个前缀
			]
		],
	]
);
