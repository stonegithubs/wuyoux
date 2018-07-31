<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/18
 */
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <title>无忧帮帮配送进度</title>
    <link rel="stylesheet" type="text/css" href="/static/css/index.css">
    <link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>
    <script src="//cache.amap.com/lbs/static/es5.min.js"></script>
    <script src="//webapi.amap.com/maps?v=1.4.5&key=c4af304699451af317de903ac2a5b3f2"></script>
</head>
<body>
<div id="container"></div>
<div class="topDiv">
<img class="topDivImg leftPicture" src="<?= $providerInfo['provider_photo'] ?>"/>
<div class="topDivImg centerText">
<p><?= $providerInfo['provider_name'] ?></p>
<div class="topDivImg pingfenDiv">
<img src="/static/schedule/img/icon_stars.png"/>
<span><?= $providerInfo['provider_star'] ?></span>
</div>
</div >
    <a class="topDivImg" href="tel:<?= $providerInfo['provider_mobile'] ?>"><img class="rightImg" src="/static/schedule/img/btn_telephone.png"/></a>
</div>
<div class="footerDiv">
<div class="footerPart" style="width:65%">
<img class="leftPicture" src="/static/schedule/img/icon_logo.png"/>
<div class="centerText">
<span class="toptext">无忧帮帮</span>
<span class="bottext">让网约更方便</span>
</div>
</div>
<div class="footerPart rightImg" style="width:30%">
    <a href="<?= $download_link ?>" style="text-decoration:none;color: #fff;">立即打开</a>
</div>
</div>
<div id="ditutip">
    <p class="left">距您<?= $route['ending_distance'] ?></p>
    <p class="right"><?= $route['spend_time'] ?>到达</p>
    <img src="/static/schedule/img/line.png" alt="" style="position: absolute;left: 51%;height: 19px;">
    <img src="/static/schedule/img/sanjiao.png" alt="" style="position: absolute;left: 38%;bottom: -23%;">
</div>
<script>
    var distance = scale2Zoom("<?= $route['distance'] ?>");
    var center_lng = (<?= $providerInfo['provider_lng'] ?> + <?= $providerInfo['end_location_lng'] ?>) / 2;
    var center_lat = (<?= $providerInfo['provider_lat'] ?> + <?= $providerInfo['end_location_lat'] ?>) / 2;
    var map = new AMap.Map('container', {
        resizeEnable: true,
        zoom:distance+2,
        center: [center_lng, center_lat]
    });

    //终点图标
    new AMap.Marker({
        map: map,
        position: <?= $providerInfo['end_location'] ?>,
        icon: new AMap.Icon({
            size: new AMap.Size(48*0.40,78*0.40),
            image: "/static/schedule/img/icon_coordinate.png",
            imageOffset: new AMap.Pixel(0, 0),
            imageSize: new AMap.Size(48*0.40,78*0.40)
        })
    });
    var content = document.getElementById('ditutip');
new AMap.Marker({
    content: content,
    position: <?= $providerInfo['provider_location'] ?>,
    offset: new AMap.Pixel(-80, -80),
    map: map
});


    //小帮图标
    new AMap.Marker({
        map: map,
        position: <?= $providerInfo['provider_location'] ?>,
        icon: new AMap.Icon({
            size: new AMap.Size(70*0.40, 91*0.40),
            image: "/static/schedule/img/map_running_man.png",
            imageOffset: new AMap.Pixel(0, 0),
            imageSize: new AMap.Size(70*0.40, 91*0.40)
        })
    });

    function scale2Zoom(scale) {
        if (scale <= 10) return 19;
        else if (scale <= 25) return 18;
        else if (scale <= 50) return 17;
        else if (scale <= 100) return 16;
        else if (scale <= 200) return 15;
        else if (scale <= 500) return 14;
        else if (scale <= 1000) return 13;
        else if (scale <= 2000) return 12;
        else if (scale <= 5000) return 11;
        else if (scale <= 10000) return 10;
        else if (scale <= 20000) return 9;
        else if (scale <= 30000) return 8;
        else if (scale <= 50000) return 7;
        else if (scale <= 100000) return 6;
        else if (scale <= 200000) return 5;
        else if (scale <= 500000) return 4;
        else if (scale <= 1000000) return 3;
        else if (scale > 1000000) return 2;
        return 20;
    }
</script>
</body>
</html>