<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/17
 * Time: 12:01
 */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>成为企业送用户</title>
    <link href="/static/bizapply/css/style.css" rel="stylesheet">
    <style>
        .container{ width: 75%; margin:30% auto 0 auto}
        .title{ margin: 30px auto;}
        .button{ border: 1px solid #FF6000; color:#ff6000; margin: 40% auto 0 auto !important; width: 65%;}
    </style>
</head>

<body class="index_bg">
<div class="container text-center">
    <p class="img">
        <img src="/static/bizapply/img/enterprise_fail.png" width="45%"/>
    </p>
    <p class="title txt_333 txt_666">
        抱歉您提交的申请信息有误哦！<br/>请修改店铺资料重新提交
    </p>
    <a href="<?= $apply_url ?>" class="button button-big button-round mt15">重新填写</a>
</div>

</body>

</html>
