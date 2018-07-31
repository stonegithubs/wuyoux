<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/15
 * Time: 11:27
 */

$this->title = '用户账单明细';
?>



<block name="style">
	<style>
		tr td{ width:50%; color:#999}
		tr td:last-child {
			text-align: right;
		}
        .content {
            color: #333
        }
	</style>
</block>
<block name="body">
	<div class="statusbar-overlay"></div>
	<!-- Views-->
	<div class="views" id="trade_detail">
		<!-- Your main view, should have "view-main" class-->
		<div class="view view-main">

			<div class="pages">
                <!--<div class="page no-navbar">-->
                <div class="no-navbar">
					<div class="page-content">
						<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="p10 bg-white">
							<tbody id = "data_list">
							<?php
								if($code == 20000){
									echo "<script>no_message('账号异常');</script>";
								}else{
									echo $data;
								}
							?>
							</tbody>
						</table>
						<?php
							if($code != 20000){
								echo $extend;
							}
						?>
					</div>
				</div>
			</div>

		</div>
	</div>
</block>

<block name="script">

</block>