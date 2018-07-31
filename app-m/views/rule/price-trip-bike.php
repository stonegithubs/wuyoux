<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/3/17
 * Time: 9:33
 */
$this->title = '计价规则';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>计价规则</title>
    <style>
        body, table {
            font-size: 13px;
            margin: 25px 0 0 0;
            padding: 0;
            color: #666;
            font-family: Helvetica, Tahoma, Arial, "Hiragino Sans GB", "Hiragino Sans GB W3", "Microsoft YaHei", STXihei, STHeiti, Heiti, SimSun, sans-serif;
            font-weight: normal;
        }

        h2 {
            font-size: 16px;
            font-weight: normal;
            line-height: 32px;
            padding-top: 20px;
            color: #333;
        }

        .txt_999 {
            color: #999
        }

        a {
            color: #ff6000
        }

        table {
            width: 85%;
            margin: auto;
        }

        table td {
            line-height: 28px;
        }

        .lh20 {
            line-height: 20px;
        }

        p {
            line-height: 18px;
            margin: 5px 0;
        }

        .f12 {
            font-size: 12px;
        }

        .f20 {
            font-size: 20px;
        }
    </style>
</head>

<body>
<table border="0" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td height="45" class="f20">计价规则</td>
        <td align="right"><img src="/static/rule/img/12.png" width="8"/> <?= $city['region_name'] ?></td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <p><img src="/static/rule/img/11.png" width="30%"/></p>
            <p class="f12">小帮出行</p>
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" class="txt_999" align="center" height="40">––––&nbsp;&nbsp;&nbsp;&nbsp;白天（<?= $cityPrice['day_time'] ?>-<?= $cityPrice['night_time'] ?>）&nbsp;&nbsp;&nbsp;&nbsp;––––
        </td>
    </tr>
    <tr>
        <td width="50%">起步价（<?= $cityPrice['day_range_init'] ?>公里）</td>
        <td align="right"><?= $cityPrice['day_range_init_price'] ?>元</td>
    </tr>
    <tr>
        <td>里程费（超出<?= $cityPrice['day_range_init'] ?>公里）</td>
        <td align="right"><?= $cityPrice['day_range_unit_price'] ?>元/公里</td>
    </tr>

	<?php
	if ($cityPrice['day_service_fee'] > 0) { ?>

        <tr>
            <td>白天服务费</td>
            <td align="right"><?= $cityPrice['day_service_fee'] ?>元</td>
        </tr>

	<?php } ?>

    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" class="txt_999" align="center" height="40">––––&nbsp;&nbsp;&nbsp;&nbsp;夜间（<?= $cityPrice['night_time'] ?>-<?= $cityPrice['day_time'] ?>）&nbsp;&nbsp;&nbsp;&nbsp;––––
        </td>
    </tr>
    <tr>
        <td width="50%">起步价（<?= $cityPrice['night_range_init'] ?>公里）</td>
        <td align="right"><?= $cityPrice['night_range_init_price'] ?>元</td>
    </tr>
    <tr>
        <td>里程费（超出<?= $cityPrice['night_range_init'] ?>公里）</td>
        <td align="right"><?= $cityPrice['night_range_unit_price'] ?>元/公里</td>
    </tr>
	<?php
	if ($cityPrice['night_service_fee'] > 0) { ?>

        <tr>
            <td>夜间服务费</td>
            <td align="right"><?= $cityPrice['night_service_fee'] ?>元</td>
        </tr>

	<?php } ?>
    <tr>
        <td colspan="2" height="40">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" class="f12 txt_999 lh20">
            <p>注：</p>
            <p>1、计费是根据行程起终点距离计算。</p>
            <p>2、一切费用以平台为准，如果发现计费计算错误问题，可以联系我们的客服人员进行处理。</p>
        </td>
    </tr>
    </tbody>
</table>

</body>
</html>

