<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/12/10 12:40
 */

namespace console\controllers;

use common\helpers\payment\CityPriceHelper;
use common\helpers\utils\RegionHelper;

use yii\console\Controller;
use Yii;

class PriceController extends Controller
{
	/**
	 * 检查城市费率
	 */
	public function actionCheckCityPrice()
	{

		$normal_tmp = CityPriceHelper::updateNormalCityPrice();    //常规
		$start_tmp  = CityPriceHelper::updateStarTmpCityPrice();    //临时开始
		$end_tmp    = CityPriceHelper::updateEndTmpCityPrice();    //临时结束

		if ($end_tmp > 0 || $start_tmp > 0 || $normal_tmp > 0) {

			RegionHelper::clearCityPriceCache();
		}

		echo "start_tmp=" . $start_tmp;
		echo "  end_tmp=" . $end_tmp;
		echo "  normal_tmp=" . $normal_tmp;
	}
}