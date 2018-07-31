<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/23
 */
$this->title = '消息';
?>
<extend name="Base/Base"/>
<block name="style">
	<link rel="stylesheet" href="/static/message/css/message.css">
    <style scoped>
        .error {
            width: 50%;
            margin: 75% auto 0 auto;
            display: none;
        }

        .title {
            margin: 30px auto;
            line-height: 28px

        }

        .logo {
            position: fixed;
            bottom: 30px;
            left: 0%
        }
    </style>
</block>
<block name="body">
	<!-- Views-->
	<div class="views">
        <div class="error" style="text-align:center">
            <p class="title" style="font-size: 18px;color: #8e8e93;">暂无消息通知</p>

            <div class="logo" style="text-align:center"><img src="/static/404/img/userbar_logo.png" width="40%"/></div>
        </div>
		<!-- Your main view, should have "view-main" class-->
		<div class="view view-main">
			<!-- Pages, because we need fixed-through navbar and toolbar, it has additional appropriate classes-->
			<div class="pages navbar-through toolbar-through">
				<!-- Page, data-page contains page name-->
				<div data-page="index" class="page">
					<div class="page-content infinite-scroll">

						<div class="message-list">
							<ul></ul>
						</div>
						<div class="infinite-scroll-preloader" style="display: none;">
							<div class="preloader"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</block>
<script type="text/javascript" src="/static/js/framework7.min.js"></script>
<block name="script">
	<script>
        //  var myApp = new Framework7();
        var myApp = new Framework7({
            modalTitle: "提示",
            modalButtonOk: "确定",
            modalButtonCancel: "取消",
            modalPreloaderTitle: "加载中...",
            modalUsernamePlaceholder: "用户名",
            modalPasswordPlaceholder: "密码"
        });
        // 初始化 app

        var $$ = Dom7;

        // 加载flag
        var loading = false;

        // 上次加载的序号
        var lastIndex = $$('.message-list li').length;

        // 最多可加载的条目
        var maxItems = "<?= $count ?>";

        // 每次加载添加多少条目
        var itemsPerLoad = 20;

        // 注册'infinite'事件处理函数
        var page = 1;
        var uid = "<?= $user_id ?>";
        $$.getJSON("<?= $ajax_get_message_url ?>", {
            uid: uid,
            type: 1,
            page: page,
            pagesize: itemsPerLoad,
			count:maxItems
        }, function (data) {
            console.log(data);
            if(data.status == 404) {
                $('.view-main').css('display', 'none');
                $('.error').css('display', 'block');
            }

            // 添加新条目
            $$('.message-list ul').append(data.data.list);
            $$('.swipeout').on('swipeout:deleted', function () {
                var id = $(this).attr('data-id');
                $$.getJSON("{:U('message/del_msg')}", {id:id}, function (data) {
                    if (data.status == 200)
                    {
                        myApp.alert('删除成功');
                    }
                    else
                    {
                        myApp.alert('删除失败');
                    }
                });
            });
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
                    $$('.infinite-scroll-preloader').remove();
                    return;
                }

                // 生成新条目的HTML
                page++;
                $$.getJSON("<?= $ajax_get_message_url ?>", {
                    uid: uid,
                    type: 1,
                    page: page,
                    pagesize: itemsPerLoad
                }, function (data) {
                    // 添加新条目
                    $$('.message-list ul').append(data.data.list);
                    $$('.swipeout').on('swipeout:deleted', function () {
                        var id = $(this).attr('data-id');
                        $$.getJSON("{:U('message/del_msg')}", {id:id}, function (data) {
                            if (data.status == 200)
                            {
                                myApp.alert('删除成功');
                            }
                            else
                            {
                                myApp.alert('删除失败');
                            }
                        });
                    });
                });

                // 更新最后加载的序号
                lastIndex = $$('.message-list li').length;
            }, 100);
        });
	</script>
</block>
