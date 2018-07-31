<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/12
 */

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <title>提现</title>
</head>
<body>
<div class="marketIndex">
    <div class="success">
        <img src="/static/market/img/ok.png" alt="">
        <h3>提现申请已提交</h3>
        <p>预计3个工作日内到账</p>
        <button onclick="backUrl()">我知道了</button>
    </div>
</div>
</body>
<script>
    function backUrl() {
        return window.location.href = "withdraw"
    }
</script>
</html>
