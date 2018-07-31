<?php

use yii\helpers\Url;

$this->title = '计价规则';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>计价规则</title>
    <style>body, table, td {
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: #333;
            font-weight: normal;
        }
        .f24{ font-size: 24px; }
        .pt10{ padding:20px 0 ; }
        table {
            width: 80%;
            margin: 10% auto 0 auto;
        }

        table td {
            line-height: 28px;
        }

    </style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td colspan="2" class="pt10"><span class="f24">计价规则</span></td>
    </tr>
    <tr>
    <tr>
        <td width="50%"><?= $init_range ?>公里内</td>
        <td align="right"><?= $init_price ?>元</td>
    </tr>
    <tr>
        <td width="50%">超出<?= $init_range ?>公里</td>
        <td align="right">每公里加<?= $unit_price ?>元</td>
    </tr>
    <tr>
        <td width="50%">夜晚<?= $day_night ?></td>
        <td align="right">夜晚服务费<?= $service_fee ?>元</td>
    </tr>

    </tbody>
</table>
</body>
</html>