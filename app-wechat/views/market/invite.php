<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/31
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/market/js/jquery.qrcode.min.js"></script>
    <title>全民合伙人</title>
</head>
<body>
<div class="marketIndex" id="invite">
    <div class="shareHeader">
        <div id="scan"></div>
    </div>
    <div class="share">
        <div class="line">
            <b></b>
            <p>其他邀请方式</p>
            <b></b>
        </div>
        <div class="shareFunction">
            <div class="share-header-width" onclick="shareBg()">
                <img src="/static/market/img/icon_wechat.png" alt="">
                <p>微信好友</p>
            </div>
            <div class="share-header-width" onclick="shareBg()">
                <img src="/static/market/img/icon_moment.png" alt="">
                <p>微信朋友圈</p>
            </div>
            <div class="share-header-width" onclick="shareBg()">
                <img src="/static/market/img/icon_qq.png" alt="">
                <p>QQ好友</p>
            </div>
        </div>
    </div>
</div>

<div class="inputPwd active visible hide" onclick="displayNone()">
        <img src="/static/market/img/share.png" alt="" style="width: 75%; right: .5rem;position: absolute;pointer-events: none">
</div>
</body>
<script>
    $(function () {
        $('#scan').qrcode("<?= $data['qrcode_link']?>");
    });
    function shareBg() {
        $(".inputPwd").show()
    }
    function displayNone() {
        $(".inputPwd").hide()
    }
</script>
</html>

<!--微信分享sdk-->
<script src="//res.wx.qq.com/open/js/jweixin-1.2.0.js?r=woeo" type="text/javascript" charset="utf-8"></script>
<script>
    wx.config(<?= $data['jssdk'] ?>);

    wx.ready(function () {

        var share_link = "<?= $data['link'] ?>";
        var share_pic = "<?= $data['imgUrl'] ?>";
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