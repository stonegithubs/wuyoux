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

					<div class="title"><i class="iconfont icon-xuhao301"></i> 到达店铺</div>
					<div class="sub_title" style="color:#666">1、到达店铺后，需要清点配送的物品。注意小帮尽量是配送同一个方向的物品。如果出现方向相差太远的订单，提醒商家再次下单，呼叫其他小帮过来配送。</div>
					<div class="sub_title" style="color:#666">2、清点完需要配送的物品后，滑动开始配送，输入配送订单的收货人的手机号码。确定完全输入后开始配送。</div>
					<div class="img"><img src="/static/school/img_banner12.png" style="width: 100%;"/></div>

				</div>
				<div class="toolbar tabbar">
					<div class="toolbar-inner">
						<div class="line"><i style="width:100%"></i></div>
						<div class="p_btn">
							<a href="<?= Url::to(['school/biz','view'=>'biz_02']); ?>"  class="back button button-big button-fill">上一页</a>
							<a href="<?= Url::to(['school/biz','view'=>'biz_04']); ?>"  class="button button-big button-fill">下一页</a>
						</div>

					</div>
				</div>

			</div>

		</div>

	</div>
</div>

</block>

