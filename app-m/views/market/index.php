<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/31
 */

use \yii\helpers\Url;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>全民合伙人</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <link rel="stylesheet" href="/static/market/css/notice.css">
    <script type="text/javascript" src="/static/js/vue.js"></script>
    <script type="text/javascript" src="/static/market/js/notice.js"></script>
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
</head>
<body>
<div class="marketIndex market" id="market" v-cloak>
    <div class="market-part">
        <div class="scrollHeader notice">
            <i><img src="/static/market/img/icon_news.png" alt=""></i>
            <ul>
                <li> {{notice}}</li>
                <li> {{notice}}</li>
            </ul>
        </div>
        <a href="<?= Url::to(['market/common-problem']); ?>" class="question">
            <img src="/static/market/img/question.png" alt="">
        </a>
        <div class="marketHeader">
            <label class="marker-header-left">
                <img src="/static/market/img/img_title.png" alt="">
                <p>我的推荐码：{{mobile}}</p>
            </label>
        </div>
        <div class="marketBody">
            <div class="marketCenter">
                <div class="itemList">
                    <div class="itemList-width">
                        <p>可提现收益(元)</p>
                        <p class="font-normal">{{current_profit}}</p>
                    </div>
                    <div class="itemList-button">
                        <a href="<?= Url::to(['market/withdraw']); ?>">
                            <button>提现</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="marketData">
                <a href="<?= Url::to(['market/profit?type=today']); ?>" class="marketBox">
                    <p> 今日收益(元)</p>
                    <p class="font-color">{{today_profit}}</p>
                </a>
                <!--                <div class="hr"></div>-->
                <hr>
                <a href="<?= Url::to(['market/profit?type=total']); ?>" class="marketBox">
                    <p>总收益(元)</p>
                    <p class="font-color">{{total_profit}}</p>
                </a>
            </div>


        </div>
        <div class="marketTab marketTabIndex">
            <div id="tab" class="tab strongFont">
                <ul>
                    <li @click="changeTab('0')" id="changeTab0" class="liBorder">活动规则</li>
                    <li @click="changeTab('1')" id="changeTab1">推荐用户 ({{user_count}})</li>
                    <li @click="changeTab('2')" id="changeTab2">推荐小帮 ({{provider_count}})
                    </li>
                </ul>
            </div>

            <div class="tab-body">
                <div id="tabCon_0" class="tabCon">
                    <div class="market-content">
                        <div class="market-item">
                            <!--                            <img src="/static/market/img/img_1st.png" alt="" class="first-img">-->
                            <div class="item-child">
                                <h4><img src="/static/market/img/img_title_1.png" alt=""></h4>

                                <p>
                                    无忧帮帮“合伙人”计划，皆在致力于打造一个全民参与，互助共赢的推广活动。鼓励所有社会人员成为无忧帮帮的合伙人，通过推荐新用户注册并成为有效用户，即可以通过订单抽佣分成的方式，获得相应报酬。  </p>
                            </div>
                            <!--                            <img src="/static/market/img/icon_award_1st.png" alt="" class="layout-img">-->
                        </div>
                        <div class="market-item">
                            <!--                            <img src="/static/market/img/img_2nd.png" alt="" class="first-img">-->
                            <div class="item-child">
                                <h4><img src="/static/market/img/img_title_2.png" alt=""></h4>
                                <div class="item">
                                    <p>1、</p>
                                    <p>您可以通过以下三种方式推荐好友注册，完成与您的账户绑定关联后，才可获得返利奖励。<br>
                                        （1）在社交平台分享您的专属链接页面，好友通过进入您的专属链接注册。<br>
                                        （2）好友在海报、传单上扫描您的专属二维码注册，或者好友通过扫描app内您的专属二维码进行注册。<br>
                                        （3）好友下载无忧帮帮app后，在注册的时候输入您的邀请码。<br></p>
                                </div>

                                <div class="item">
                                    <p>2、</p>
                                    <p>使用同设备，同号码，视为同一个用户。</p>
                                </div>
                                <div class="item">
                                    <p>3、</p>
                                    <p>已经有邀请注册记录的好友，将不作为您的有效邀请，重复邀请无效。</p>
                                </div>
                            </div>
                        </div>
                        <div class="market-item">
                            <div class="item-child">
                                <h4><img src="/static/market/img/img_title_3.png" alt=""></h4>
                                <div class="item">
                                    <p>1、</p>
                                    <p>好友每次下单或者接单，产生了真实交易订单，并完成该订单，您即可获取该好友所有关联订单利润的10%奖励，奖励无上限。</p>
                                </div>

                                <div class="item">
                                    <p>2、</p>
                                    <p>好友在完成邀请注册后的30天内，若没有发单或接单等市场行为，好友将会自动与您解除绑定关联，其推荐抽佣分成的奖励不再发放。</p>
                                </div>
                                <div class="item">
                                    <p>3、</p>
                                    <p>
                                        如发现采用特殊技术手段或邀请的好友身份异常，违规套取奖励行为，一经核实，将视情节严重程度进行判罚：不予发放奖励、封停账号、冻结所有通过推荐奖励渠道获得的奖励、依法追究其法律责任。</p>
                                </div>
                            </div>
                            <!--                            <img src="/static/market/img/icon_award.png" alt="" class="layout-img">-->
                        </div>

                        <div class="market-item">
                            <!--                            <img src="/static/market/img/img_3rd.png" alt="" class="first-img">-->
                            <div class="item-child">
                                <h4><img src="/static/market/img/img_title_4.png" alt=""></h4>
                                <div class="item">
                                    <p>1、</p>
                                    <p>本活动最终解释权归无忧帮帮公司所有。</p>
                                </div>
                                <div class="item">
                                    <p>2、</p>
                                    <p>如有疑问，敬请咨询无忧帮帮官方客服热线400-135-5188。</p>
                                </div>
                            </div>
                            <!--                            <img src="/static/market/img/icon_award.png" alt="" class="layout-img">-->
                        </div>
                    </div>
                </div>
                <div id="tabCon_1">
                    <div class="historyListBody history-index">
                        <div class="historyTh " align="center">
                            <ul>
                                <li>昵称</li>
                                <li>账号</li>
                                <!--                                <li>时间</li>-->
                                <li>收益</li>
                            </ul>
                        </div>
                        <div class="history-body">
                            <label class="historyTh history-list" v-for="list in user_relation">
                                <ul>
                                    <li>{{list.user_name}}<b class="font-b">({{list.role}})</b></li>
                                    <li>{{list.mobile}}</li>
                                    <!--                                    <li>{{list.create_time}}</li>-->
                                    <li>{{list.amount}}</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="tabCon_2">

                    <div class="historyListBody history-index">
                        <div class="historyTh" align="center">
                            <ul>
                                <li>昵称</li>
                                <li>账号</li>
                                <!--                                <li>时间</li>-->
                                <li>收益</li>
                            </ul>
                        </div>
                        <div class="history-body">
                            <label class="historyTh history-list" v-for="list in provider_relation">
                                <ul>
                                    <li>{{list.user_name}}<b class="font-b">({{list.role}})</b></li>
                                    <li>{{list.mobile}}</li>
                                    <!--                                    <li>{{list.create_time}}</li>-->
                                    <li>{{list.amount}}</li>
                                </ul>
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <a href="/market/gift" class="redPackage">
            <img src="/static/market/img/btn_luckymoney.png" alt="">
        </a>
        <div class="marketFooter">
            <div class="tabButton">
                <a href="<?= Url::to(['market/invite']); ?>">
                    <button>马上邀请</button>
                </a>
            </div>
        </div>

    </div>


    <div class="inputPwd active visible hide">
        <div class="plateNumber-body" align="center">
            <div class="plate-center" align="center">
                <div class="row fullWidth" align="center">
                    <p>此功能暂未在您的城市上线</p>
                </div>
            </div>
        </div>
    </div>

    <div class="inputPwd active visible hide">
        <div class="plateNumber-body" align="center">
            <div class="plate-center" align="center">
                <div class="row fullWidth" align="center">
                    <p>此功能暂未开放</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    var data =<?=json_encode($data) ?>;
    // if(data.status==0){
    //     $(".inputPwd").show();
    // }else $(".inputPwd").hide();
    data = $.extend(data,{notice:""});
    new Vue({
        el: "#market",
        data: data,
        created:function(){
          this.getNotice();
        },
        mounted: function () {
            var self =this;
            setInterval(function () {
                noticeUp('.notice ul','-35px',1000);
                self.getNotice()
            }, 3000);
            self.is_gift == 0 ? $(".redPackage").removeClass("shake-slow") : $(".redPackage").addClass("shake-slow");
        },
        methods: {
            changeTab: function (tab_num) {
                for (i = 0; i <= 2; i++) {
                    $("#tabCon_" + i).hide();
                    $("#changeTab" + i).removeClass("liBorder")
                }
                $("#tabCon_" + tab_num).show();
                $("#changeTab" + tab_num).addClass("liBorder")
            },
            getNotice:function () {
                var self = this;
                    $.ajax({
                        url:  "/market/ajax-top",
                        type: 'get',
                        data: {
                        },
                        dataType: 'json',
                        success: function (res) {
                            if (res.code===0){
                                self.notice = res.data.data.top
                            }
                        },
                        error: function (res) {
                        }
                    });
            }
        }
    });
</script>
</html>