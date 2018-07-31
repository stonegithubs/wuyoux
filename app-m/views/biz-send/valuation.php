<?php

use yii\helpers\Url;

$this->title = '计价明细';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>计价明细</title>
    <style>body, table, td {
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: #333;
            font-weight: normal;
        }

        .f24 {
            font-size: 24px;
        }

        .pt10 {
            padding: 20px 0;
        }

        .txt_orange {
            color: #ff6000
        }

        .tip {
            padding: 25% 0 5% 0;
        }

        table {
            width: 80%;
            margin: 10% auto 0 auto;
        }

        table td {
            line-height: 28px;
        }

        a {
            text-decoration: none;
            color: #000000;
        }

        .font_grey {
            color: #999;
            line-height: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td colspan="2" class="pt10"><span class="f24">合计：<?= $total_amount ?></span>元</td>
    </tr>
    <tr>
    <tr>
        <td width="50%">服务费(<?= $order_distance ?>)</td>
        <td align="right"><?= $service_amount ?>元</td>
    </tr>
	<?php
	if ($payment_status == 2) {
		echo "<tr><td width='50%' class='txt_orange'>卡券抵扣</td><td align='right' class='txt_orange'>-$card_discount 元</td></tr>" .
			"<tr><td width='50%' class='txt_orange'>$payment_type</td><td align='right' class='txt_orange'>$total_amount 元</td><tr>";
	}
	?>
    <tr>
        <td colspan="2" align="center" class="tip"><a
                    href="<?= Url::to(["biz-send/valuation-rule?order_no=$order_no"]); ?>">查看计价规则 ></a></td>
    </tr>

    <tr>
        <td height="45" colspan="2" align="center" class="font_grey">若对费用任何疑问，请您立即联系客服，我们会跟进你得反馈</td>
    </tr>

    </tbody>
</table>
</body>
</html>