<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/27
 */


use yii\helpers\Url;

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/static/market/css/web-share.css">
    <title>全民合伙人</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>

</head>
<body>
<div class="introduce_box">
    <img class="img_home" src="/static/market/img/img_home.png" alt="">
    <a class="foot_joinBtn" href="<?= Url::to(['site/login']); ?>">
        <button style="font-size: 1rem">我要成为合伙人</button>
    </a>
</div>
</body>

</html>

