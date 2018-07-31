<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/24
 */
?>

<block name="style">
    <link rel="stylesheet" href="/static/message/css/message.css">
</block>
<block name="body">
    <!-- Views-->
    <div class="views">
        <!-- Your main view, should have "view-main" class-->
        <div class="view view-main">
            <!-- Pages, because we need fixed-through navbar and toolbar, it has additional appropriate classes-->
            <div class="pages navbar-through toolbar-through">
                <!-- Page, data-page contains page name-->
                <div data-page="index" class="page detail">
                    <div class="page-content">
                        <div class="content-block-inner">
                            <div class="item-inner">
                                <div class="item-subtitle"><?= $msg['title'] ?></div>
                                <div class="item-text"><?= $msg['create_time'] ?></div>
                            </div>
                        </div>
                        <div class="content-block">
                            <p><?= $msg['content'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</block>
<block name="script">
    <script type="text/javascript">
        function msgcount() {
            return JSON.stringify({"<?= $count ?>"});
        }
    </script>
</block>
