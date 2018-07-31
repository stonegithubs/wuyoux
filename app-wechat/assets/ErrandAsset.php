<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/4/11
 */

namespace wechat\assets;

use yii\web\AssetBundle;

class ErrandAsset extends AssetBundle
{

	final public function init()
	{
		$this->_init();
		parent::init();
	}

	public function _init()
	{
		$this->baseUrl = "/errand_static";

		return $this->baseUrl;
	}
}
