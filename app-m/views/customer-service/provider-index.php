<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/19
 */
 $this->title = '客服中心';
use yii\helpers\Url;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/static/use-customer-service/css/userCustomerService.css">
    <title>客服中心</title>
</head>
<body>
<div class="container">
    <div class="customer">
        <p class="h1">常见问题</p>
        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_login.png" alt="">
                <p>登录注册问题</p>
            </div>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'register_code']); ?>">
                <p>注册时收不到验证码</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'register_position']); ?> ">
                <p class="">注册会提示“定位失败，请返回重试”</p>
                <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
        </div>

        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_push.png" alt="">
                <p>推送问题</p>
            </div>
            <a class="customer-font" href="<?= Url::to(['customer-service/provider-problem','doc'=>'push_error']); ?>">
                <p class="">小帮接收不到订单推送</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
        </div>

        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_location.png" alt="">
                <p>定位问题</p>
            </div>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'position_deviation']); ?>">
                <p class="">小帮定位位置会有偏差偿？</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'biz_address_deviation']); ?>">
                <p class="">企业送订单，小帮配送到达后生成的地址有偏差</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
        </div>
        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_order.png" alt="">
                <p>订单问题</p>
            </div>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'user_info_incomplete']); ?>">
                <p class="">小帮抢单后界面位置及用户信息等显示不全</p>
                <p> <img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'order_num_inaccurate']); ?>">
                <p class="">今日接单有5单，但怎么显示4单</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
        </div>
        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_delivery.png" alt="">
                <p>配送费用问题</p>
            </div>
            <a class="customer-font" href="<?= Url::to(['customer-service/provider-problem','doc'=>'not_account']); ?>">
                <p class="">订单配送完成后，配送费用没有到账</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
        </div>
        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_other.png" alt="">
                <p>其他问题</p>
            </div>
            <a class="customer-font"
               href="<?= Url::to(['customer-service/provider-problem','doc'=>'power_problem']); ?>">
                <p class="">小帮版耗电问题</p>
                 <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""> </p>
            </a>
        </div>
    </div>
    <!-----
    <div class="customer-footer">
        <!--<div >-->
        <a class="footer-width" href="http://51bangbang.udesk.cn/im_client/?web_plugin_id=43064">
        </a>
        <a class="footer-second" href="tel:4001355188">
        </a>
    </div>
   ------- >
</div>
</body>
</html>
