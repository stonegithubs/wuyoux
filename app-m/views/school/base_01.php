<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 14:02
 */

use m\assets\AppAsset;
use yii\helpers\Url;

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

						<div class="title txt_center" style="margin: 10px 0; font-size: 18px;">详情介绍</div>
						<div class="sub_title" style="text-indent:2em;color: #666;font-size:1em;line-height:1.5;">       新手入门基础培训包括产品介绍，产品功能知识，安全驾驶常识，平台接单流程操作等基础知识的介绍。小帮需完成所有培训方可接单。本培训大概需要2分钟 时间。</div>

					</div>

					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 0"></i></div>
							<div class="btn">
								<a href="<?= Url::to(['school/base','view'=>'base_02']); ?>" style="width: 100%;" class="button button-big button-fill">
									<? if ($base_status == 1) { ?>
                                                                               复习培训
                                											<? } else { ?>
                                                                               开始学习
                                											<? } ?>

								</a>
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
