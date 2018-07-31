<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 15:04
 */

use m\assets\AppAsset;
use yii\helpers\Url;
$asset = AppAsset::register($this);
AppAsset::register($this);
$this->registerCssFile("/static/css/school.css");
?>

<block name="body">

	<!-- Views-->
	<div class="views" id="school">
		<!-- Your main view, should have "view-main" class-->
		<div class="view view-main">
			<div class="pages">

				<div data-page="index" class="page school_con">
					<div class="page-content">

						<div class="title"><i class="iconfont icon-xuhao701"></i> 抢单</div>
								<div class="tab"><span>出行、快送抢单</span></div>
								<div class="img"><img src="/static/school/img_banner5.png" style="width:100%"></div>
						<div class="sub_title" style="font-size: 1em;line-height:2;color:#666">
						    <p>小帮处于上班状态的时候，可以2种方式抢单：</p>
						    <p>1、订单提示会弹屏10秒，可选择不抢或抢单</p>
						    <p>2、 查看订单海，订单海存在的订单都是还未抢状态，小帮都可以查看或者抢单</p>
						</div>
					<div class="img">	<img src="/static/school/img_banner6.png" style="width:100%"></div>

							<div class="tab"><span>企业送抢单</span></div>
                        						<div class="img"><img src="/static/school/img_banner7.png" /></div>
                        						<div class="con" style="font-size: 1em;line-height:2;color:#666">当有企业送订单生成时，小帮需点击“查看”进入订单海进行抢单。</div>
					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 50%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_07']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_09']); ?>"  class="button button-big button-fill">下一页</a>
							</div>

						</div>
					</div>

				</div>

			</div>

		</div>
	</div>

</block>

<block name="script">

</block>