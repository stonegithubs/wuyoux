<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/3/30
 */
$this->title = $title;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <style>
        h1 {
            font-size: 16px;
            margin-top: 1.5em;
            color: #666666;
        }

        p {
            font-size: 14px;
            line-height: 20px;
            padding: 0;
            color: #999999;
        }

        img {
            width: 100%;
            height: auto;
        }
    </style>

</head>
<body style="padding:10px 15px;">
<div class="">
	<?= $content ?>
</div>
</body>
</html>

