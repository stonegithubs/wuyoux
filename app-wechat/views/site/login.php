<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/27
 */

use yii\helpers\Url;

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/static/css/market.css">
    <!--    <link rel="stylesheet" href="/static/market/css/web-share.css">-->
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <title>账号密码登陆</title>
    <meta charset=utf-8>
    <meta name=viewport
          content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>

</head>
<body>
<div class="register" id="login">
    <div class="login-body">
        <div class="header">
            <ul>
                <li class="active" onclick="changeTab('0')" id="changeTab0">
                    账号密码登录
                    <div class="border" id="border0"></div>
                </li>
                <li onclick="changeTab('1')" id="changeTab1">
                    手机快速登录
                    <div class="border" hidden id="border1"></div>
                </li>
            </ul>
        </div>
        <div class="register-body" align="center">
            <div class="">
                <input type="tel" placeholder="请输入手机号码" maxlength="11" id="tel">
                <img class="yanjing clear" src="/static/market/img/btn_clear_serch.png" alt="" id="clear">
            </div>

            <div class="has-icon" id="pasInput0">
                <input type="password" placeholder="请输入密码" id="password" maxlength="30">
                <img class="yanjing" src="/static/market/img/login_icon_close.png" alt="" onclick="changeImg()"
                     id="passwordLock">
            </div>


            <div id="img_code" class="has-icon registerCode" hidden>
                <input class="graph_code" type="text" placeholder="请输入图形验证码" id="graph_code">
                <img class="graph_code_img" src="" alt="">
            </div>

            <div class="has-icon has-button" id="pasInput1">
                <input type="text" placeholder="请输入验证码" id="msgCode" maxlength="6">
                <button id="getCode" onclick="getCode()">获取验证码</button>
            </div>
        </div>
        <div class="register-button" align="center">
            <button onclick="login()" disabled id="buttonLogin" class="changeBg">
                登录
            </button>
            <a href="register" class="registerUrl">
                <p>注册新账号</p>
                <p><img src="/static/market/img/icon_arrow_right.png" alt=""></p>
            </a>
        </div>
    </div>
    <div class="register-footer">登录即代表您已经同意<a
                href="protocol">《无忧帮帮声明》</a></div>
</div>
</body>

<script>
    var flag = 0;
    var dataUrl =<?=json_encode($data) ?>;


    $('#tel').bind('input propertychange', function () {
        if ($(this).val()) {
            $("#clear").css("display", "inline-flex");
            $("#buttonLogin").attr("disabled", false);
            $("#buttonLogin").removeClass("changeBg");
        }
        else {
            $(".clear").hide();
            $("#buttonLogin").attr("disabled", true);
            $("#buttonLogin").addClass("changeBg");
        }
    });
    //点击切换图片验证码
    $(".graph_code_img").click(function () {
        ajax_get_auth_code();
    });

    //清楚手机号码
    $("#clear").click(function () {
        $("#tel").val('');
        $("#clear").hide();
        $("#buttonLogin").attr("disabled", true);
        $("#buttonLogin").addClass("changeBg");
    });

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

    //头部tab
    function changeTab(tab_num) {
        for (i = 0; i <= 1; i++) {
            $("#pasInput" + i).hide();
            $("#changeTab" + i).removeClass("active");
            $("#border" + i).hide();
        }
        $("#pasInput" + tab_num).show();
        $("#border" + tab_num).show();
        flag = tab_num;
        $("#tel").val("");
        $("#clear").hide();
        $("#changeTab" + tab_num).addClass("active")
        if (tab_num == '0') {
            $("title").text("账号密码登录"),
                $("#img_code").hide()
        } else
            $("title").text("手机快速登录")
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

    //发送验证码
    function getCode() {
        var phone = $("#tel").val();
        var graph = $("#graph_code").val();
        const phoneReg = /^1[3-8]\d{9}$/;
        if (!phone || !phoneReg.test(phone)) return showToast("请输入正确的手机号码");
        sendCode(phone, graph);
    }

    //登陆
    function login() {
        var data;
        const phoneReg = /^1[3-8]\d{9}$/;
        var phone = $("#tel").val();
        var password = $("#password").val();
        var msgCode = $("#msgCode").val();
        if (!phone || !phoneReg.test(phone)) return showToast("请输入正确的手机号码");
        if (flag == 0) {
            if (!password) return showToast("请输入密码");
            data = {
                mobile: phone,
                password,
                type: flag
            }
        }
        else {
            if (!msgCode) return showToast("请输入验证码");
            data = {
                mobile: phone,
                code: msgCode,
                type: flag
            }
        }
        ajaxLogin(data);
    }

    //显示toast 提示
    function showToast(text) {
        $('.register').append('<div class="jdialog-toast">' + text + '</div>');
        setTimeout(function () {
            $('.jdialog-toast').remove();
        }, 1500);
    }

    //api 登陆
    function ajaxLogin(data) {
        $.ajax({
            url: "/site/ajax-login",
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (res) {
                if (res.code === 0) {
                    window.location.href = dataUrl.market_url;
                }
                else
                    return showToast(res.message)
            },
            error: function (res) {
                return showToast("登录失败")
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
                type: 4,
                graph_code: graph
            },
            dataType: 'json',
            success: function (res) {
                if (res.data.status === 1) {
                    countDown($("#getCode"), 60);
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
        console.log("phone", phone);
        $.ajax({
            url: ajax_get_code,
            type: 'post',
            data: {
                mobile: phone,
                type: 4
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
</script>
</html>

