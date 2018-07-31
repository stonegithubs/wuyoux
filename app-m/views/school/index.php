<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/19
 * Time: 10:25
 */

use yii\helpers\Url;
use m\assets\AppAsset;

AppAsset::register($this);
$this->registerCssFile("/static/css/school.css");
?>
<block name="body">
    <!-- Views-->
    <div id="school">
        <!-- Your main view, should have "view-main" class-->
        <div class="view view-main">
            <div class="pages">

                <div class="page" data-page="home">
                    <div class="page-content school_index" style="padding: 5px !important;">
                        <div class="school_index_box">
                            <div class="title"><i class="iconfont icon-peixun-copy"></i>&nbsp;常规培训</div>
                            <div class="sub_title">培训教程与接单相关，参加相应培训才能接单</div>

                            <div class="task_list">
                                <ul>
                                    <li>
                                        <div class="icon"><i class="iconfont icon-xinshou txt_green"></i></div>
                                        <div class="task">
                                            <p>新手入门基础</p>
											<? if ($base_status == 1) { ?>
                                                <p class="txt_green">已通过</p>
											<? } else { ?>
                                                <p class="txt_deep_orange">待学习</p>
											<? } ?>
                                        </div>
										<? if ($base_status == 1) { ?>
                                            <div class="again_btn"><a href="<?= Url::to(['school/base','view'=>'base_01']); ?>" onclick="clickIosBase()">再学一遍</a>
                                            </div>
										<? } else { ?>
                                            <div class="start_btn"><a href="<?= Url::to(['school/base','view'=>'base_01']); ?>"  onclick="clickIosBase()">开始学习</a>
                                            </div>
										<? } ?>
                                    </li>

									<? if ($base_status == 1) { ?>
                                        <li>
                                            <div class="icon">
                                                <i class="iconfont icon-mode txt_deep_orange"></i>
                                            </div>
                                            <div class="task">
                                                <p>小帮出行订单</p>
												<? if ($trailer_status == 1) { ?>
                                                    <p class="txt_green">已通过</p>
												<? } else { ?>
                                                    <p class="txt_deep_orange">待学习（选学）</p>
												<? } ?>
                                            </div>
											<? if ($trailer_status == 1) { ?>
                                                <div class="again_btn"><a href="<?= Url::to(['school/motocycle','view'=>'motocycle_01']); ?>"  onclick="clickIosMoto()">再学一遍</a>
                                                </div>
											<? } else { ?>
                                                <div class="start_btn"><a href="<?= Url::to(['school/motocycle','view'=>'motocycle_01']); ?>"  onclick="clickIosMoto()">开始学习</a>
                                                </div>
											<? } ?>
                                        </li>
									<? } else { ?>
                                        <li>
                                            <div class="icon">
                                                <i class="iconfont icon-mode txt_ddd"></i>
                                            </div>
                                            <div class="task">
                                                <p class="txt_999">摩的专车订单</p>
                                                <p class="txt_999">待学习（选学）</p>
                                            </div>
                                            <div class="lock_btn"><a href="#"  onclick="clickIosMoto()">开始学习</a></div>
                                        </li>
									<? } ?>

									<? if ($base_status == 1) { ?>
                                        <li>
                                            <div class="icon">
                                                <i class="iconfont icon-paotui1 txt_brown"></i>
                                            </div>
                                            <div class="task">
                                                <p>小帮快送订单</p>
												<? if ($errand_status == 1) { ?>
                                                    <p class="txt_green">已通过</p>
												<? } else { ?>
                                                    <p class="txt_deep_orange"  onclick="clickIos()">待学习（选学）</p>
												<? } ?>
                                            </div>
											<? if ($errand_status == 1) { ?>
                                                <div class="again_btn"><a
                                                            href="<?= Url::to(['school/errand','view'=>'errand_01']); ?>"  onclick="clickIosErrand()">再学一遍</a></div>
											<? } else { ?>
                                                <div class="start_btn"><a
                                                            href="<?= Url::to(['school/errand','view'=>'errand_01']); ?>"  onclick="clickIosErrand()">开始学习</a></div>
											<? } ?>
                                        </li>
									<? } else { ?>
                                        <li>
                                            <div class="icon">
                                                <i class="iconfont icon-paotui1 txt_ddd"></i>
                                            </div>
                                            <div class="task">
                                                <p class="txt_999">小帮快送订单</p>
                                                <p class="txt_999">待学习（选学）</p>
                                            </div>
                                            <div class="lock_btn"><a href="#"  onclick="clickIosErrand()">开始学习</a></div>
                                        </li>
									<? } ?>

									<? if ($base_status == 1) { ?>
                                        <li>
                                            <div class="icon">
                                               <!--- <i class="iconfont icon-paotui1 txt_brown"></i>--->
                                               <img src="/static/school/icon_shop.png" style="width:48px" >
                                            </div>
                                            <div class="task">
                                                <p>企业送订单</p>
												<? if ($biz_status == 1) { ?>
                                                    <p class="txt_green" >已通过</p>
												<? } else { ?>
                                                    <p class="txt_deep_orange" >待学习（选学）</p>
												<? } ?>
                                            </div>
											<? if ($biz_status == 1) { ?>
                                                <div class="again_btn"><a
                                                            href="<?= Url::to(['school/biz','view'=>'biz_01']); ?>"  onclick="clickIosBiz()">再学一遍</a></div>
											<? } else { ?>
                                                <div class="start_btn"><a
                                                            href="<?= Url::to(['school/biz','view'=>'biz_01']); ?>"  onclick="clickIosBiz()">开始学习</a></div>
											<? } ?>
                                        </li>
									<? } else { ?>
                                        <li>
                                            <div class="icon">
                                               <!--- <i class="iconfont icon-paotui1 txt_ddd"></i>-->
                                                 <img src="/static/school/icon_shop_off.png" style="width:48px" >
                                            </div>
                                            <div class="task">
                                                <p class="txt_999">企业送订单</p>
                                                <p class="txt_999">待学习（选学）</p>
                                            </div>
                                            <div class="lock_btn"><a href="#"  onclick="clickIosBiz()">开始学习</a></div>
                                        </li>
									<? } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

     <script>
        function clickIosBase(){
        jsInterface.schoolCallback("新手入门基础");
        }
         function clickIosMoto(){
             jsInterface.schoolCallback("小帮出行订单");
        }
        function clickIosErrand(){
             jsInterface.schoolCallback("小帮快送订单");
        }
        function clickIosBiz(){
            jsInterface.schoolCallback("企业送订单");
        }

     </script>
</block>






