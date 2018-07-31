<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/2/9
 * Time: 9:47
 */
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title>您已是企业送用户</title>
	<link href="/static/bizapply/css/style.css" rel="stylesheet">
	<style>
		.container{ width: 75%; margin:30% auto 0 auto}
		.title{ margin: 30px auto;}
		.button{ border: 1px solid #FF6000; color:#ff6000; margin: 40% auto 0 auto !important; width: 65%;}
	</style>
</head>

<body>
<div class="container text-center">
	<p class="img">
		<img src="/static/bizapply/img/enterprise_submit.png" width="100%"/>
	</p>

	<p class="title txt_333 txt_666 f18">
		您已是企业送用户
	</p>
	<button onclick="backUser()" class="button button-big button-round mt15">返回用户首页</button>
</div>

<script>
    function backUser() {
        bizSendApply.backUserHome(); //js call java 跳转到用户首页
    }
</script>
</body>

</html>
