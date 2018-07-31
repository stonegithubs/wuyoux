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
	<img class="leftPicture" src="<?= $providerInfo['provider_photo'] ?>"/>
	<div class="centerText">
		<p><?= $providerInfo['provider_name'] ?></p>
		<div class="pingfenDiv">
			<img src="/static/schedule/img/icon_stars.png"/>
			<span><?= $providerInfo['provider_star'] ?></span>
		</div>
	</div>
	<a href="tel:<?= $providerInfo['provider_mobile'] ?>"><img class="rightImg" src="/static/schedule/img/btn_telephone.png"/></a>
</div>
<div class="footerDiv">
	<img class="leftPicture" src="/static/schedule/img/icon_logo.png"/>
	<div class="centerText">
		<span class="toptext">无忧帮帮</span>
		<span class="bottext">让网约更方便</span>
	</div>
	<div class="rightImg">
		<a href="<?= $download_link ?>" style="text-decoration:none;color: #fff;">立即打开</a>
	</div>
</div>

<script>
    var center_lng = <?= $providerInfo['provider_lng'] ?>;
    var center_lat = <?= $providerInfo['provider_lat'] ?>;
    var map = new AMap.Map('container', {
        resizeEnable: true,
        zoom:13,
        center: [center_lng, center_lat]
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
</script>
</body>
</html>