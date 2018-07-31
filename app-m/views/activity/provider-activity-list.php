<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/23
 */
use yii\helpers\Html;
$this->title = '活动中心';
?>
<link rel="stylesheet" href="/static/message/css/message.css">
<style>
    .date-time {
        text-align: center;
    }
    .page-list {
        padding-top: 31px;
    }
</style>
<!-- Views-->
<div class="views">
    <!-- Your main view, should have "view-main" class-->
    <div class="view view-main">
        <!-- Pages, because we need fixed-through navbar and toolbar, it has additional appropriate classes-->
        <div class="pages navbar-through toolbar-through">
            <!-- Page, data-page contains page name-->
            <div data-page="index" class="page">
                <!-- Scrollable page content-->
                <div class="page-content infinite-scroll">
                    <div class="page-list">
                        <ul>
                        </ul>
                    </div>
                    <div class="infinite-scroll-preloader">
                        <ul class="list-unstyled">
                            <li>
                                <div class="preloader"></div>
                            </li>
                            <li><span>内容加载中</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= Html::jsFile("/static/js/framework7.min.js") ?>
<?= Html::jsFile("/static/activity/js/list.js",[
        'id'=>'base',
        'data'=>json_encode([
                'requestUrl'=>$params['requestUrl'],
                'requestType'=>"get",
                'requestData'=>$params['requestData'],
                'requestMaxNum'=>$params['num'],
                'requestPageSize'=>$params['pageSize'],
        ])])
?>
