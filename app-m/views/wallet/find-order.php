<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/7/18
 */
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>用户账单明细</title>
    <link rel="stylesheet" href="/static/css/framework7.ios.min.css">
    <link rel="stylesheet" href="/static/css/framework7.ios.colors.min.css">
    <link rel="stylesheet" href="/static/css/my-app.css">
</head>
<body>
<block name="style">
    <style>
        tr td {
            width: 50%;
            color: #999
        }

        tr td:last-child {
            text-align: right;
        }

        body {
            height: auto;
            background: #f1f1f1
        }

        .content {
            color: #333
        }
    </style>
</block>
<block name="body">
    <div class="statusbar-overlay"></div>
    <!-- Views-->
    <div class="views" id="trade_detail">
        <!-- Your main view, should have "view-main" class-->
        <div class="view view-main">

            <div class="pages">
                <!--<div class="page no-navbar">-->
                <div class="no-navbar">
                    <div class="page-content">
                        <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0"
                               class="p10 bg-white">
                            <tbody id="data_list">

                            <tr>
                                <td colspan="2" class="txt_center"><i class="<?= $income['icon_style'] ?> f32"></i><span
                                            style="padding-left: 10px;top: -5px;position: relative;"
                                            class="txt_666"><?= $income['title'] ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="txt_center f32 txt_333"><?= $income['money'] ?></td>
                            </tr>

							<?php foreach ($order as $key => $value): ?>
                                <tr>
                                    <td>订单号</td>
                                    <td class="content"><?php echo $value['order_no'] ?></td>
                                </tr>
                                <tr>
                                    <td>下单时间</td>
                                    <td class="content"><?php echo $value['order_time'] ?></td>
                                </tr>
                                <tr>
                                    <td>订单金额</td>
                                    <td class="content">￥<?php echo $value['order_amount'] ?></td>
                                </tr>
								<?php
								if ($value['discount'] != '0.00'):?>
                                    <tr>
                                        <td>订单优惠</td>
                                        <td class="content">-￥<?php echo $value['discount'] ?></td>
                                    </tr>
								<?php endif; ?>
                                <tr>
                                    <td>实付金额</td>
                                    <td class="content">￥<?php echo $value['amount_payable'] ?></td>
                                </tr>
                                <tr>
                                    <td>订单类型</td>
                                    <td class="content"><?php echo $value['order_name'] ?></td>
                                </tr>
								<?php
								if ($key != count($order) - 1):?>
                                    <tr>
                                        <td colspan="2" style="text-align: center;">
                                            <hr style="height:1px;border:none;border-top:1px dashed #C9C9C9;">
                                        </td>
                                    </tr>
								<?php endif; ?>
							<?php endforeach; ?>

                            </tbody>
                        </table>
                        <div class="txt_center p20 txt_blue f13 "><a
                                    href="http://51bangbang.udesk.cn/im_client/?web_plugin_id=43064"
                                    class="item-link item-content external">对此订单有疑问</a></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</block>
<block name="script">
</block>
</body>
</html>