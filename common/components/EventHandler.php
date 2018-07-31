<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/6/8 14:31
 */
namespace common\components;

class EventHandler extends \yii\base\Component
{
    //TODO根据Yii2 规则编写 事件处理功能

    /**
     * @param $class \yii\base\Component
     */
    public static function routeEvents($class)
    {
        self::_routeControllerAPIEvents($class);
    }

    /**
     * @param $class
     * @return bool
     */
    private static function _routeControllerAPIEvents($class)
    {

        if (!isset($class)) return false;
        $class->on(Event::DEMO, function ($event) {
            self::_handle($event);
        });
    }


    /**
     * @param Event $event
     */
    private static function _handle(Event $event)
    {

        $handled = false;
        switch ($event->name) {

            case Event::DEMO:
                $handled = self::_handleTest($event);
                break;
            default:
                $handled = self::defaultLogEvent($event);
                break;
        }

        if (!$handled) {
            \Yii::getLogger()->log('Unhandled event ' . $event->name . ' from ' . get_class($event->sender), \yii\log\Logger::LEVEL_WARNING, 'events');
        }
    }

    public static function _handleTest($event)
    {

    }

    private static function defaultLogEvent($event)
    {

    }
}