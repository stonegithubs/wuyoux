<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/7
 */

use yii\helpers\Url;

?>
<!DOCTYPE html>
<html>

<head>
    <title>注册</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <link rel="stylesheet" href="/static/register/css/index.css">
    <style>
        input[type="checkbox"] {
            -webkit-appearance: none; /*清除复选框默认样式*/
            background: url(/static/register/img/btn_checkbox.png); /*复选框的背景图*/
            background-size: 100% 100%;
            height: 15px; /*高度*/
            vertical-align: middle;
            width: 15px;
        }

        input[type="checkbox"]:checked {
            background-position: -48px 0;
            -webkit-appearance: none; /*清除复选框默认样式*/
            background: url(/static/register/img/btn_checkbox_check.png); /*复选框的背景图*/
            background-size: 100% 100%;
            height: 15px; /*高度*/
            vertical-align: middle;
            width: 15px;
        }
    </style>

</head>
<body>
<div class="main_box">
    <img class="img_banner" src="/static/register/img/img_banner.png" alt="">
    <div class="top_tab">
       <div class="btn_register_on"></div>
       <div style="display:none" class="btn_register_user_off"></div>
       <div class="btn_register_bang_off"></div>
       <div style="display:none" class="btn_register_bang_on"></div>
        <!-- <img class="btn_register_on" src="/static/register/img/btn_register_on.png" alt="">
        <img class="btn_register_user_off" src="/static/register/img/btn_register_user_off.png" alt=""
             style="display:none">
        <img class="btn_register_bang_off" src="/static/register/img/btn_register_bang_off.png" alt="">
        <img class="btn_register_bang_on" src="/static/register/img/btn_register_bang_on.png" alt=""
             style="display:none"> -->
    </div>
    <div class="regin_box">
        <!-- <div class="tip_box">
            <img src="/static/register/img/icon_portrait.png" alt="">
            <div class="text_box" id="user-tip" style="display:none">
                <div><span class="tip_text"></span>推荐您成为无忧帮帮合伙人</div>
                <div class="invite_code">
                    <div class="invite_mobile"></div>
                    <img class="right_icon" src="/static/register/img/icon_mark.png" alt="" style="width:10px;height:10px">
                </div>
            </div>
            <div class="text_box" id="bang-tip" style="display:none">
                <div><span class="tip_text"></span>推荐您成为无忧帮帮合伙人</div>
                <div class="invite_code">
                    <div class="invite_mobile"></div>
                    <img class="right_icon" src="/static/register/img/icon_mark.png" alt="" style="width:10px;height:10px">
                    </div>
            </div>
        </div> -->
        <!-- <img class="box_shadow_img" src="/static/register/img/img_shadow.png" alt="" > -->
        <div class="input_box">
            <div><input id="registerMobile" type="number" oninput="if(value.length>11)value=value.slice(0,11)"
                        placeholder="请输入手机号码"></div>
            <div id="img_code" class="has-icon" hidden>
                <input class="graph_code" type="text" placeholder="请输入图形验证码">
                <img class="graph_code_img" src="" alt="">
            </div>
            <div style="display: flex;justify-content: center;align-items: center;">
                <input oninput="if(value.length>6)value=value.slice(0,6)" id="mes_code" type="text" placeholder="请输入短信验证码"
                       style="width:60%;display: flex;justify-content: center;align-items: center;">
                <img class="btn_get_code" style="width:40%" src="/static/register/img/btn_get_code.png" alt="">
                <div class="btn_get_code_off" style="width:40%;height: 30px;border-radius: 100px;display:none"><span
                            id="miaotext"></span>S
                </div>
            </div>
            <div class="password_box"><input id="password" type="password"
                                             oninput="if(value.length>16)value=value.slice(6,24)"
                                             placeholder="设置6-24位字母、数字组合"></div>
             <div style="display:none;" class="invite_mobile_box"><input id="invite_mobile" type="number" oninput="if(value.length>11)value=value.slice(0,11)"
                        placeholder="推荐码（选填）"></div>
        </div>
        <div class="check_agree">
            <input class="checkbox" type="checkbox" checked="checked"/>
            <div>我已阅读并同意<a href="#">《无忧帮帮申明》</a></div>
        </div>
        <div id="user_regin_box" onclick="ajax_register()">
        <div style="display:none" class="btn_join_on"></div>
        <div class="btn_join_off"></div>
        </div>
        <div id="xiaobang_regin_box" onclick="ajax_bang_register()" style="display:none">
        <div style="display:none" class="btn_join_on"></div>
        <div class="btn_join_off"></div>
        </div>
    </div>
