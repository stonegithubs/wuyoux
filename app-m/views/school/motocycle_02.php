<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 16:10
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

						<div class="title"><i class="iconfont icon-xuhao201"></i> 出发前</div>
						<div class="sub_title">1、确认客户信息。</div>
						<div class="con" style="color:#999">（参考话术：“您好，是X先生/小姐吗？”）</div>
						<div class="sub_title">2、提醒客户注意安全。</div>
						<div class="con" style="color:#999">（参考话术：“您好，请带好头盔。”）</div>
						<div class="img"><img src="/static/images/md_02.jpg" style="width: 60%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 66.6%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/motocycle','view'=>'motocycle_01']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/motocycle','view'=>'motocycle_03']); ?>"  class="button button-big button-fill">下一页</a>
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
