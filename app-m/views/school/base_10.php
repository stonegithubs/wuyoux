<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 15:05
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

						<div class="title"><i class="iconfont icon-xuhao901"></i> 提现方式</div>
						<div class="sub_title" style="font-size: 1em;line-height:2;color:#666">小帮提现必须绑定银行卡账号或支付宝账号，提现时间为每周二及每周五</div>
						<div class="tip">注：提现金额需大于10元。</div>
						<div class="img"><img src="/static/images/base_04.jpg" /></div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 66.6%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_09']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_11']); ?>"  class="button button-big button-fill">下一页</a>
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
