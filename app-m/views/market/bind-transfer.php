<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/6/4
 */

use \yii\helpers\Url;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>绑定账号</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/vue.js"></script>
</head>
<body>
<div class="marketIndex bind" id="bindTransfer" v-cloak>
    <div class="bind-header">
        <div class="bind-header-title" v-if="!transfer_type">
            <p>银行卡/支付宝</p>
        </div>
        <div class="bind-header-title" v-if="transfer_type==2">
            <p>{{transfer_realname}} 的支付宝</p>
            <p><img src="/static/market/img/icon_delete.png" alt="" @click="clearBindData"></p>
        </div>
        <div align="left" class="bind-header-title" v-if="transfer_type==3">
            <p>{{transfer_realname}}  的银行卡</p>
            <p><img src="/static/market/img/icon_delete.png" alt="" @click="clearBindData"></p>
        </div>
        <div align="left" class="number-margin">
            <p>{{transfer_account}}</p>
        </div>
        <a href="<?= Url::to(['market/binding-transfer']); ?>" class="transferTip">
            <p v-if="!transfer_type">点击添加</p>
            <p v-if="transfer_type">点击修改</p>
            <p><img src="/static/market/img/btn_enter.png" alt=""></p>
        </a>
        <div class="bind-img" v-if="transfer_type==2">
            <img src="/static/market/img/img_alipay_bound.png" alt="">
        </div>
        <div class="bind-img" v-else>
            <img src="/static/market/img/img_card_bound_default.png" alt="">
        </div>
    </div>
    <div class="withdrawalFooterText">
        <p>温馨提示：</p>
        <div class="item">
            <p>1、</p>
            <p>平台暂时只支持绑定银行卡和支付宝来提现。</p>
        </div>
        <div class="item ">
            <p>2、</p>
            <p>每次只能绑定一个账号，如果需要更换账号，必须删除之前的账号，再重新绑定。</p>
        </div>
        <div class="item">
            <p>3、</p>
            <p>绑定的账号必须为本人真实账号。</p>
        </div>
        <div class="item">
            <p>4、</p>
            <p>如在账号绑定过程中有任何疑问，请咨询无忧帮帮官方客服热线400-135-5188</p>
        </div>
    </div>

    <div class="inputPwd active visible hide">
        <div class="plateNumber-body" align="center">
            <div class="plate-center" align="center">
                <div class="row" align="center">
                    <p>删除该提现账号后</p>
                    <p>将不能在使用该账号提现</p>
                </div>
                <div class="certain row" align="center">
                    <button class="cancelColor" @click="confirm">删除</button>
                    <button class="confirmColor font-normal" @click="cancel">我再想想</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    var data =<?=json_encode($data) ?>||
    {transfer_account: "", transfer_realname: "", transfer_accountname: "", transfer_type: ""};
    new Vue({
        el: "#bindTransfer",
        data: data,
        //在渲染成功后拿到银行卡账号或者支付宝账号除了后四位的都给*号
        mounted: function () {
            var self = this;
            if (self.transfer_account){
                var str="" ;
                for (var i = 0; i<=self.transfer_account.length-5 ;i++){
                    str = str+"*";
                    if (self.transfer_type == 3)
                        if (str.length==4||str.length===10||str.length==11||str.length==16)
                            str = str + "  "
                    if(self.transfer_type == 2 )
                        if (str.length==3||str.length===9||str.length==11)
                            str = str + "  "
                }
                str = str + "  "
                    return   self.transfer_account = str + self.transfer_account.substr(-4);

            }

        },
        methods: {
            //点击删除icon按钮弹框显示
            clearBindData: function () {
                $(".inputPwd").show()
            },
            //点击取消按钮弹框隐藏
            cancel: function () {
                $(".inputPwd").hide()
            },
            //点击删除按钮，请求接口清空绑定提现数据
            confirm: function () {
                var self = this;
                $(".inputPwd").hide()
                $.ajax({
                    url: "/market/ajax-clear-transfer",
                    type: 'get',
                    data: {},
                    dataType: 'json',
                    success: function (res) {
                        if (res.code === 0) {
                            self.transfer_account = null;
                            self.transfer_realname = null;
                            self.transfer_accountname = null;
                            self.transfer_type = null;
                        }
                    },
                    error: function (res) {
                        console.log('fail');
                    }
                });
            }

        }
    })
</script>
</html>

