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

						<div class="title"><i class="iconfont icon-xuhao201"></i> 小帮的基本要求</div>
						<div class="img">
							<img src="/static/images/base_02_01.jpg" />
							<img src="/static/images/base_02_02.jpg" />
							<img src="/static/images/base_02_03.jpg" />
							<img src="/static/images/base_02_04.jpg" />
						</div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 33.3%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_02']); ?>"  class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/base','view'=>'base_04']); ?>"  class="button button-big button-fill">下一页</a>
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
