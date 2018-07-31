<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 15:07
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

						<div class="title"><i class="iconfont icon-xuhao501"></i> 在线宝使用规则</div>
						<div class="img"><img src="/static/images/base_05.jpg" /></div>
						<div class="sub_title" style="font-size: 14px; margin-bottom: 5px;">A．什么是在线宝</div>
						<div class="con">在线宝是手机App无忧帮帮里面的在线收益功能，只要App在线，每分钟小帮可获得0.01元收益。</div>
						<div class="sub_title" style="font-size: 14px; margin-bottom: 5px;">B．在线宝的使用方式</div>
						<div class="con">在线宝提现首先要有订单，然后根据每个订单用户扣取的在线宝金额对等提现。比如说商家完成了一个摩的专车100元的订单：100×10%=10元，而这10元就是该商家在线宝能提现的金额。</div>
						<div class="tip">注：在线宝提现金额在15天后自动转入余额账户</div>


					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 83.5%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','base_id'=>'05']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','base_id'=>'07']); ?>"  class="button button-big button-fill">下一页</a>
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