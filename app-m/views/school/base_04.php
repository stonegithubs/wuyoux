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
					<div class="title"><i class="iconfont icon-xuhao301"></i> 小帮入驻</div>
	                    <h3 class="title" style="font-size: 1em;">1、申请入驻小帮出行</h3>
	                    <div class="img"><img src="/static/school/img_banner1.png" style="width: 100%;"></div>
						<div class="sub_title" style="font-size: 1em;line-height:1.5;color:#666" align="left">点击首页左上角个人中心—点击“入驻小帮”—选择交通出行“小帮出行”—输入个人资料及三证（<b style="color:#ff6600">行驶证、驾驶证及身份证</b>）拍照上传—提交并等待客服审核—审核通过后即可点击“上班”接单</div>

						     <h3 class="title" style="font-size: 1em;">2、申请入驻小帮快送</h3>
						     <div class="img">  <img src="/static/school/img_banner2.png" style="width: 100%;"></div>
                        						<div class="sub_title" style="font-size: 1em;line-height:1.5;color:#666" align="left">点击首页左上角个人中心—点击“入驻小帮”—选择“小帮快送”—输入个人资料，<b style="color:#ff6600">拍照上传身份证和手持身份证照片</b>—提交并等待客服审核—审核通过后即可点击“上班”接单</div>


					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_03']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_05']); ?>"  class="button button-big button-fill">下一页</a>
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
