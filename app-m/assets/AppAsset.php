<?php

namespace m\assets;

use yii\web\AssetBundle;

/**
 * Main app-backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       /* 'css/site.css',
		'static/css/framework7.ios.min.css',
		'static/css/framework7.ios.colors.min.css',
		'static/css/my-app.css',*/
    ];
    public $js = [
    	/*'static/js/jquery.min.js',
		'static/js/framework7.min.js',
		'static/js/my-app.js',*/
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
