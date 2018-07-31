<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/10
 * Time: 15:55
 */
$this->title = '用户收支明细';
?>
<block name="style">
    <style>
        .pages {
            background: #fff;
        }
        .order_navbar .list-block {
            margin: 44px 0 0 0;
        }
        .order_navbar .buttons-row{background: #fff;}
        #trade_list .list-block .item-link .item-inner{
            padding: 10px 0;
        }
        .list-block .item-inner, #trade_list .list-group .list-group-title {
            padding: 0 7px;
            height: 50px;
        }
        .list-block .item-inner:after {
            height: 0;
        }
        .order_navbar .list-block .item-title span{
            border: 0;
            color: #aaa;
            font-weight: normal;
        }
        .count_list .item-title,#trade_list .list-group .list-group-title .item-title{
            font-size: 14px !important;
        }
        .count_list .item-after{
            font-size: 16px;
            color: #ff6000;
        }
    </style>

</block>
<block name="body">
    <!-- Views-->
    <div class="views" id="trade_list" >
        <!-- Your main view, should have "view-main" class-->
        <div class="view view-main">
            <div class="pages order_navbar">
                <div class="page" data-page="home">
                <!--<div data-page="home">-->
                    <div class="navbar">
                        <div class="navbar-inner">
                            <!-- 导航栏中的按键排控制器 -->
                            <div class="buttons-row">
                                <!-- 关联到第一个tab的链接，默认激活 -->
                                <!--<a onclick="changeType(3)" id="type3" class="tab-link active button">全部</a>-->
                                <!-- 关联到第二个tab的链接 -->
                                <a onclick="changeType(2)"  id="type2" class="tab-link <?php if($type==2)echo 'active' ?> button">支出</a>
                                <!-- 关联到第三个tab的链接 -->
                                <a onclick="changeType(1)" id="type1" class="tab-link <?php if($type==1)echo 'active' ?> button">收入</a>
                            </div>
                        </div>
                    </div>
                    <!-- Tabs, tabs容器, page-content为tabs -->
                    <div class="page-content infinite-scroll" data-distance="100">

                        <div class="list-block contacts-block">
                            <div class="list-group message-list">
                                <ul>

                                </ul>
                            </div>
                        </div>
                        <!-- 加载提示符 -->
                        <div id="loading"></div>

                    </div>
                </div>

            </div>

        </div>
    </div>

</block>

<script type="text/javascript" src="/static/js/framework7.min.js"></script>
<block name="script">
    <script>
        var myApp = new Framework7();
        // 初始化 app

        var $$ = Dom7;

        // 加载flag
        var loading = false;

        // 上次加载的序号
        var lastIndex = $$('.message-list li').length;

        // 最多可加载的条目
        var maxItems = <?= $count ?>;

        // 每次加载添加多少条目
        var itemsPerLoad = 20;

        // 注册'infinite'事件处理函数
        var page = 1;
        var uid =   <?= $uid ?>;
        var type = <?= $type ?>;
        var code = "<?= $code ?>";

        var ajax_user_bill_num_url = "<?= $ajax_user_bill_num_url ?>";
        var ajax_user_bill_url = "<?= $ajax_user_bill_url ?>";

        //修改url
        function changeType(type) {
            type = parseInt(type);
            page = 1;
            lastIndex = 0;

            var url = '/wallet/user-bill' + '?type=' + type;
            window.history.pushState(null, null, url);


            $$.getJSON(ajax_user_bill_num_url, {
                type: type,
            }, function (data) {
                maxItems = parseInt(data.data.count);
            });
            showInLoading();
            $$('.message-list ul').html('');

            $$.getJSON(ajax_user_bill_url, {
                type: type,
            }, function (data) {
                // 添加新条目
                var money_list = data.list;
                if (money_list != '') {
                    $$('.message-list ul').html(data.list);
                }
                else {

                    //no_message('暂无信息');
                }
                unshowInLoading();
            });
        }

        if (code == "20000") {
            no_message('账号异常');
        } else {
            showInLoading();//显示菊花
            $$.getJSON(ajax_user_bill_url, {
                type: type
            }, function (data) {
                // 添加新条目
                var money_list = data.list;
                if (money_list != '') {

                    $$('.message-list ul').append(data.list);
                }
                else {
                    //no_message('暂无信息');

                }
                unshowInLoading();
            });

            $$('.infinite-scroll').on('infinite', function () {

                // 如果正在加载，则退出
                if (loading) return;

                // 设置flag
                loading = true;
                // 模拟1s的加载过程
                setTimeout(function () {
                    // 重置加载flag
                    loading = false;
                    if (lastIndex >= maxItems) {
                        // 加载完毕，则注销无限加载事件，以防不必要的加载
                        myApp.detachInfiniteScroll($$('.infinite-scroll'));
                        // 删除加载提示符
                        //$$('.infinite-scroll-preloader').remove();
                        showInBottleLoading();
                        return;
                    }

                    // 生成新条目的HTML
                    page++;
                    type = getQueryString("type");//重新定义type
                    if(!type){
                        type = 2;
                    }
                    showInLoading();
                    $$.getJSON(ajax_user_bill_url, {
                        uid: uid,
                        page: page,
                        type: type,
                        pagesize: itemsPerLoad
                    }, function (data) {
                        // 添加新条目
                        unshowInLoading();
                        $$('.message-list ul').append(data.list);
                        $$('.swipeout').on('swipeout:deleted', function () {
                            var id = $(this).attr('data-id');
                            $$.getJSON("{:U('message/del_msg')}", {id: id}, function (data) {
                                if (data.status == 200) {
                                    myApp.alert('删除成功');
                                }
                                else {
                                    myApp.alert('删除失败');
                                }
                            });
                        });
                    });

                    // 更新最后加载的序号
                    lastIndex = $$('.message-list .count_list').length;
                }, 100);
            });
        }
        $$("#type{$type}").addClass("active");
        //tab点击切换变色
        $(function () {
            $(".buttons-row a").click(function () {
                $(".buttons-row a").eq($(this).index()).addClass("active").siblings().removeClass("active");
            })
        })
    </script>

</block>