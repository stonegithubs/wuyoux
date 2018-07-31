<?php

use wechat\assets\ErrandAsset;

$asset = ErrandAsset::register($this);

?>
<!DOCTYPE html>
<html>

<head>
    <title>小帮快送</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <script src="//webapi.amap.com/maps?v=1.3&key=c4af304699451af317de903ac2a5b3f2"></script>
    <script src="//webapi.amap.com/ui/1.0/main.js"></script>
    <link rel="stylesheet" href="<?= $asset->baseUrl ?>/css/app.css?v=<?= PROJECT_VERSION ?>">
</head>

<body>
<div id="app">

    <input type="hidden" id="unionid" value="<?= $unionId ?>"/>
    <input type="hidden" id="openid" value="<?= $openId ?>"/>
    <input type="hidden" id="wx_access_token" value="<?= $wx_access_token ?>"/>
    <input type="hidden" id="access_token" value="<?= $access_token ?>"/>
    <input type="hidden" id="api_address" value="<?= $api_address ?>"/>
    <input type="hidden" id="role" value="<?= $role ?>"/>
    <input type="hidden" id="current_url" value="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>"/>

    <router-view></router-view>
</div>

<script src="<?= $asset->baseUrl ?>/js/manifest.js?v=<?= PROJECT_VERSION ?>"></script>
<script src="<?= $asset->baseUrl ?>/js/vendor.js?v=<?= PROJECT_VERSION ?>"></script>
<script src="<?= $asset->baseUrl ?>/js/app.js?v=<?= PROJECT_VERSION ?>"></script>
</body>

</html>