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

<!-- Views-->
<div class="views" id="school">
	<!-- Your main view, should have "view-main" class-->
	<div class="view view-main">
		<div class="pages">

			<div data-page="index" class="page school_con">
				<div class="page-content">

					<div class="title"><i class="iconfont icon-xuhao401"></i> 配送到达</div>
					<div class="sub_title" style="color:#666">1、当把物品安全交给收货人之后，小帮需要根据已经配送的收货人的手机号码，进入对应的订单详情内，滑动确认配送位置。</div>
					<div class="img"><img src="/static/school/img_banner13.png" style="width: 100%;"/></div>
					<div class="sub_title" style="color:#666">2、如果订单配送位置有偏移，必须手动修改当前位置。如果确认位置无误后，则滑动配送到达，完成交易。</div>
					<div class="img"><img src="/static/school/img_banner14.png" style="width: 100%;"/></div>

				</div>
				<div class="toolbar tabbar">
					<div class="toolbar-inner">
						<div class="line"><i style="width:100%"></i></div>
						<div class="p_btn">
							<a href="<?= Url::to(['school/biz','view'=>'biz_03']); ?>"  class="back button button-big button-fill">上一页</a>
							<a href="<?= Url::to(['school/index','status'=>'biz']); ?>"  class="button button-big button-fill external">完成学习</a>
						</div>

					</div>
				</div>

			</div>

		</div>

	</div>
</div>

</block>
