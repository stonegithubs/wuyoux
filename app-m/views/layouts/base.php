<?php
    /* @var $this \yii\web\View */
    /* @var $content string */
    use \yii\helpers\Html;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

	<?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>

    <?= Html::cssFile("/static/css/framework7.ios.min.css") ?>
    <?= Html::cssFile("/static/css/framework7.ios.colors.min.css") ?>
    <?= Html::cssFile("/static/css/my-app.css") ?>
    <?= Html::jsFile("/static/js/jquery.min.js") ?>
	<?= Html::cssFile("/css/base.css") ?><!--公共CSS函数-->
	<?= Html::jsFile("/static/unit/js/base.js") ?><!--公共JS函数-->
    <!--    //百度数据统计-->
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?8661b69998378f6c1f6254ea4e28d3f7";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
	<?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
<!-- 内容区 -->
<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
