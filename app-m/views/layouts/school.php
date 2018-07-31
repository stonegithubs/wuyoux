<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport"
		  content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<title>小帮学堂</title>
	<link rel="stylesheet" href="/static/css/framework7.ios.min.css">
	<link rel="stylesheet" href="/static/css/framework7.ios.colors.min.css">
	<link rel="stylesheet" href="/static/css/my-app.css">
	<script type="text/javascript" src="/static/js/jquery.min.js"></script>
	<script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?8661b69998378f6c1f6254ea4e28d3f7";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
	</script>
	<block name="style"></block>
	<block name="headScript"></block>

	<?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>

<block name="style"></block>
<!-- 内容区 -->
<?= $content ?>
<?php $this->endBody() ?>

<script type="text/javascript" src="/static/js/framework7.min.js"></script>
<script type="text/javascript" src="/static/js/my-app.js"></script>

</body>
</html>
<?php $this->endPage() ?>
