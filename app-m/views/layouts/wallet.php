<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport"
		  content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<title><?= $this->title ?></title>
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
	<script>
        //显示暂无信息
        function no_message(message)
        {
            var str = '<style>body{background: #f1f1f1;}</style></style><div><div class = "logo1" style="text-align: center;" > <img style="width: 60%;margin-top: 50px;" src = "/static/images/no_order.png"/> </div > <p class="txt_center">'+message+'</p></div>';
            $('body').html(str);
            return false;
        }
        function showInLoading() {
            $('#loading').html('<div class="infinite-scroll-preloader" style="display: block; margin:4px; text-align: center"><div class="preloader"></div></div>');//菊花现
            return true;
        }
        function unshowInLoading() {
            $('#loading').html('');//菊花现
            return true;
        }
        function showInBottleLoading() {
            $('#loading').html('<div class="infinite-scroll-preloader" style="display: block; margin:4px; text-align: center"><div style="color: #999999">别拉了,到底啦</div></div>');//菊花现
            return true;
        }
        //获取URL中的参数
        function getQueryString(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
            var r = window.location.search.substr(1).match(reg);
            if (r != null) return unescape(r[2]); return null;
        }
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

</body>
</html>
<?php $this->endPage() ?>
