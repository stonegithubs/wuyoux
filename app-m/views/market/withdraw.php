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
    <title>提现</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/vue.js"></script>
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
</head>
<body>
<div class="marketIndex withdrawal" id="withdrawal" v-cloak>
    <div class="withdrawal-body">
        <div class="header" id="profitTab">
            <a href="<?= Url::to(['market/withdraw-history']); ?>" class="question transferList" hidden> 提现记录</a>
            <div class="header-padding">
                <p class="turnContent">未转账金额</p>
                <p :class="{'has-withdraw':entrance=='provider'?true:false}">{{current_profit}}</p>
                <ul v-if="entrance=='user'||entrance=='wechat'">
                    <li v-on:click="changeWithdrawTab('0')" id="changeFontColor0" class="changeFontColor">转账到余额
                        <div class="border" id="changeWithdraw_0"></div>
                    </li>
                    <hr>
                    <li v-on:click="changeWithdrawTab('1')" id="changeFontColor1">提现到银行卡
                        <div class="border" id="changeWithdraw_1"></div>
                    </li>
                </ul>
            </div>

        </div>
        <div class="transfer-div">
            <div class="transferInput">
                <span>￥</span>
                <input type="number" id="number" v-model="input">
            </div>
            <div class="transferTip">
                <p v-if="entrance=='user'||entrance=='wechat'" id="userTransfer">转账余额不得低于30元</p>
                <p v-if="entrance=='provider'">转账余额不得低于30元</p>
                <p v-on:click="dataAll(current_profit)">全部提现</p>
            </div>
        </div>
        <div class="withdrawal_display" id="withdraw_1" v-if="entrance=='user'||'wehcat'">
            <a href="<?= Url::to(['market/bind-transfer']); ?>" class="item-input item-label" v-if="transfer_type==2">
                <p class="label-img"><img src="/static/market/img/alipay.png" alt=""></p>
                <p class="label-color">当前绑定支付宝：{{transfer_account}}</p>
            </a>
            <a href="<?= Url::to(['market/bind-transfer']); ?>" class="item-input item-label" v-if="transfer_type==3">
                <p class="label-img"><img src="/static/market/img/car.png" alt=""></p>
                <p class="label-color">当前绑定银行卡: {{transfer_account}}</p>
            </a>
            <a href="<?= Url::to(['market/bind-transfer']); ?>" class="item-input" v-if="!transfer_type">
                <p><img src="/static/market/img/bank_car.png" alt=""></p>
                <p class="input-label">点击绑定提现账号</p>
            </a>
        </div>
        <div class="withdrawalFooterText" id="transferBalance">
            <p>温馨提示</p>
            <div class="item">
                <p>1、</p>
                <p>全民合伙人活动的收入必须转到账号余额或者直接提现，才可以使用。</p>
            </div>
            <div class="item ">
                <p>2、</p>
                <p>余额转账金额必须不得低于30元。</p>
            </div>
            <div class="item">
                <p>3、</p>
                <p>余额转账，不需审核，立马到账</p>
            </div>
            <div class="item">
                <p>4、</p>
                <p>如在转账过程中有任何疑问，请咨询无忧帮帮官方客服热线400-135-5188</p>
            </div>
        </div>
        <div class="withdrawalFooterText" id="transferBank" hidden>
            <p>温馨提示</p>
            <div class="item">
                <p>1、</p>
                <p>全民合伙人活动的收入必须转到账号余额或者直接提现，才可以使用。</p>
            </div>
            <div class="item ">
                <p>2、</p>
                <p>提现金额必须不得低于30元。</p>
            </div>
            <div class="item">
                <p>3、</p>
                <p>提现时间：每个星期二及星期五的00:00-17:00</p>
            </div>
            <div class="item">
                <p>4、</p>
                <p>提提现申请后，资金会处于冻结状态。申请将在3个工作日内审批并到账。
                    双休日和法定假日顺延。</p>
            </div>
            <div class="item ">
                <p>5、</p>
                <p>如在提现过程中有任何疑问，请咨询无忧帮帮官方客服热线400-135-5188</p>
            </div>
            <div class="item item-last">
                <p>备注：</p>
                <p>通常提现到账时间为提现日当天17:00-次日17:00（具体以银行放款时间为准）</p>
            </div>
        </div>
    </div>
    <div class="marketFooter withdrawalButton">
        <div class="tabButton">
            <button v-on:click="transferClick" id="transferBtn">立即提现</button>
        </div>
    </div>

    <div class="messagePopup visible active hide">
        <div class="popup-body">
            <div class="popupOverflow-body" align="center">
                <p>{{alertContent}}</p>
            </div>
        </div>
    </div>

    <div class="inputPwd active visible hide">
        <div class="plateNumber-body" align="center">
            <div class="plate-center" align="center">
                <div class="row fullWidth" align="center">
                    <p>提现成功</p>
                </div>
                <div class="certain row fullWidth" align="center">
                    <button class="confirmColor font-normal" @click="certainTransfer">确定</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    var flag = 0;
    var data =<?=json_encode($data) ?>;
    data = $.extend(data, {input: "", alertContent: "请输入提现金额"});
    new Vue({
        el: "#withdrawal",
        data: data,
        //监听input 如果>=30按钮高亮
        watch: {
            input: {
                handler: function (val) {
                    if (val >= 30 && this.current_profit>=30)
                        $("#transferBtn").addClass("withdrawal-button");
                    else
                        $("#transferBtn").removeClass("withdrawal-button");
                },
                deep: true
            }
        },
        methods: {
            showToast: function (content) {
                this.alertContent = content;
                $(".messagePopup").show();
                setTimeout(function () {
                    $(".messagePopup").hide();
                },1000)
            },
            changeWithdrawTab: function (tab_num) {
                for (i = 0; i <= 1; i++) {
                    $("#withdraw_" + i).hide();
                    $("#changeWithdraw_" + i).hide();
                    $("#changeFontColor"+i).removeClass("changeFontColor");
                }
                flag = tab_num;
                if (tab_num == 0) {
                    $(".turnContent").text("未转账金额");
                    $("#userTransfer").text("转账余额不得低于30元");
                    $("#transferBank").hide();
                    $("#transferBalance").show();
                    $(".transferList").hide();


                } else {
                    $(".turnContent").text("未提现金额");
                    $("#userTransfer").text("提现金额不得低于30元");
                    $("#transferBalance").hide();
                    $("#transferBank").show();
                    $(".transferList").show();
                }
                $("#withdraw_" + tab_num).show();
                $("#changeWithdraw_" + tab_num).show();
                $("#changeFontColor"+tab_num).addClass("changeFontColor");
            },
            //点击全部提现，赋值input
            dataAll: function (val) {
                $("#number").val(val );
                this.input = val;
            },
            //提现功能 判断是小帮还是用户入口 url改变 提示改变
            transferClick: function () {
                var url;
                const val = $("#number").val();
                var slef = this;
                if (slef.current_profit<30)
                    return slef.showToast("未转账或未提现金额少于30元，无法转账或提现");
                if (flag == 0 || this.entrance == "provider") {
                    url = "/market/ajax-transfer";
                    if (!val)
                        return slef.showToast("请输入转账金额");
                    if (val < 30)
                        return slef.showToast("转账金额不能少于30元");
                }

                if (flag == 1) {
                    console.log("come")
                    url = "/market/ajax-withdraw";
                    if (!val)
                        return slef.showToast("请输入提现金额");
                    if (val < 30)
                        return slef.showToast("提现金额不能少于30元");
                    if (!data.transfer_type)
                        return slef.showToast("请绑定提现账号");
                }
                $.ajax({
                    url: url,
                    type: 'get',
                    data: {
                        transfer_amount: val
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (flag == 1 && res.code === 0)
                            return window.location.href = "withdraw-success"
                        else {
                            if (res.code == 0) {
                                $(".inputPwd").show();
                                slef.input = "";
                                $("#number").val("");
                                slef.current_profit =res.data.data.current_profit;
                            }
                            else slef.showToast(res.message);
                        }
                    },
                    error: function (res) {
                        return slef.showToast("提现失败");
                    }
                });
            },
            certainTransfer: function () {
                $(".inputPwd").hide()
            }

        }
    })
</script>
</html>
