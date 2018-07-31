<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 16:08
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

						<div class="title"><i class="iconfont icon-xuhao101"></i> 抢到订单后</div>
						<div class="sub_title">1、请立即联系下单客户，确认信息（在哪等，附近建筑物有什么，什么时候过去），方便找到用户。</div>
						<div class="con" style="color:#999">（参考话术：“您好，我是无忧帮帮X师傅，很高兴为您服务，您是要从**送到**？”）</div>
						<div class="sub_title">2、如果订单无误，请告知客户预计到达时间</div>
						<div class="con" style="color:#999">（参考话术：“请您稍等片刻，我现在出发，大约XX分钟到达。”）</div>
						<div class="sub_title">3、到达用户预定地点后</div>
						<div class="con" style="color:#999">（因客户不在或繁忙等原因而需要等待时，再提醒客户尽快到达预定地点的同时应尽量避免打扰客户；如果等待时间即将超过20分钟时，一定要提醒客户超出时间后将取消订单）</div>
						<div class="img"><img src="/static/images/md_01.jpg" style="width: 80%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="btn">
								<a href="<?= Url::to(['school/motocycle','view'=>'motocycle_02']); ?>" style="width: 100%;" class="button button-big button-fill">下一页</a>
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