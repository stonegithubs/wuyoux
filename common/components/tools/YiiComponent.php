<?php

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 */
class Yii extends \yii\BaseYii
{
	/**
	 * @var BaseApplication|WebApplication|ConsoleApplication the application instance
	 */
	public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 *
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 * @property \common\components\tools\DebugComponent      $debug
 * @property \common\components\amap\AMapComponent        $amap
 * @property \common\components\baidu\LBSComponent        $lbs
 * @property \common\components\push\getui\GeTuiComponent $GTPushUser
 * @property \common\components\push\getui\GeTuiComponent $GTPushProvider
 * @property \common\components\alipay\AlipayComponent    $alipay
 * @property common\components\sms\SMSComponent           $sms
 * @property yii\queue\Queue                              $queue
 * @property \common\components\wechat\AppWeChat          $app_wechat
 * @property \common\components\wechat\MpWeChat           $mp_wechat
 * @property \common\components\wechat\UserAppWeChat      $user_app_wechat
 * @property \common\components\wechat\WorkerAppWeChat    $worker_app_wechat
 * @property \common\components\wechat\MiniBizWeChat      $mini_biz
 * @property yii\redis\Connection                         $redis
 */
class WebApplication extends yii\web\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 *
 */
class ConsoleApplication extends yii\console\Application
{
}