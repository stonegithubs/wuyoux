<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/27
 */

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/static/css/market.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <title>账号密码登陆</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <script type="text/javascript" src="//webapi.amap.com/maps?v=1.4.6&key=c4af304699451af317de903ac2a5b3f2"></script>
</head>
<body>
<div class="register">
    <div class="login-body-register">
        <div class="register-body" align="center">
            <div class="">
                <input type="tel" placeholder="请输入手机号码" maxlength="11" id="tel">
                <img class="yanjing " hidden src="/static/market/img/btn_clear_serch.png" alt="" onclick="clear()"
                     id="clear">
            </div>

            <div id="img_code" class="has-icon registerCode" hidden>
                <input class="graph_code" type="text" placeholder="请输入图形验证码" id="graph_code">

                <img class="graph_code_img" src="" alt="">
            </div>
            <div class="has-icon registerCode">
                <input type="text" placeholder="请输入验证码" id="msgCode" maxlength="6">
                <button id="getCode" onclick="getCode()">获取验证码</button>
            </div>
            <div class="has-icon " id="pasInput0" hidden>
                <input type="password" placeholder="设置6-16位字母、数字组合的密码" id="password" maxlength="16">
                <img class="yanjing" src="/static/market/img/login_icon_close.png" alt="" onclick="changeImg()"
                     id="passwordLock">
            </div>
        </div>
        <div class="register-button" align="center">
            <button id="certain" onclick="certain()" disabled class="changeBg">
                注册
            </button>
        </div>
    </div>
    <div class="register-footer registerBottom">注册即代表您已经同意<a
                href="protocol">《无忧帮帮声明》</a></div>
</div>

</body>

<script>
    var data =<?=json_encode($data) ?>;
    $('#tel').bind('input propertychange', function () {
        if ($(this).val()) {
            $("#clear").css("display", "inline-flex")
            $("#certain").attr("disabled", false);
            $("#certain").removeClass("changeBg");
        }
        else {
            $(".clear").hide();
            $("#certain").attr("disabled", true);
            $("#certain").addClass("changeBg");
        }
    });
    //切换图片验证码
    $(".graph_code_img").click(function () {
        ajax_get_auth_code();
    });
    //清空号码
    $("#clear").click(function () {
        $("#tel").val('');
        $("#clear").hide();
        $("#certain").attr("disabled", true);
        $("#certain").addClass("changeBg");
    });

    //获取验证码
    function getCode() {
        var phone = $("#tel").val();
        var graph = $("#graph_code").val();
        const phoneReg = /^1[3-8]\d{9}$/;
        if (!phone || !phoneReg.test(phone)) return showToast("请输入正确的手机号码");
        sendCode(phone, graph)
    }

    //注册
    function certain() {
        var geolocation, lng, lat;
        var phone = $("#tel").val();
        var msgCode = $("#msgCode").val();
        var password = $("#password").val();
        var mapObj = new AMap.Map('iCenter');
        const phoneReg = /^1[3-8]\d{9}$/;

        if (!phone || !phoneReg.test(phone)) return showToast("请输入正确的手机号码");
        if (!msgCode) return showToast("请输入验证码");
        if (!password) return showToast("请输入密码");
        //获取地理位置
        mapObj.plugin('AMap.Geolocation', function () {
            geolocation = new AMap.Geolocation({});
            geolocation.getCurrentPosition();
            AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息

        });

        function onComplete(data) {
            lng = data.position.getLng();
            lat = data.position.getLat();
        }

        //注册
        $.ajax({
            url: "/site/ajax-sign-up",
            type: 'post',
            data: {
                mobile: phone,
                code: msgCode,
                password,
                user_location: [lng, lat]
            },
            dataType: 'json',
            success: function (res) {
                if (res.code === 0) {
                    window.location.href = data.market_url;
                }
                else
                    return showToast(res.message)
            },
            error: function (res) {
                return showToast("操作失败")
            }
        });
    }

    //发送验证码
    function sendCode(phone, graph) {
        $.ajax({
            url: "/site/ajax-send-code",
            type: 'post',
            data: {
                mobile: phone,
                type: 1,
                graph_code: graph
            },
            dataType: 'json',
            success: function (res) {
                if (res.data.status === 1) {
                    countDown($("#getCode"), 60);
                    $("#pasInput0").show();
                }
                if (res.data.status === 3 || res.data.status === 4) {
                    $("#img_code").css("display", "block");
                    ajax_get_auth_code();
                }
                else
                    return showToast(res.message)
            },
            error: function (res) {
                return showToast("获取验证码失败")
            }
        });
    }

    //获取图形验证码
    function ajax_get_auth_code() {
        var ajax_get_code = "/site/ajax-get-auth-code";
        var phone = $("#tel").val();
        console.log("tel", phone)
        $.ajax({
            url: ajax_get_code,
            type: 'post',
            data: {
                mobile: phone,
                type: 1
            },
            dataType: 'json',
            success: function (res) {
                if (res.code === 0) {
                    $(".graph_code_img").attr("src", res.data);
                } else
                    return showToast(res.message);
            },
            fail: function (res) {
                showToast(res.message);
            }
        });
    }


    //显示toast
    function showToast(text) {
        $('.register').append('<div class="jdialog-toast">' + text + '</div>');
        setTimeout(function () {
            $('.jdialog-toast').remove();
        }, 1500);
    }

    //显示密码或者不显示
    function changeImg() {
        const lockImg = $("#passwordLock");
        const password = $("#password");
        if (lockImg.attr("src") === "/static/market/img/login_icon_close.png") {
            password.prop('type', 'text');
            lockImg.attr("src", "/static/market/img/login_icon_open.png")
        }
        else {
            password.prop('type', 'password');
            lockImg.attr("src", "/static/market/img/login_icon_close.png")
        }
    }

    //60s倒数
    function countDown(element, counter) {
        var c = counter || 60;
        setTime(element);

        function setTime(ele) {
            if (c == 0) {
                ele.attr("disabled", false);
                ele.text('获取验证码');
                ele.css("background", "#ff6000");
                ele.css("border", "none");
                ele.css("color", "#fff");
            } else {
                ele.attr("disabled", true);
                ele.text(c + 's后重新获取');
                ele.css("background", "transparent");
                ele.css("color", "#000");
                ele.css("border", "1px solid #000");
                --c;
                setTimeout(function () {
                    setTime(ele);
                }, 1000);
            }
        }
    }

</script>
</html>

