<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */


$this->title = '404 error';
?>

<div class="error" style="text-align:center">
    <p class="img">
        <img src="/static/lottery/img/error.png" width="70%"/>
    </p>
    <p class="title">404<br/>暂时找不到回家的路</p>

    <div class="logo" style="text-align:center"><img src="/static/lottery/img/userbar_logo.png" width="40%"/></div>
</div>

<style scoped>
    .error {
        width: 50%;
        margin: 15% auto 0 auto;
    }

    .img {
        margin-top: 20px;
    }

    .title {
        margin: 30px auto;
        line-height: 28px
    }

    .logo {
        position: fixed;
        bottom: 30px;
        left: 0%
    }
</style>