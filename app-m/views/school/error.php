<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/19
 * Time: 16:31
 */
use m\assets\AppAsset;
$asset = AppAsset::register($this);
?>

<block name="body">
<style type="text/css">
    .logo1 {
	text-align: center;
    }
    .logo1 img {
	width: 60%;
	margin-top: 50px;
    }
    .error {
	text-align: center;
        font-size: 40px;
		margin: .5em 0;
    }

</style>
    <!-- Views-->
    <div class="views">
        <!-- Your main view, should have "view-main" class-->
        <div class="view view-main">
            <!-- Pages, because we need fixed-through navbar and toolbar, it has additional appropriate classes-->
            <div class="pages navbar-through toolbar-through">
                <!-- Page, data-page contains page name-->
                <div data-page="index" class="page">
                    <!-- Scrollable page content-->
                    <div class="page-content">

                        <div class="system-message">
                            <div class="logo1">
                                <img src="/static/images/no_order.png"/>
                            </div>
                            <p class="error">暂无消息</p>
                            <p class="detail"></p>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



</block>


<block name="script">

</block>

