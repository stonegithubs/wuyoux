<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/13
 */
?>

<!DOCTYPE html>
<html>

<head>
    <title>注册成功</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <link rel="stylesheet" href="/static/register/css/web-share.css">
</head>
<body>
 <div class="main_box">
 <img class="manneimg" src="/static/register/img/img_join_succeed.png" alt="">
 <div class="footer_btn">
 <div id="fenxiang">分享链接</div>
 <div onclick="goDownload()">点击下载</div>
 <textarea readonly=”readonly” cols="10" rows="1" id="biao1"></textarea>
 </div>
 
 </div>
</body>

</html>

<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script type="text/javascript">
 var data =<?=json_encode($data) ?>;
  var entrance = data.entrance;
   $("#biao1").text(data.web_share_url);
   $("#fenxiang").click(function(){
    copyUrl2();
    })
    function copyUrl2()
{
var Url2=document.getElementById("biao1");
Url2.select(); // 选择对象
document.execCommand("Copy"); // 执行浏览器复制命令
showToast();
}

 function showToast(text) {
        $('.main_box').append('<div class="jdialog-toast"><span>复制链接成功！</span><span>马上去分享给好友吧！</span></div>');
        setTimeout(function () {
            $('.jdialog-toast').remove();
        },2000);
    }

  function goDownload() {
      if (entrance==1) {
        window.location.href="http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.user"
      }
      if (entrance==2) {
        window.location.href="http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.business"
      }else {
        window.location.href="http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.user"
      }
  }
</script>