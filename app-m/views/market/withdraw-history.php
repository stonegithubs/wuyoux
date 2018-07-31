<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/14
 */

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/vue.js"></script>
    <title>提现记录</title>
</head>
<body>
<div class="marketIndex withdraw-height" id="withdrawList" v-cloak>
    <div v-if="!withdrawList" class="today-profit">
        <img src="/static/market/img/icon_blank_page.png" alt="">
        <p>暂无提现记录</p>
    </div>
    <div v-else>
        <div align="left" class="tip">提示：显示最近10条提现信息</div>
        <div class="withdraw-body">
            <div class="withdraw-list" v-for="list in withdrawList">
                <div class="withdraw-first">
                    <p>提现类型：{{list.transfer_accountname}}</p>
                    <p v-if="list.status==0" class="warn">待处理</p>
                    <p v-if="list.status==1" class="confirm">提现成功</p>
                    <p v-if="list.status==2" class="warn">提现失败</p>
                </div>
                <div align="left" class="withdraw-second">
                    <p>提现账号：{{list.transfer_account}}</p>
                    <p>申请时间：{{list.apply_time}}</p>
                    <p v-if="list.transfer_time">提现时间：{{list.transfer_time}}</p>
                    <p>提现金额：<b class="font-w">{{list.transfer_amount}}</b></p>
                    <p v-if="list.status==2" >处理结果：{{list.remark}}</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    var data =<?=json_encode($data) ?>;
    new Vue({
        el: "#withdrawList",
        data: {
            withdrawList: data
        }
    })
</script>


</html>
