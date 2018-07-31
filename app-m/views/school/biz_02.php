<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/21
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

						<div class="title"><i class="iconfont icon-xuhao201"></i> 抢到订单后</div>
						<div class="sub_title">1、抢单成功后，小帮需要联系下单客户，确认订单信息（商家地址、配送物品等），核实无误再前往商家地点。</div>
						<div class="con" style="color:#999">注：企业单在弹屏中不可以直接抢单，必须是悬在查看，跳转订单海来抢单。</div>
												<div class="sub_title">2、如果订单无误，请告知客户预计到达时间</div>
																		<div class="con" style="color:#999">（参考话术：“请您稍等片刻，我现在出发，大约XX分钟到达。”）</div>

						<div class="img"><img src="/static/school/img_banner11.png" style="width: 100%;"/></div>
					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 66.6%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/biz','view'=>'biz_01']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/biz','view'=>'biz_03']); ?>"  class="button button-big button-fill">下一页</a>
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