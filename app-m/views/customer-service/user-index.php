<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/19
 */
 use yii\helpers\Url;
  $this->title = '客服中心';
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
                <img src="/static/use-customer-service/img/icon_order.png" alt="">
                <p>订单问题</p>
            </div>
            <a class="customer-font" href="<?= Url::to(['customer-service/user-problem','doc'=>'get_store']); ?>">
                <p class="">小帮到店无货取问题</p>
                <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
            <a class="customer-font" href="<?= Url::to(['customer-service/user-problem','doc'=>'not_create_order']); ?>">
                <p>未支付小帮服务费，不能下单</p>
                <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
        </div>

        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_pay.png" alt="">
                <p>费用问题</p>
            </div>
            <a class="customer-font" href="<?= Url::to(['customer-service/user-problem','doc'=>'how_charge']); ?>">
                <p class="">怎么收费的？</p>
                <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
            <a class="customer-font" href="<?= Url::to(['customer-service/user-problem','doc'=>'service_charge_unreasonable']); ?>">
                <p>订单服务不合理？</p>
               <p> <img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
            <a class="customer-font"  href="<?= Url::to(['customer-service/user-problem','doc'=>'automatic_deduction']); ?>">
                <p>平台会自动扣费？</p>
                <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
        </div>

        <div class="customer-body">
            <div class="customer-row">
                <img src="/static/use-customer-service/img/icon_delivery_1.png" alt="">
                <p>配送问题</p>
            </div>
            <a class="customer-font"  href="<?= Url::to(['customer-service/user-problem','doc'=>'how_claim']); ?>">
                <p class="">若配送物品损坏，如何赔偿？</p>
                <p><img src="/static/use-customer-service/img/icon_enter_service.png" alt=""></p>
            </a>
        </div>
    </div>
    <!-----
    <div class="customer-footer">
        <!--<div >-->
        <a class="footer-width"
       href="http://51bangbang.udesk.cn/im_client/?web_plugin_id=43064">
        </a>
        <a class="footer-second" href="tel:4001355188">
        </a>
    </div>
    ----->
</div>
</body>
</html>