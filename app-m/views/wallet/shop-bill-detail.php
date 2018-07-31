<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/16
 * Time: 11:41
 */

$this->title = '小帮账单明细';
?>

<extend name="Base/Base"/>
<block name="style">
	<style>
		tr td{ width:50%; color:#999}
		tr td:last-child {
			text-align: right;
		}
	</style>
</block>
<block name="body">
	<!-- Views-->
	<div class="views" id="trade_detail">
		<!-- Your main view, should have "view-main" class-->
		<div class="view view-main">
			<div class="pages order_navbar">
				<!--<div class="page">-->
				<div >
					<div class="page-content">
						<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="p10 bg-white">
							<tbody>
							<?= $data ?>
							</tbody>
						</table>
						<?= $extend ?>
					</div>

				</div>
			</div>

		</div>
	</div>

</block>

<block name="script"></block>
