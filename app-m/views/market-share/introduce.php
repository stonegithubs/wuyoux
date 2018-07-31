<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/14
 */

use yii\helpers\Url;

?>

<!DOCTYPE html>
<html>

<head>
    <title>全民合伙人</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <link rel="stylesheet" href="/static/register/css/web-share.css">
</head>
<body>
 <div class="introduce_box">
 <img class="img_home" src="/static/register/img/img_home.png" alt="">
 <img onclick="toRegister()" class="foot_joinBtn" src="/static/register/img/joinBtn.png" alt="">
 </div>
</body>

</html>

<script>
    function toRegister() {
        window.location.href = "<?= Url::to(['market-share/register?market_code=']); ?>" + "<?= $data['market_code'] ?>" + '&entrance=' + "<?= $data['entrance'] ?>";
    }
</script>


