<?php

use yii\helpers\Url;

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>企业送</title>
    <link href="/static/bizapply/css/style.css" rel="stylesheet">
    <style>
        .about img{ margin-bottom: 70px;}
        .footerbtn{position: fixed; bottom: 0; width: 94%; padding: 15px 3%;background: #fff; }
    </style>
</head>

<body>
<div class="container">
    <div class="about">
        <img src="/static/bizapply/img/enterprise_bg.jpg" width="100%"/>
        <div class="footerbtn">
            <a href="<?= Url::to(['biz-apply/apply']); ?>" class="button button-round button-big button-fill bg_orange">申请企业用户</a>
        </div>
    </div>
</div>
</body>

</html>