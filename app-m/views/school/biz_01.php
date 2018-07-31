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

						<div class="title"><i class="iconfont icon-xuhao101"></i> 抢单</div>
						<div class="sub_title">1、订单是以弹屏10秒的形式出现。小帮需要分辨清楚订单类型，企业送的订单有“小帮快送-企业送”订单名称标注。小帮可以根据弹屏的内容选择不抢或者查看。如果选择查看，会跳转订单海，小帮再完成抢单。</div>
						<div class="con">注：企业单在弹屏中不可以直接抢单，必须是选在查看，跳转订单海来抢单。</div>
									<div class="img"><img src="/static/school/img_banner7.png" style="width: 100%;"/></div>

						<div class="sub_title">2、小帮上班后可以直接进入订单海，查看企业送的订单，并且完成抢单。</div>
						<div class="img"><img src="/static/school/img_banner10.png" style="width: 100%;"/></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="btn">
								<a href="<?= Url::to(['school/biz','view'=>'biz_02']); ?>" style="width: 100%;" class="button button-big button-fill">下一页</a>
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