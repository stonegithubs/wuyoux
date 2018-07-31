<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/6/8 13:53
 */
namespace common\components;

use yii\web\Controller;

class ControllerBase extends Controller
{

    public function triggerEvent($name, $data = null)
    {
        $event = null;
        if ($data != null) {
            $event = new Event();
            $event->msg = $data;
        }
        $this->trigger($name, $event);
    }
}


