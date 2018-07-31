<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/7/5
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
    <script type="text/javascript" src="/static/js/vue.js"></script>
    <title>红包</title>
</head>
<body>
<div class="marketIndex giftIndex" id="gift">
    <div class="red" align="center">
        <div class="red-body">
            <div class="red-bottom-zindex">
                <div class="z-red-top-part">
                    <img src="/static/market/img/icon_gold.png" alt="">
                    <p>{{user}}</p>
                    <span>为你斩获一个大红包</span>
                    <div class="strong">
                        <p class="litter-strong">￥</p>
                        <p>{{gift_amount}}</p>
                    </div>
                </div>
                <div class="z-red-top"></div>
            </div>
            <div class="red-part" v-if="received==0">
                <div class="red-top-part">
                    <p>{{user}}</p>
                    <span>为您斩获一个大红包</span>
                    <p class="isOpen">恭喜发财，大吉大利</p>
                </div>
                <div class="red-top">
                </div>
                <div class="giftOpen" @click="openClick">開</div>
            </div>

            <div class="red-footer" v-if="received==1">
                <div>已存入收益，可直接提现</div>
            </div>
        </div>
        <div class="close-red">
            <img src="/static/market/img/btn_money_close.png" alt="" @click="redHide">
        </div>
    </div>

    <div class="item-header-font" align="center">
        <p>您邀请了好友，好友接单或者发单，产生真实交易完成后，您即可有机会获得一个随机大红包。</p>
        <i>
            <img src="/static/market/img/btn_news_close.png" alt="" @click="hideGiftHeader">
        </i>
    </div>
    <div class="none-data" v-if="!imgList">
        <img src="/static/market/img/img_nothing.png" alt="">
        <h3>您暂时没有获得大红包</h3>
        <p>多分享，多邀请，这样才有更多机会获得大红包</p>

    </div>

    <div class="red-page-data" v-else>
        <div class="items" v-for="(imgList,index) in imgList" align="center" >
            <div class="time-margin">
                <span class="item-time">
                    {{imgList.send_time}}
                </span>
            </div>
            <div class="item-img onepx" :id="imgList.gift_id" @click="redShow(index)" :name="imgList.invite_name" :class="{disabledClass:imgList.received=='1'||imgList.received=='2'}">
                <div class="item-top">
                    <img src="/static/market/img/close-red.png" alt="" v-if="imgList.received=='0'||imgList.received=='2'">
                    <img src="/static/market/img/open-red.png" alt="" v-if="imgList.received=='1'">
                    <div align="left" class="item-left">
                        <p class="item-big-font">{{imgList.invite_name}}</p>
                        <p>为您斩获一个大红包，大吉大利</p>
                    </div>
                </div>
                <div class="item-img-bottom">
                    <p>全民合伙人红包</p>
                    <p v-if="imgList.received=='0'">立即领取</p>
                    <p v-if="imgList.received=='1'">已领取</p>
                    <p v-if="imgList.received=='2'">已过期</p>
                </div>
            </div>
        </div>

    </div>
    <a class="red-recode" href="/market/gift-record">
        <img src="/static/market/img/red.png" alt="">
        <span>红包记录</span>
    </a>
</div>
</body>
<script>
    var data = <?=json_encode($data) ?>;
    var  user = "",userId = "",gift_amount=0,received = 0,index = 0;
    new Vue({
        el: "#gift",
        data: {
            imgList: data,
            user:user,
            gift_amount:gift_amount,
            received:received
        },
        methods: {
            redShow: function (idex) {
                var id = event.currentTarget.id;
                var name =$(event.currentTarget).attr("name");
                index = idex;
                if( this.imgList[index].received == 1)
                  return  window.location.href = "/market/gift-record"

                if( this.imgList[index].received == 2)
                    return this.showToast("红包已过期，如已领取，请在红包记录查看")
                this.checkIsOutDate(id);
                userId = id;
                this.user = name;
                this.received = 0
            },
            redHide: function () {
                $(".red").hide();
                $(".giftOpen").removeClass("giftOpenAnimation");
                $(".red-part").removeClass("red-part-top");
            },
            openClick: function () {
                $(".giftOpen").addClass("giftOpenAnimation");
                var self =this;
                setTimeout(function () {
                    self.openGift(userId,index);
                },1500);

            },
            hideGiftHeader: function () {
                $(".item-header-font").hide();
            },
            checkIsOutDate:function (id) {
                var self= this;
                $.ajax({
                    url:"/market/ajax-check-gift",
                    type: 'post',
                    data:{gift_id:id},
                    dataType: 'json',
                    success: function (res) {
                        if (res.code===0){
                            if (res.data.data.overdue==0)
                            $(".red").show();
                            if (res.data.data.overdue==1) {
                                self.imgList[index].received = 2;
                                self.showToast("红包已过期，如已领取，请在红包记录查看")
                            }
                        }
                    },
                    error: function (res) {
                    }
                })
            },
            openGift:function (id,index) {
                var self = this;
                $.ajax({
                    url:"/market/ajax-open-gift",
                    type: 'post',
                    data:{gift_id:id},
                    dataType: 'json',
                    success: function (res) {
                       if (res.code === 0) {
                           $(".giftOpen").removeClass("giftOpenAnimation");
                           $(".giftOpen").addClass("gift-change");
                           self.gift_amount = res.data.data.gift_amount;
                           self.imgList[index].received =  res.data.data.received;
                           self.received = res.data.data.received;
                           setTimeout(function () {
                               $(".giftOpen").removeClass("gift-change");
                               $(".red-part").addClass("red-part-top");
                           }, 1500);
                           userId = "";
                           this.user = "";
                           this.received = 0
                       }
                       if (res.code == 90015){
                           $(".giftOpen").removeClass("giftOpenAnimation");
                           $(".isOpen").text("已领取该红包，请到红包记录查看")
                       }else{
                            $(".giftOpen").removeClass("giftOpenAnimation");
                        }
                    },
                    error: function (res) {
                    }
                })
            },
            showToast:function (text) {
                //显示toast
                    $('.giftIndex').append('<div class="jdialog-toast">' + text + '</div>');
                    setTimeout(function () {
                        $('.jdialog-toast').remove();
                    },1500 );
            }
        }
    })
</script>
</html>

