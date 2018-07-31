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

						<div class="title"><i class="iconfont icon-xuhao601"></i> 订单类型</div>
						<div class="sub_title" style="font-size: 1em;line-height:2">根据平台现有的服务类型，订单分为2种</div>
						<div class="tab"><span>小帮出行订单</span></div>
						<div class="img"><img src="/static/images/base_03_01.jpg" /></div>
						<div class="con" style="font-size: 1em;line-height:2">小帮出行采用LBS定位系统，迅速准确。用户只需点击小帮出行功能，输入目的地即可生成订单。</div>
						<div class="tab"><span>小帮快送订单</span></div>
						<div class="img"><img src="/static/images/base_03_02.jpg" /></div>
						<div class="con" style="font-size: 1em;line-height:2">小帮快送订单包含“帮我买”、“帮我送”、“帮我办”、“企业送”。“帮我买”的订单内容是用户通过平台下单，指定帮忙购买的商品；“帮我送”的订单内容是帮用户将物品配送到目的地；“帮我办”的订单内容是代办用户所需的服务；“企业送”的订单内容是针对商家用户群，将物品配送到目的地。
</div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 50%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_06']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_08']); ?>"  class="button button-big button-fill">下一页</a>
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