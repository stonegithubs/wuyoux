<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2017/12/20
 * Time: 15:09
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

						<div class="title">
						<img src="/static/school/xv_10.png" style="width: 18px;"</img>
						 安全驾驶常识
						 </div>
						<div class="img"><img src="/static/images/base_06.jpg" /></div>
						<div class="tab" style="margin-bottom: 10px;"><span>驾车前</span></div>
						<div class="con" style="font-size: 1em;line-height:2">
							①驾驶摩托前一定要戴好安全头盔，穿显眼的紧身衣服，便于操纵和增加汽车驾驶员的注意；<br /> ②身体不适时不要驾驶摩托车；
							<br /> ③吃药后不要驾驶摩托车；
							<br /> ④严禁酒后驾驶和无证驾驶；
							<br /> ⑤仔细查看车况，不骑带病车。
						</div>
						<div class="tab" style="margin-bottom: 10px;"><span>驾车中</span></div>
						<div class="con" style="font-size: 1em;line-height:2">
							①保持良好心情和心理素质，集中精力驾车；<br /> ②不开呕气车和“好汉”车；
							<br /> ③尽可能保持匀速、靠右行驶；
							<br /> ④减少急加速和突然停车，预防突发事件；
							<br /> ⑤遇交叉路口一定要换挡减速慢行，确保安全后通过；
							<br /> ⑥遇弯路时一定要减速慢行，防止侧滑（此时禁止使用前刹车，否则车辆容易失控飞出）； <br /> ⑦超车时一定要开转向灯，确保安全下超车，不要紧贴被超越车辆；
							<br /> ⑧雨雪天气时，地面摩擦阻力小，制动距离相对拖长，一定要减速慢行，制动操作要柔和，避免抱死摔车；
							<br /> ⑨夜晚行车因可视距离短，一定要减速慢行，并打开夜间行车灯，引起行人和车辆的注意；
							<br /> ⑩行车中感觉摩托车有异常时，一定要停车检查。
						</div>
						<div class="tab" style="margin-bottom: 10px;"><span>停车后</span></div>
						<div class="con" style="font-size: 1em;line-height:2">
							①要检查灯光、电器有无异常；<br /> ②发动机等有无渗油或异常声音；
							<br /> ③关闭电路，锁好车；
							<br /> ④关闭油箱开关；
							<br /> ⑤停稳车辆，最好用中心支撑停车，减少轮胎负荷，延长轮胎寿命；
							<br /> ⑥远离火源，不要靠近摩托车点火吸烟。
						</div>

					</div>
					<div class="toolbar tabbar">
						<div class="toolbar-inner">
							<div class="line"><i style="width: 100%"></i></div>
							<div class="p_btn">
								<a href="<?= Url::to(['school/base','view'=>'base_10']); ?>" class="back button button-big button-fill">上一页</a>
								<a href="<?= Url::to(['school/index','status'=>'base']); ?>" class="button button-big button-fill external">完成学习</a>
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