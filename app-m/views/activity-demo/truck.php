<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/3/1
 * Time: 9:53
 */
$this->title = '小帮货车';
?>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>小帮货车</title>
	<script type="text/javascript" src="/static/js/jquery.min.js"></script>
	<link rel="stylesheet" href="/static/css/framework7.ios.css">
	<style>body, table, td {
			font-size: 13px;
			margin: 0;
			padding: 0;
			color: #333;
			font-weight: normal;
		}

		p {
			color: #999;
			font-size: 16px;
			text-align: center;
			margin: 6px;
		}

		.txt_orange {
			color: #ff6000;
			font-size: 24px;
		}

		img {
			width: 100%;
			position: relative;
			bottom: -5px;
		}

		.box {
			position: absolute;
			bottom: 0;
		}

		#ok {
			display: none;
		}
	</style>

</head>
<body>
<div class="box">
	<div id="default">
		<p>功能正在开发</p>
		<p style="margin-bottom:20px">为我们加油能开发得更快哦！</p>
	</div>
	<img  src="/static/truck/img/truck_img_front_twenty.jpg" id="img" onclick="comeOn()">
</div>
<script type="text/javascript" src="/static/js/framework7.min.js"></script>
	<script>
	var myApp = new Framework7({
            modalTitle: "",
            modalButtonOk: "确定",
        });

            var $$ = Dom7;
        function comeOn() {
            myApp.alert('很抱歉您未登录，请先登录');
        }
	</script>
</body>
</html>