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
<div class="registerMain" style="display:none;">
</div>
<div id="downTouch">
    <div class="registerBox" style="display:none;">
        <div class="header">您还不是用户，完成注册领取
            <img src="/static/lottery/img/btn_close.png" alt="" id="btn_close">
        </div>
        <div class="registerInput">
            <div><img src="/static/lottery/img/icon_phone.png" alt=""><input id="registerMobile" type="number" oninput="if(value.length>11)value=value.slice(0,11)" placeholder="请输入手机号码"></div>
            <div><img src="/static/lottery/img/icon_message.png" alt=""><input id="codeNum" type="text" placeholder="请输入验证码">
                <span class="erificationCode" onclick="setTime()">获取验证码</span><span class="erificationCodeAc" style="display:none;">再次获取(<span id="miaotext">60</span>)</span>
            </div>
            <div><img src="/static/lottery/img/icon_password.png" alt=""><input id="password" type="password" oninput="if(value.length>16)value=value.slice(6,16)" placeholder="请输入密码"></div>
            <span class="mimatip" style="display:none;">密码需要由6-16位字母和数字组合</span>
        </div>
        <div class="registeBtn" onclick="ajax_register_receive()">注册</div>
    </div>
    <div class="cantanine">
        <img src="/static/lottery/img/raw/img_banner.png" width="100%" onclick="return false"/>
        <div class="bg_img">
        <img class="gifts" style="pointer-events:none;" src="/static/lottery/img/raw/img_gift.png" width="100%"/>
        <p>领取无忧帮帮518大礼包</p>

        <div class="inputBox">
            <input type="number" id="inputMobile" oninput="if(value.length>11)value=value.slice(0,11)"  placeholder="请输入手机号码">
        </div>
        <div class="giftBtn" onclick="clickGiftBtn()">
            立即领取大礼包
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
    <img class="bot_bg" style="pointer-events:none;" src="/static/lottery/img/raw/Iimg_logo_bottom.png" width="100%"/>
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
  /**
   * ajax领取
   */
  function clickGiftBtn() {
    var inputMobile =  $("#inputMobile").val();
    if (inputMobile.length!==11) {
      showToast1("请输入正确的手机号码")
    } 
   else {
      ajax_receive();
    }
  }

  function ajax_receive() {
    var ajax_login_receive = "<?= $ajax_receive ?>";
    var inputMobile =  $("#inputMobile").val();
    $.ajax({
      url: ajax_login_receive,
      type: 'get',
      data: {
        mobile: inputMobile,
        order_no: order_no
      },
      dataType: 'json',
      success: function (res) {
        if(res.data.lottery_status==2){
          window.location.href="<?= $is_received ?>&mobile="+inputMobile;
        }
        if(res.data.lottery_status==3){
          window.location.href="<?= $is_null ?>";
        }
        if(res.data.lottery_status==4){
          window.location.href="<?= $receive_success ?>&mobile="+inputMobile;
        }
        if(res.data.lottery_status==5){
          window.location.href="<?= $over_num ?>";
        }
        if(res.status==90005){
          $(".registerMain").show();
          $(".registerBox").show();
        }
      },
      fail: function (res) {
        //console.log(res);
      }
    });
  }


  /**
   * ajax发送验证码(注册/快捷登录)
   */
  function ajax_get_code() {
    var ajax_get_code = "<?= $ajax_get_code ?>";
    var registerMobile=$("#registerMobile").val();
    $.ajax({
      url: ajax_get_code,
      type: 'get',
      data: {
        mobile: registerMobile,
      },
      dataType: 'json',
      success: function (res) {
        showToast(res.msg);
      },
      fail: function (res) {
        showToast(res.msg);
      }
    });
  }

  /**
   * ajax注册领取
   */
  function ajax_register_receive() {
    var user_location = longitude + ',' + latitude;
    var ajax_register_receive = "<?= $ajax_register_receive ?>";
    var registerMobile=$("#registerMobile").val();
    var codeNum=$("#codeNum").val();
    var password=$("#password").val();
    if(password.length<6||password.length>24){
      $(".mimatip").show();
      return;
    }
    $.ajax({
      url: ajax_register_receive,
      type: 'get',
      data: {
        mobile: registerMobile,
        password: password,
        code: codeNum,
        user_location: user_location,
        order_no: order_no
      },
      dataType: 'json',
      success: function (res) {
        if(res.status==90004){
          showToast("验证码有误，请重新输入！");
        }
        else if(res.status==90006){
          showToast("用户已存在！");
          $(".erificationCode").show();
          $(".erificationCodeAc").hide();

        }
        else if(res.status==90008){
          showToast("注册失败,请重新填写！");
        }
        else if(res.data.lottery_status==2){
          window.location.href="<?= $is_received ?>&mobile="+registerMobile;
        }
        else if(res.data.lottery_status==3){
          window.location.href="<?= $is_null ?>";
        }
        else if(res.data.lottery_status==4){
          window.location.href="<?= $receive_success ?>&mobile="+registerMobile;
        }
        else if(res.data.lottery_status==5){
            window.location.href="<?= $over_num ?>";
        }
      },
      fail: function (res) {
        //console.log(res);
      }
    });
  }


  function showToast(text) {
    $('.registerBox').append('<div class="jdialog-toast">' + text + '</div>');
    setTimeout(() => {
      $('.jdialog-toast').remove();
  }, 2000);
  }

  function showToast1(text) {
    $('.cantanine').append('<div class="jdialog-toast">' + text + '</div>');
    setTimeout(() => {
      $('.jdialog-toast').remove();
  }, 2000);
  }

  //页面初始化获取倒计时数字（避免在倒计时时用户刷新浏览器导致倒计时归零）
  var $getCodeInput = $(".erificationCode");
  var sessionCountdown = sessionStorage.getItem("countdown");
  if (!sessionCountdown) {
    $(".erificationCode").show();
    $(".erificationCodeAc").hide();
  } else {
    $(".erificationCode").hide();
    $(".erificationCodeAc").show();
    setCode($getCodeInput, sessionCountdown);
  }
  //获取验证码
  function setTime() {
    var remobile = $("#registerMobile").val();
    if (!remobile) {
      showToast("请输入手机号码")
      return;

    }
    if (remobile.length!==11) {
      showToast("手机号码格式不正确请重新输入")
      return;
    } else {
      ajax_get_code();
      setCode($getCodeInput, 60);

    }

  }
  //发送验证码倒计时
  function setCode($getCodeInput, countdown) {
    if (countdown == 0) {
      $(".erificationCode").show();
      $(".erificationCodeAc").hide();
      sessionStorage.removeItem("countdown");
      return;
    } else {
      $(".erificationCode").hide();
      $(".erificationCodeAc").show();
      $("#miaotext").text(countdown);
      countdown--;
    }
    sessionStorage.setItem("countdown", countdown);
    window.setTimeout(function () {
      setCode($getCodeInput, countdown);
    }, 1000);
  }




</script>