</div>
<img class="footer_img" src="/static/register/img/img_banner2.png" alt="">
</body>
<script type="text/javascript" src="//webapi.amap.com/maps?v=1.4.6&key=c4af304699451af317de903ac2a5b3f2"></script>
<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script type="text/javascript">
       $(".btn_register_bang_off").click();
        sessionStorage.setItem("bang", "bang");
    $(".btn_register_bang_off").click(function(){
        $(".btn_register_user_off").show();
        $(".btn_register_on").hide();
        $(".btn_register_bang_on").show();
        $(".btn_register_bang_off").hide();
        $("#xiaobang_regin_box").show();
        $("#user_regin_box").hide();
        // $("#bang-tip").show();
        // $("#user-tip").hide();
        sessionStorage.removeItem("user");
        sessionStorage.setItem("bang", "bang");
    })
    $(".btn_register_user_off").click(function(){
        $(".btn_register_user_off").hide();
        $(".btn_register_on").show();
        $(".btn_register_bang_on").hide();
        $(".btn_register_bang_off").show();
        $("#xiaobang_regin_box").hide();
        $("#user_regin_box").show();
        // $("#bang-tip").hide();
        // $("#user-tip").show();
        sessionStorage.removeItem("bang");
        sessionStorage.setItem("user", "user");
    })
 
    // if(data.entrance==="1") {
    //     sessionStorage.setItem("user", "user");
    // }
    // if(data.entrance==="2") {
    //     $(".btn_register_bang_off").click();
    //     sessionStorage.setItem("bang", "bang");
    // }
    // $(".tip_text").text(data.invite_name);
    // $(".invite_mobile").text("邀请码 "+data.invite_mobile);
   
    mapObj = new AMap.Map('iCenter');
    mapObj.plugin('AMap.Geolocation', function () {
        geolocation = new AMap.Geolocation({});
        geolocation.getCurrentPosition();
        AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息

    });

    function onComplete(data) {
        console.log(data.position.getLng(),data.position.getLat());
        sessionStorage.setItem("longitude",data.position.getLng());
        sessionStorage.setItem("latitude",data.position.getLat());
    }

    $("#registerMobile").keypress(function(){
        $(".btn_join_on").show();
        $(".btn_join_off").hide();
    })

    $(".graph_code_img").click(function(){
        ajax_get_auth_code();
    })

    $(".btn_get_code").click(function(){
        var bang = sessionStorage.getItem("bang");
        var user = sessionStorage.getItem("user");
        if (user=="user") {
            setTime();
        }
        if (bang=="bang") {
            setTime1();
        }
    })


    function showToast(text) {
        $('.main_box').append('<div class="jdialog-toast">' + text + '</div>');
        setTimeout(function () {
            $('.jdialog-toast').remove();
        },2000);
    }


    //页面初始化获取倒计时数字（避免在倒计时时用户刷新浏览器导致倒计时归零）
    var $getCodeInput = $(".btn_get_code");
    var sessionCountdown = sessionStorage.getItem("countdown");
    if (!sessionCountdown) {
        $(".btn_get_code").show();
        $(".btn_get_code_off").hide();
    } else {
        $(".btn_get_code").hide();
        $(".btn_get_code_off").show();
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
            $(".btn_get_code").show();
            $(".btn_get_code_off").hide();
            sessionStorage.removeItem("countdown");
            return;
        } else {
            $(".btn_get_code").hide();
            $(".btn_get_code_off").show();
            $("#miaotext").text(countdown);
            countdown--;
        }
        sessionStorage.setItem("countdown", countdown);
        window.setTimeout(function () {
            setCode($getCodeInput, countdown);
        }, 1000);
    }

    function ajax_get_code() {
        var ajax_get_code = "/market-share/ajax-get-user-code";
        var registerMobile=$("#registerMobile").val();
        var graph_code=$(".graph_code").val();
        $.ajax({
            url: ajax_get_code,
            type: 'get',
            data: {
                mobile: registerMobile,
                graph_code:graph_code
            },
            dataType: 'json',
            success: function (res) {
                showToast(res.message);
                if(res.code!==90006){
                    $(".password_box").show();
                    $(".invite_mobile_box").show();
                }
                if(res.code===90011){
                    $("#img_code").css("display","flex");
                    $(".graph_code_img").attr("src",res.data);
                }
            },
            fail: function (res) {
                showToast(res.message);
            }
        });
    }

    function setTime1() {
        var remobile = $("#registerMobile").val();
        if (!remobile) {
            showToast("请输入手机号码")
            return;

        }
        if (remobile.length!==11) {
            showToast("手机号码格式不正确请重新输入")
            return;
        } else {
            ajax_get_code1();
            setCode($getCodeInput, 60);

        }

    }

    function ajax_get_code1() {
        var ajax_get_code = "/market-share/ajax-get-provider-code";
        var registerMobile=$("#registerMobile").val();
        var graph_code=$(".graph_code").val();
        $.ajax({
            url: ajax_get_code,
            type: 'get',
            data: {
                mobile: registerMobile,
                graph_code:graph_code
            },
            dataType: 'json',
            success: function (res) {
                console.log("res",res);
                if(res.data.is_user===0){
                    $(".password_box").show();
                    $(".invite_mobile_box").show();
                }
                if(res.code===90011||res.code===90012){
                    $("#img_code").css("display","flex");
                    $(".graph_code_img").attr("src",res.data);
                }
                else
                    showToast(res.message);
            },
            fail: function (res) {
                showToast(res.message);
            }
        });
    }

    //获取图形验证码

    function ajax_get_auth_code() {
        var ajax_get_code = "/market-share/ajax-get-auth-code";
        var registerMobile=$("#registerMobile").val();
        $.ajax({
            url: ajax_get_code,
            type: 'get',
            data: {
                mobile: registerMobile,
            },
            dataType: 'json',
            success: function (res) {
                if(res.code===0){
                    $(".graph_code_img").attr("src",res.data);
                }
            },
            fail: function (res) {
                showToast(res.message);
            }
        });
    }

    //注册

    function ajax_register() {
        var checkbox = $(".checkbox").is(":checked");
        var ajax_register ="/market-share/ajax-user-open-register";
        var code = $("#mes_code").val();
        var longitude = sessionStorage.getItem("longitude");
        var latitude = sessionStorage.getItem("latitude")
        var remobile = $("#registerMobile").val();
        var password = $("#password").val();
        var invite_mobile = $("#invite_mobile").val();
        if (!checkbox) {
            showToast("请阅读《无忧帮帮服务协议》")
            return;
        }
        if (remobile.length!==11) {
            showToast("请输入正确的手机号码")
            return;
        }
        if (code == "") {
            showToast("请输入验证码")
            return;
        }
        // if (password == "") {
        //     showToast("请输入密码")
        //     return;
        // }
        // if (password.length < 6 || password.length > 24) {
        //     showToast("密码由6-24位字母或数字组合");
        //     return;
        // }
        $.ajax({
            url: ajax_register,
            type: 'get',
            data: {
                mobile: remobile,
                password:password,
                code:code,
                longitude:longitude,
                latitude:latitude,
                invite_mobile:invite_mobile
            },
            dataType: 'json',
            success: function (res) {
                showToast(res.message);
                if(res.code===0) {
                window.location.href="<?= Url::to(['market-share/open-share']); ?>"+"?entrance=1";
                }
                
            },
            fail: function (res) {
                //console.log(res);
            }
        });
    }

    function ajax_bang_register() {
        var checkbox = $(".checkbox").is(":checked");
        var ajax_register ="/market-share/ajax-provider-open-register";
        var code = $("#mes_code").val();
        var longitude = sessionStorage.getItem("longitude");
        var latitude = sessionStorage.getItem("latitude")
        var remobile = $("#registerMobile").val();
        var password = $("#password").val();
        var invite_mobile = $("#invite_mobile").val();
        if (!checkbox) {
            showToast("请阅读《无忧帮帮服务协议》")
            return;
        }
        if (remobile.length!==11) {
            showToast("请输入正确的手机号码")
            return;
        }
        if (code == "") {
            showToast("请输入验证码")
            return;
        }
        $.ajax({
            url: ajax_register,
            type: 'get',
            data: {
                mobile: remobile,
                password:password,
                code:code,
                longitude:longitude,
                latitude:latitude,
                invite_mobile:invite_mobile
            },
            dataType: 'json',
            success: function (res) {
                showToast(res.message);
                if(res.code===0) {
                window.location.href="<?= Url::to(['market-share/open-share']); ?>"+"?entrance=2";
                }
            },
            fail: function (res) {
                //console.log(res);
            }
        });
    }


</script>
</html>


