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

    <link rel="stylesheet" href="/static/market/css/web-share.css">
    <link rel="stylesheet" href="/static/market/css/index.css">
</head>
<body>
<div class="inputPwd active visible hide mengceng">
    <img src="/static/market/img/img_guide.png" alt="" style="width: 80%;right: .5rem;position: absolute;">
</div>
<div class="main_box">
    <img class="manneimg" src="/static/market/img/img_join_succeed.png" alt="">
    <div class="footer_btn">
        <div id="fenxiang">分享赚钱</div>
        <div onclick="goDownload()">点击下载</div>
        <textarea style="" cols="10" rows="1" id="biao1"></textarea>
    </div>

</div>
<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script src="//res.wx.qq.com/open/js/jweixin-1.2.0.js?r=woeo" type="text/javascript" charset="utf-8"></script>

<script>

  var entrance = "<?=$data['entrance']?>"
  $("#fenxiang").click(function () {
    $(".mengceng").show();
  })
  $(".mengceng").click(function () {
    $(this).hide();
  })


  function goDownload() {
    if (entrance == 2) {
      window.location.href = "http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.business"
    } else {
      window.location.href = "http://android.myapp.com/myapp/detail.htm?apkName=com.wybb.user"
    }
  }


  wx.config(<?= $data['jssdk'] ?>);

  wx.ready(function () {

    var share_link = "<?= $data['wechat_share_url'] ?>";
    var share_pic = "<?= $data['share_pic'] ?>";
    var share_title = "Hi，朋友，无忧帮帮招募合伙人啦！";
    var share_desc = "零投入，零风险，月收入过万。接的多，邀的多，赚的多，分享赚更多";

    //分享到朋友圈
    wx.onMenuShareTimeline({
      title: share_title,
      imgUrl: share_pic,
      link: share_link,
      success: function () {

      },
      cancel: function () {

      }
    });

    //分享给朋友
    wx.onMenuShareAppMessage({

      title: share_title,
      imgUrl: share_pic,
      link: share_link,
      desc: share_desc,
      success: function () {

      },
      cancel: function () {

      }
    });

    //分享到QQ
    wx.onMenuShareQQ({
      title: share_title,
      desc: share_desc,
      imgUrl: share_pic,
      link: share_link,
      success: function () {

      },
      cancel: function () {

      }
    });
  });

</script>

</body>
</html>