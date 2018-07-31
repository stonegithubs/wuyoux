<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/6
 */

use \yii\helpers\Url;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我的收益</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/vue.js"></script>
</head>
<body>
<div class="marketIndex historyList" id="historyList" v-cloak>
    <div class="historyList-body ">
        <div class="header" id="profitTab">
            <ul class="profitTab">
                <li v-on:click="changeProfitTab('0')" id="profitTab0" class="font-white">今日收益 ( {{today_sum_profit}} )</li>
                <hr>
                <li v-on:click="changeProfitTab('1')" id="profitTab1">总收益( {{total_sum_profit}} )</li>
            </ul>
        </div>
        <div class="historyListBody historyList-data">
            <div class="historyTh marketTab has-radius" align="center">
                <ul>
                    <li>类型</li>
                    <li>昵称/账号</li>
                    <li>时间</li>
                    <li>收益</li>
                </ul>
            </div>
            <div class="profit-list" ref="viewBox">
                <div class="history-body marketTab today-profit">
                    <div id="profit_0">
                        <div v-if="!today_profit">
                            <img src="/static/market/img/icon_blank_page.png" alt="">
                            <p>暂无收益记录</p>
                        </div>
                        <div v-else >
                            <label class="historyTh history-list profit-list-ul" v-for="today in today_profit">
                                <ul>
                                    <li v-if="today.profit_from==0">利润分红</li>
                                    <li v-if="today.profit_from==1">红包奖励</li>
                                    <li class="li-width">
                                        <p>{{today.user_name}}<b class="font-b">({{today.role}})</b></p>
                                        <p>{{today.mobile}}</p>
                                    </li>

                                    <li>{{today.create_time}}</li>
                                    <li>{{today.amount}}</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                    <div id="profit_1">
                        <div v-if="!total_profit">
                            <img src="/static/market/img/icon_blank_page.png" alt="">
                            <p>暂无收益记录</p>
                        </div>
                        <div v-else  >
                            <label class="historyTh history-list profit-list-ul" v-for="total in total_profit">
                                <ul>
                                    <li v-if="total.profit_from==0">利润分红</li>
                                    <li v-if="total.profit_from==1">红包奖励</li>
                                    <li class="li-width">
                                        <p>{{total.user_name}}<b class="font-b">({{total.role}})</b></p>
                                        <p>{{total.mobile}}</p>
                                    </li>
                                    <li>{{total.create_time}}</li>
                                    <li>{{total.amount}}</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="marketFooter">
        <div class="tabButton">
            <a href="<?= Url::to(['market/invite']); ?>">
                <button>马上邀请</button>
            </a>
        </div>
    </div>
</div>
</body>
<script>
   var pageSize = 15,pageNumber = 1;
   var data =<?=json_encode($data) ?>;
   console.log("data",data)
   if (data.type=="total"){
       $("#profitTab0" ).removeClass("font-white");
       $("#profit_0").hide();
       $("#profitTab1" ).addClass("font-white");
       $("#profit_1").show();
   } 
   if (data.type=="today") {
       $("#profitTab1" ).removeClass("font-white");
       $("#profit_1").hide();
       $("#profitTab0" ).addClass("font-white");
       $("#profit_0").show();
   }
       
    new Vue({
        el: "#historyList",
        data: {today_profit:null,total_profit:null,today_sum_profit:0,total_sum_profit:0} ,
        //在created时请求接口拿到数据
        created:function(){
            var self= this;
            var url = <?=json_encode($data) ?>;
            if (url.type =="total"){
                $("#profitTab1" ).addClass("font-white");
                $("#profitTab0").removeClass("font-white");
                $("#profit_1").show();
                $("#profit_0").hide();
            }
            else if(url.type =="today"){
                $("#profitTab1" ).removeClass("font-white");
                $("#profitTab0").addClass("font-white");
                $("#profit_1").hide();
                $("#profit_0").show();
            }
            $.ajax({
                url:  "/market/ajax-profit",
                type: 'get',
                data: {
                    page:pageNumber,
                    page_size:pageSize
                },
                dataType: 'json',
                success: function (res) {
                  if ( res.code===0)
                    {
                        self.today_profit = res.data.data.today_profit;
                        self.total_profit = res.data.data.total_profit;
                        self.today_sum_profit =res.data.data.today_sum_profit;
                        self.total_sum_profit =res.data.data.total_sum_profit;
                    }
                },
                error: function (res) {
                    console.log("fail");
                }
            });
        },
        //滑动事件触发接口请求数据====分页
        mounted :function() {
            var self = this;
            self.box = self.$refs.viewBox;
            // 监听这个dom的scroll事件
            self.box.addEventListener('scroll',function() {
                if (self.scrollLoadData('.profit-list')>=0.90)
                    $.ajax({
                        url:  "/market/ajax-profit",
                        type: 'get',
                        data: {
                            page:pageNumber,
                            page_size:pageSize+1
                        },
                        dataType: 'json',
                        success: function (res) {
                        if (res.code===0){
                            pageNumber = pageNumber++;
                            pageSize = pageSize+1;
                            self.today_profit = res.data.data.today_profit;
                            self.total_profit = res.data.data.total_profit;
                        }
                        },
                        error: function (res) {
                        }
                    });
            }, false)
        },
        methods: {
            changeProfitTab: function (tab_num) {
                for (i = 0; i <= 1; i++) {
                    $("#profit_" + i).hide();
                    $("#profitTab" + i).removeClass("font-white");
                }
                pageSize = 15,pageNumber = 1;
                $('.profit-list').animate({scrollTop: '0px'}, 800);
                $("#profitTab" + tab_num).addClass("font-white");
                $("#profit_" + tab_num).show();
            },
            //获取到滚动高度
            scrollLoadData:function (valClass) {
                const $content = $(valClass);
                const viewH = $content.height();//可见高度
                const contentH = $content.get(0).scrollHeight;//内容高度
                const scrollTop = $content.scrollTop();//滚动高度
                return scrollTop / (contentH - viewH)  //到达底部90%时,加载新内容
            },
        }
    })
</script>
</html>