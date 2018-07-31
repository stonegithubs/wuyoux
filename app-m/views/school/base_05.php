<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 15:02
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

						<div class="title"><i class="iconfont icon-xuhao401"></i> 小帮资料</div>
					    <div class="img"><img src="/static/school/img_banner3.png" style="width:100%"></div>
						<div class="" align="center" style="margin: 15px;color:#666">进入小帮设置</div>
							<div class="img"><img src="/static/school/banner_b.png" style="width:100%"></div>
						<div class="sub_title" style="color: #666; font-size: 1em;">
						    <p>1、在“小帮资料”中可以更改接单号码</p>
						    <p>2、在“小帮设置”选择“接单类型”可以设置接单范围（注册后是默认2公里接单范围）这样可以可以自主设置公里数</p>
						</div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_04']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_06']); ?>"  class="button button-big button-fill">下一页</a>
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
