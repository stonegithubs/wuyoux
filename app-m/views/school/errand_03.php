<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 16:43
 */
use m\assets\AppAsset;
use yii\helpers\Url;
$asset = AppAsset::register($this);
AppAsset::register($this);
$this->registerCssFile("/static/css/school.css");
?>

	<!-- Views-->
	<div class="views" id="school">
		<!-- Your main view, should have "view-main" class-->
		<div class="view view-main">
			<div class="pages">

				<div data-page="index" class="page school_con">
					<div class="page-content">

						<div class="title"><i class="iconfont icon-xuhao301"></i> 出发送货前</div>
						<div class="sub_title">1、请联系收货人，核实订单信息（收货地址、配送物品等）。如订单信息不正确，请立即与发货人协商解决。</div>
						<div class="con" style="color:#999">（参考话术：“您好，我是小帮快送X师傅，很高兴为您服务，有您的一个物件需要签收，请问您是在xx吗？”）</div>
						<div class="sub_title">2、如果订单无误，请告知对方预计送达时间</div>
						<div class="con" style="color:#999">（参考话术：“请您稍等，我现在出发，大约XX分钟到达，请做好接收准备，请您的手机保持畅通以便联系，再见。”）</div>
						<div class="img"><img src="/static/images/ks_03_01.jpg" style="width: 80%;"/></div>
						<div class="img"><img src="/static/images/ks_03_02.jpg" style="width: 80%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width:100%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/errand','view'=>'errand_02']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/index','status'=>'errand']); ?>"  class="button button-big button-fill external">完成学习</a>
							</div>

						</div>
					</div>

				</div>

			</div>

		</div>
	</div>

</block>

