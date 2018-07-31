<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 16:41
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

						<div class="title"><i class="iconfont icon-xuhao201"></i> 到达购买（取货）地点后</div>
						<div class="tab"><span>“帮我买”订单</span></div>
						<div class="sub_title">在购买商品之前，需电话联系用户确定商品的价格等再购买。</div>
						<div class="con">（参考话术：您好，我是无忧帮帮小帮快送XXX师傅，你需购买的XX商品价格是XX元，这个价格有没有问题？）</div>
						<div class="img"><img src="/static/images/ks_02_01.jpg" style="width: 80%;"/></div>
						<div class="tab"><span>“帮我送”订单</span></div>
						<div class="sub_title">取货前（去到指定地点，确认物品信息）请再次确认订单信息（收货人地址、电话、配送要求等）</div>
						<div class="img"><img src="/static/images/ks_02_02.jpg" style="width: 80%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 66.6%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/errand','view'=>'errand_01']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/errand','view'=>'errand_03']); ?>"  class="button button-big button-fill">下一页</a>
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