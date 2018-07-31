<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 16:19
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

						<div class="title"><i class="iconfont icon-xuhao301"></i> 到达目的地</div>
						<div class="sub_title">1、送客户到达目的地。</div>
						<div class="con"style="color:#999">（参考话术：“您好，***到了。”）</div>
						<div class="sub_title">2、完成订单之后，请用户支付车费。</div>
						<div class="con" style="color:#999">（参考话术：“您好，麻烦点一下支付按钮支付本次车费，谢谢。”）</div>
						<div class="sub_title">3、让客户对你的服务评分。</div>
						<div class="con" style="color:#999">（参考话术：“您好，请对我本次服务进行评分，如果满意请给五星好评，谢谢。）</div>
						<div class="img"><img src="/static/images/md_03.jpg" style="width: 80%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width:100%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/motocycle','view'=>'motocycle_02']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/index','status'=>'trip']); ?>"  class="button button-big button-fill external" >完成学习</a>
							</div>

						</div>
					</div>

				</div>

			</div>

		</div>
	</div>

</block>

