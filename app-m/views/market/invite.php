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
<div class="marketIndex invite" id="invite">
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
            <div class="share-header-width" onclick="wechatShare()">
                <img src="/static/market/img/icon_wechat.png" alt="">
                <p>微信好友</p>
            </div>
            <div class="share-header-width" onclick="wechatCircle()">
                <img src="/static/market/img/icon_moment.png" alt="">
                <p>微信朋友圈</p>
            </div>
            <div class="share-header-width" onclick="tenCentClick()">
                <img src="/static/market/img/icon_qq.png" alt="">
                <p>QQ好友</p>
            </div>
        </div>
    </div>
    <!--<button class="share-footer">-->
    <!--    取消-->
    <!--</button>-->
</div>
</body>
<script>

    var data = <?= json_encode($data) ?>;
    console.log("data",data);
    var jsonData = JSON.stringify(data);

    $(function () {
        $('#scan').qrcode(data.qrcode_link);
    });

    function wechatShare() {
        weChatFriend.shareWeChatFriend(jsonData);
    }

    function wechatCircle() {
        moments.shareMoments(jsonData);
    }

    function tenCentClick() {
        tencentQQ.shareTencentQQ(jsonData)
    }
    // $(document).ready(function(){
    //     $('#scan').qrcode(data.qrcode_link);
    // });


</script>
</html>