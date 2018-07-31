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

						<div class="title"><i class="iconfont icon-xuhao801"></i> 取消订单</div>
						<div class="sub_title" style="font-size: 1em; line-height:2;color:#666">
						在进行中的订单中，点击更多，选择取消。只要用户同意了， 则可以取消订单</div>
						<div class="tip">注：小帮取消订单是要经过用户同意，建议取消订单前要先和用户沟通好</div>
						<div class="img"><img src="/static/school/img_banner8.png" ></div>
					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 50%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_08']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_10']); ?>"  class="button button-big button-fill">下一页</a>
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