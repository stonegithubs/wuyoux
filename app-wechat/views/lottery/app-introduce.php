<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/25
 */
?>

<!DOCTYPE html>
<html>

<head>
    <title>立即安装用户版APP</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <link rel="stylesheet" href="/static/css/index.css">

</head>
<body>
   <div class="APPdownload">
       <img class="icon_APP" src="/static/lottery/img/icon_APP.png" alt="">
       <div class="bangTittle">
           <span class="topTittle">无忧帮帮</span>
           <span class="buttonTittle">用户版</span>
       </div>
  <div class="textMian">  
          无忧帮帮是一款专注于网约生活服务平台，旗下产品小帮出行、小帮快送，通过LBS定位为商家和用户提供精准匹配，让每个人发挥更大的社会价值和商业价值，无忧帮帮提供微信公众号、APP客户端等多个下单入口，用户可随时随地下单，提供按需服务。
  </div>
<img class="btn_download_android" src="/static/lottery/img/btn_download_android.png" alt="">
<img class="btn_download_apple" src="/static/lottery/img/btn_download_apple.png" alt="">
<img style="pointer-events:none;" class="yindaobeijin" src="/static/lottery/img/raw/img_download_bg.png"/>   


   </div>
</body>

<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script>
$(".btn_download_android").click(function(){
    window.location.href="http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.user&ADTAG=mobile";
    })
$(".btn_download_apple").click(function(){
    window.location.href="http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.user&ADTAG=mobile";
})
</script>

</html>