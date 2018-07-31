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

						<div class="title"><i class="iconfont icon-xuhao501"></i> 缴纳保证金</div>
						    <div class="img"> 	<img src="/static/school/banner_1.png" style="width:100%"></div>
									<div class="sub_title" style="color: #666; font-size: 1em;line-height:2">
                        						  进入个人中心的“保证金”—选择“缴纳”，保证金需缴纳<b style="color:#ff6600">200元</b>。保证金的钱只是暂时保管在本平台，缴纳保证金后满15个工作日，即可解冻保证金，钱会自动返回到绑定的账户上（支付宝/银行卡）。
                        						</div>
						<div class="img">
							<img src="/static/school/img_banner4.png" />
						</div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_05']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_07']); ?>"  class="button button-big button-fill">下一页</a>
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
