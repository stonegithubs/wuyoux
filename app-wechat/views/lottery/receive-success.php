<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/25
 */

$title = '无忧帮帮';
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
<body style="background: #fff4a4;">
<div id="downTouch">
    <div class="cantanine" style="background: #fff4a4;">
    <img src="/static/lottery/img/raw/img_banner_get.png" width="100%" style="pointer-events:none;"/>
        <div class="libaoBox">
            <span class="leftFont">518大礼包已放入账户</span><span class="rightFont"><?= $mobile ?></span>
        </div>
        <div class="lineText">
            <div></div>
            <span>恭喜您获得了以下的优惠券</span>
            <div></div>
        </div>
        <div class="libaoContent">
        </div>

        <div class="useBtn">
            立即使用
        </div>
        <div class="footBox">
            <img style="height: 15%;width: 30%;left: 35%;" class="dianjiImg" src="/static/lottery/img/raw/btn_rules_two.png"/>         
        </div>
    </div>
</div>
<div class="activityBox" id="upTouch" style="display:none;">
     <div class="topBtn"> 
     <img class="dianjiImg" src="/static/lottery/img/raw/btn_activity.png"/>   
    <img class="bot_bg" src="/static/lottery/img/raw/img_bg_top.png" width="100%"/>
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

<script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    var order_no = "<?= $order_no ?>";
    var link = window.location.protocol+"//"+window.location.hostname+"/lottery/index?order_no="+order_no;

    wx.config(<?= $jssdk ?>);

    wx.ready(function () {
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
  $(document).ready(function(){
    $(".useBtn").click(function(){
      window.location.href="<?= $app_introduce ?>";
    })
    $(".topBtn").click(function(){
      $("#downTouch").slideToggle();
      $("#upTouch").css("display","none");

    });
    $(".footBox").click(function(){
      $("#upTouch").slideToggle();
      $("#downTouch").css("display","none");
    });
    var dataLength=<?= isset($card)? json_encode($card): 'null' ?>;
    for (var i = 0; i < dataLength.length; i++){
      if(dataLength[i].card_name=="通用券"){
        console.log(<?= isset($card)? json_encode($card): 'null' ?>);//红包金额 为0不显示红包
        $(".libaoContent").append('<div><span class="num quan2"><i>￥</i>'+dataLength[i].card_price+'</span><span class="quan">'+dataLength[i].card_name+'</span></div>')
      }
      if(dataLength[i].card_name=="快送券"){
        $(".libaoContent").append('<div><span class="num quan2"><i>￥</i>'+dataLength[i].card_price+'</span><span class="quan">'+dataLength[i].card_name+'</span></div>')
      }
      if(dataLength[i].card_name=="红包"){
        $(".libaoContent").append('<div><span class="num quan2"><i>￥</i>'+dataLength[i].card_price+'</span><span class="quan">'+dataLength[i].card_name+'</span></div>')
      }
      if(dataLength[i].card_name=="出行券"){
        $(".libaoContent").append('<div><span class="num quan2"><i>￥</i>'+dataLength[i].card_price+'</span><span class="quan">'+dataLength[i].card_name+'</span></div>')
      }
    }


  });

</script>
</html>