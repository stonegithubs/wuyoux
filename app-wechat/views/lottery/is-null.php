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
    <title>518无忧帮帮周年大礼包</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <link rel="stylesheet" href="/static/css/index.css">

</head>
<body>
<div id="downTouch">
    <div class="cantanine">
        <img src="/static/lottery/img/raw/img_banner.png" width="100%" onclick="return false"/>
        <div class="bg_img">
        <img class="gifts" src="/static/lottery/img/raw/img_gift.png" width="100%"/>
        <p style="margin-top: 24px;padding: 17px 12%;">很遗憾您未能抢到礼包，不要灰心，还有机会，打开<span style="color:rgb(255,96,0)">APP</span>体验一把先！</p>
        <div class="giftBtn">
        立即打开APP
        </div>
        </div>
    </div>
    <div class="footBox" style="position: relative;">
            <img style="position: absolute;bottom: 30px;width: 40%;left: 30%;" class="dianjiImg" src="/static/lottery/img/raw/btn_rules.png"/>         
            <img style="margin-bottom: -5px;pointer-events:none;" class="bot_bg" src="/static/lottery/img/raw/img_banner_bottom.png" width="100%"/>
        </div>
</div>
<div class="activityBox" id="upTouch" style="display:none;">
     <div class="topBtn"> 
     <img class="dianjiImg" src="/static/lottery/img/raw/btn_activity.png"/>   
    <img class="bot_bg" style="pointer-events:none;" src="/static/lottery/img/raw/img_bg_top.png" width="100%"/>
    </div>   
  <div class="text_main">
    <div class="huodongTip">
        <div></div>
        <p>活动规则</p>
        <div></div>
    </div>

    <div class="shuomingTip">
        <p>活动说明</p>
    </div>
    <div class="mainBox">
        <span>1、红包新老用户同享；</span>
        <span>2、下单完成评价即可分享给小伙伴；</span>
        <span>3、每个链接会按领取顺序随机发放一个大额红包；</span>
        <span>4、红包仅限在线支付使用，每张订单仅限使用一张，红包不找零；</span>
        <span>5、每位用户每天至多可以领取5个红包；</span>
        <span>6、使用红包时的下单手机号需为抢红包时使用的手机号；</span>
        <span>7、发放至无忧帮帮账号的红包登录后即可使用；</span>
        <span>8、发放至手机号的红包需在无忧帮帮APP或无忧帮帮微信公众号注册后才可使用；</span>
        <span>9、其他未尽事宜，请咨询客服；</span>
    </div>
    </div>
    <img class="bot_bg" src="/static/lottery/img/raw/Iimg_logo_bottom.png" width="100%"/>
</div>
</body>

</html>

<script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    var longitude;
    var latitude;
    var order_no = "<?= $order_no ?>";

    var link = window.location.protocol+"//"+window.location.hostname+"/lottery/index?order_no="+order_no;

    wx.config(<?= $jssdk ?>);

    wx.ready(function () {
        //获取地理位置接口
        wx.getLocation({
            type: 'wgs84',
            success: function (res) {
                longitude = res.longitude;
                latitude = res.latitude;
            }
        });

        //分享到朋友圈
        wx.onMenuShareTimeline({
            title: '518无忧帮帮周年大礼包',
            imgUrl: "<?= $share_logo ?>", // 分享图标
            link: link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            success: function () {

            },
            cancel: function () {

            }
        });

        //分享给朋友
        wx.onMenuShareAppMessage({
            title: '518无忧帮帮周年大礼包',
            desc: '无忧帮帮-让网约更方便！帮我买、帮我送、帮我办，手机下单，越下越优惠！',
            imgUrl: "<?= $share_logo ?>",  // 分享图标
            link: link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            success: function () {

            },
            cancel: function () {

            }
        });
    });
</script>

<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script>
  $("#btn_close").click(function(){
    $(".registerMain").hide();
    $(".registerBox").hide();
  });
  $(".topBtn").click(function(){
    $("#downTouch").slideToggle();
    $("#upTouch").css("display","none");

  });
  $(".dianjiImg").click(function(){
    $("#upTouch").slideToggle();
    $("#downTouch").css("display","none");
  });
  $(".giftBtn").click(function(){
      window.location.href="<?= $app_introduce ?>";
    })
</script>