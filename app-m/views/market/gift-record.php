<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/7/5
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/vue.js"></script>
    <title>红包记录</title>
</head>
<body>
<div class="marketIndex gift-record" id="giftRecord">
    <div class="record-header" align="center">
        <img src="/static/market/img/icon_gold.png" alt="">
        <p>{{user_name}}</p>
        <p>您共收到</p>
        <div class="strong">
            <p class="litter-strong">￥</p>
            <p>{{sum}}</p>
        </div>
    </div>

    <div class="gift-body">
    <div align="left" class="record-number">领取红包个数 {{count}}</div>
    <div class="red-detail" align="left" v-if="record" v-for="list in record">
        <div class="detail-part">
            <p>{{list.invite_name}} 帮你获得</p>
            <p>￥{{list.gift_amount}}</p>
        </div>
        <span>{{list.create_time}}</span>
    </div>
    </div>
</div>
</body>
<script>
    var data =<?=json_encode($data) ?>;
    new Vue({
        el:"#giftRecord",
        data:data
    })
</script>
</html>
