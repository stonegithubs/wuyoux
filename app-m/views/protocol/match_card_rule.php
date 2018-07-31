<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/3/20
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>优惠券匹配规则</title>
	<style>
		body{ padding: 20px; margin: 0;}
		h1{font-size:15px;margin-top: 1.6em;color: #666666; text-align:center;}
		h2{font-size:14px;margin-top: 1.6em;color: #666666;}
		p{font-size: 14px;line-height: 24px;color: #999999; text-indent:2em}
	</style>
</head>
<body >
<h2>一、定义</h2>
<p>1、如单张订单总价等于优惠券价格，系统优先匹配该类订单优惠券。</p>
<p>2、如单张订单总价大于优惠券价格，系统第二步匹配该类订单优惠券。</p>
<p>3、如单张订单总价小于优惠券价格，系统不做匹配，需用户手动选择该优惠券。</p>
<p>4、如手动配置单张订单总价小于优惠券价格的情况，优惠券差价不做退还。</p>
<p>5、优惠券匹配最终解释权归无忧帮帮所有。</p>
</body>
</html>
