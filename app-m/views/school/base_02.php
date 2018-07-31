<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 15:00
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

						<div class="title"><i class="iconfont icon-xuhao101"></i> 关于无忧帮帮</div>
						<div class="img"><img src="/static/images/base_01.jpg" /></div>
						<div class="sub_title" style="color: #666; font-size: 1em;line-height:1.5">“无忧帮帮“隶属于：深圳无忧帮帮科技有限公司，是一款基于LBS定位，近距离快速的为消费者提供打摩的、高效配送等个性化、全方位即时服务的APP，同时也为广大商家提供精准的客户。平台专注于用移动和众包的方式解决用户生活需求，用户发出需求，小帮可以根据自己的时间通过无忧帮帮App自由选择订单并完成服务，得到相应报酬。</div>
					</div>

					<div class="toolbar tabbar">
						<div class="toolbar tabbar">
							<div class="toolbar-inner">
								<div class="line"><i style="width: 16.6%"></i></div>
								<div class="btn">
									<a href="<?= Url::to(['school/base','view'=>'base_03']); ?>" style="width: 100%;" class="button button-big button-fill">下一页</a>
								</div>
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