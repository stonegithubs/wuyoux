<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>绑定账号</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/static/market/css/index.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/vue.js"></script>
    <script type="text/javascript" src="/static/market/js/bank.js"></script>
</head>
<body>
<div class="marketIndex bind" id="bindingTransfer" v-cloak>
    <div class="bind-header">
        <div class="header header-common" id="profitTab">
            <ul>
                <li @click="changeBindTab('3')" id="bindTab3" class="activeTitle">银行卡账号</li>
                <hr>
                <li @click="changeBindTab('2')" id="bindTab2">支付宝账号</li>
            </ul>
        </div>
        <div class="binding-data binding-bank-data" id="bind_3">
            <div v-if="transfer_type==3">
                <input type="text" placeholder="点击输入持卡人姓名" id="bindBankName" v-model="transfer_realname">
                <!--                <input type="text" placeholder="点击输入开户行名" id="bindBank" v-model="transfer_accountname">-->
                <input type="text" placeholder="点击输入银行卡号" maxlength="19" id="bindBankAccount"
                       v-model="transfer_account">
                <p>{{transfer_accountname}}</p>
            </div>

            <div v-else>
                <input type="text" placeholder="点击输入持卡人姓名" id="bindBankName" >
                <!--                <input type="text" placeholder="点击输入开户行名" id="bindBank">-->
                <input type="text" placeholder="点击输入银行卡号" maxlength="19" id="bindBankAccount"
                       v-model="transfer_none">
                <p  id="bankName"></p>
            </div>
        </div>
        <div class="binding-data binding-alipay-data" id="bind_2">
            <div v-if="transfer_type==2">
                <input type="text" placeholder="点击输入姓名" id="bindAlipayName" v-model="transfer_realname">
                <input type="text" placeholder="点击输入支付宝账号" id="bindAlipayAccount" maxlength="25" v-model="transfer_account">
            </div>
            <div v-else>
                <input type="text" placeholder="点击输入姓名" id="bindAlipayName">
                <input type="text" placeholder="点击输入支付宝账号" maxlength="25" id="bindAlipayAccount" >
            </div>
        </div>
        <button class="transferTip" @click="certainBind">
            保存
        </button>
    </div>
    <div class="withdrawalFooterText">
        <p>提现规则</p>
        <div class="item">
            <p>1、</p>
            <p>提现时间：每个星期二及星期五的00：00-17：00</p>
        </div>
        <div class="item ">
            <p>2、</p>
            <p>如遇提现日是节假日则延后至下个提现日进行处理。
                （例：如星期五为国家法定假日申请提现，提现时间
                将会推迟到下个星期二进行处理)</p>
        </div>
        <div class="item">
            <p>3、</p>
            <p>到账时间：在提现的时间内发起的提现会在3个工作
                日内到账。</p>
        </div>
        <div class="item">
            <p>4、</p>
            <p>提现申请后，资金会处于冻结状态。
                备注：通常提现到账时间为提现日当天17：00-次日
                17：00（具体以银行放款时间为准）</p>
        </div>
    </div>

    <div class="messagePopup visible active hide">
        <div class="popup-body">
            <div class="popupOverflow-body" align="center">
                <p>{{alertContent}}</p>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    var flag = 3;//默认是银行卡类型
    var data =<?=json_encode($data) ?>||
    {
        transfer_account: "", transfer_realname
    :
        "", transfer_accountname
    :
        "", transfer_type
    :
        ""
    }
    ;
    data = $.extend(data, {alertContent: "请输入提现金额",transfer_none:""});//拼接对象
    new Vue({
        el: "#bindingTransfer",
        data: data,
        //监听输入银行卡账号获取到银行类型
        watch: {
            transfer_account: {
                handler: function (val) {
                    var self = this;
                    const bank = Bank.bankCardAttribution(val);
                    self.transfer_accountname = bank.bankName || "其他银行";
                },
                deep: true
            },
            transfer_none: {
                handler: function (val) {
                    var self = this;
                    const bank = Bank.bankCardAttribution(val);
                    $("#bankName").text(bank.bankName || "其他银行") ;
                    self.transfer_accountname = bank.bankName || "其他银行";
                },
                deep: true
            }
        },
        //渲染成功后获取到数据transfer_type，3显示银行卡账号，2显示支付宝账号
        mounted: function () {
            if (data.transfer_type != "") {
                for (i = 2; i <= 3; i++) {
                    $("#bind_" + i).hide();
                    $("#bindTab" + i).removeClass("activeTitle");
                }
                flag = data.transfer_type;
                $("#bindTab" + data.transfer_type).addClass("activeTitle");
                $("#bind_" + data.transfer_type).show();
            }
        },
        methods: {
            //显示toast提示，content是显示的内容
            showToast: function (content) {
                this.alertContent = content;
                $(".messagePopup").show();
                setTimeout(function () {
                    $(".messagePopup").hide();
                }, 1500)
            },
            changeBindTab: function (tab_num) {
                for (i = 2; i <= 3; i++) {
                    $("#bind_" + i).hide();
                    $("#bindTab" + i).removeClass("activeTitle");
                }
                flag = tab_num;
                $("#bindTab" + tab_num).addClass("activeTitle");
                $("#bind_" + tab_num).show();
            },
            //保存绑定提现数据
            certainBind: function () {
                var name, account, bindType;
                var slef = this;
                if (flag == 2) {
                    name = $("#bindAlipayName").val();
                    account = $("#bindAlipayAccount").val();
                    bindType = "支付宝"
                }
                if (flag == 3) {
                    name = $("#bindBankName").val();
                    account = $("#bindBankAccount").val();
                    bindType = slef.transfer_accountname;
                    if (!bindType)
                        return slef.showToast("保存失败");
                }
                if (!name)
                    return slef.showToast("请输入姓名");
                if (!account)
                    return slef.showToast("请输入绑定账号");
                $.ajax({
                    url: "/market/ajax-bind-transfer",
                    type: 'get',
                    data: {
                        transfer_realname: name,
                        transfer_accountname: bindType,
                        transfer_account: account,
                        transfer_type: flag
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.code === 0) {
                            self.transfer_account = res.data.data.transfer_account;
                            self.transfer_realname = res.data.data.transfer_realname;
                            self.transfer_accountname = res.data.data.transfer_accountname;
                            self.transfer_type = res.data.data.transfer_type;
                            slef.showToast("保存成功");
                            setTimeout(function () {
                                window.location.href = "bind-transfer"
                            },500);
                        }
                        else slef.showToast("保存失败");
                    },
                    fail: function (res) {
                        return res.code == 0 ? slef.showToast("保存失败") : slef.showToast("保存失败");
                    }
                });
            }
        }
    })
</script>
</html>