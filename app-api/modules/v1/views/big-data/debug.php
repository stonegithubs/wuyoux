<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/16
 */


$big = [
	['title' => '商家总数','url'=>'shops-total'],
	['title' => '今日商家数','url'=>'today-shops-total'],
	['title' => '商家总数','url'=>'shops-total'],
	['title' => '商家总数','url'=>'shops-total'],
	['title' => '商家总数','url'=>'shops-total'],

];

foreach($big as $row){
	?>


<?=$row['title'] ?>  <a href='<?=$row['url']?> ' ><?=$row['title'] ?> </a> url :<?Yii::$app->urlManager->createUrl(['big-data/'.$row['url']]);?> <br>

<?php }
?>


