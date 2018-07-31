<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 16:40
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
						<div class="sub_title">1、请分辨清楚是“帮我买”还是“帮我送”订单，然后立即联系下单客户，确认订单信息（购买地址、取货地址、配送物品等），核实无误再前往购买或取货地点。</div>
						<div class="con" style="color:#999">（参考话术：“您好，我是小帮快送X师傅，很高兴为您服务，请您确认订单信息，您是否有**（物品），需要从**送到**？”）</div>
						<div class="sub_title">2、如果订单无误，请告知客户预计到达时间</div>
						<div class="con" style="color:#999">（参考话术：“请您稍等片刻，我现在出发，大约XX分钟到达。”）</div>

						<div class="img"><img src="/static/images/ks_01.jpg" style="width: 80%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="btn">
								<a href="<?= Url::to(['school/errand','view'=>'errand_02']); ?>" style="width: 100%;" class="button button-big button-fill">下一页</a>
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