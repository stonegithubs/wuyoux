<?php
/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
return [
	'Testing' => [
		'path' => 'beta',
		'setWritable' => [
			'app-api_user/runtime',
			'app-api_user/web/assets',
			'app-api_worker/runtime',
			'app-api_worker/web/assets',
			'app-api_wx/runtime',
			'app-api_wx/web/assets',
			'app-api/runtime',
			'app-api/web/assets',
			'app-pay/runtime',
			'app-pay/web/assets',
			'app-wechat/runtime',
			'app-wechat/web/assets',
			'app-backend/runtime',
			'app-backend/web/assets',
			'app-frontend/runtime',
			'app-frontend/web/assets',
			'app-m/runtime',
			'app-m/web/assets',
		],
		'setExecutable' => [
			'yii',
			'yii_test',
		],
		'setCookieValidationKey' => [
			'app-api_user/config/main-local.php',
			'app-api_worker/config/main-local.php',
			'app-api_wx/config/main-local.php',
			'app-api/config/main-local.php',
			'app-wechat/config/main-local.php',
			'app-pay/config/main-local.php',
			'app-backend/config/main-local.php',
			'app-frontend/config/main-local.php',
			'app-m/config/main-local.php',
		],
	],
    'Development' => [
		'path' => 'dev',
		'setWritable' => [
			'app-api_user/runtime',
			'app-api_user/web/assets',
			'app-api_worker/runtime',
			'app-api_worker/web/assets',
			'app-api_wx/runtime',
			'app-api_wx/web/assets',
			'app-api/runtime',
			'app-api/web/assets',
			'app-pay/runtime',
			'app-pay/web/assets',
			'app-wechat/runtime',
			'app-wechat/web/assets',
			'app-backend/runtime',
			'app-backend/web/assets',
			'app-frontend/runtime',
			'app-frontend/web/assets',
			'app-m/runtime',
			'app-m/web/assets',
		],
		'setExecutable' => [
			'yii',
			'yii_test',
		],
		'setCookieValidationKey' => [
			'app-api_user/config/main-local.php',
			'app-api_worker/config/main-local.php',
			'app-api_wx/config/main-local.php',
			'app-api/config/main-local.php',
			'app-wechat/config/main-local.php',
			'app-pay/config/main-local.php',
			'app-backend/config/main-local.php',
			'app-frontend/config/main-local.php',
			'app-m/config/main-local.php',
		],
    ],
    'Production' => [
		'path' => 'prod',
		'setWritable' => [
			'app-api_user/runtime',
			'app-api_user/web/assets',
			'app-api_worker/runtime',
			'app-api_worker/web/assets',
			'app-api_wx/runtime',
			'app-api_wx/web/assets',
			'app-api/runtime',
			'app-api/web/assets',
			'app-pay/runtime',
			'app-pay/web/assets',
			'app-wechat/runtime',
			'app-wechat/web/assets',
			'app-backend/runtime',
			'app-backend/web/assets',
			'app-frontend/runtime',
			'app-frontend/web/assets',
			'app-m/runtime',
			'app-m/web/assets',
		],
		'setExecutable' => [
			'yii',
			'yii_test',
		],
		'setCookieValidationKey' => [
			'app-api_user/config/main-local.php',
			'app-api_worker/config/main-local.php',
			'app-api_wx/config/main-local.php',
			'app-api/config/main-local.php',
			'app-wechat/config/main-local.php',
			'app-pay/config/main-local.php',
			'app-backend/config/main-local.php',
			'app-frontend/config/main-local.php',
			'app-m/config/main-local.php',
		],
    ],
];
