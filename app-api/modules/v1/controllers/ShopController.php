<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/22
 */

namespace api\modules\v1\controllers;


use api\modules\v1\api\ShopAPI;
use api\modules\v1\helpers\StateCode;
use common\components\Ref;
use common\helpers\orders\OrderHelper;
use common\helpers\security\SecurityHelper;
use common\helpers\shop\ShopHelper;
use common\helpers\utils\DocumentHelper;
use Yii;

class ShopController extends ControllerAccess
{
	/**
	 * 商家入驻 暂时不能删除
	 */
	public function actionEnterShop()
	{
		if ($this->api_version == '1.0') {

			//1.判断是否已经有商家,商家状态是否正确
			//2.处理参数
			//3.商家入驻
			//4.存储图片
			$userInfo = $this->getUserInfo();
			$shop     = ShopHelper::shopDetailByMobile($userInfo['mobile']);
			if ($shop) {
				if ($shop['status'] == 1) {
					//商家已入驻
					$this->setCodeMessage(StateCode::SHOP_HAVE_ENTER);

					return $this->response();
				}
				if ($shop['status'] == 0) {
					$this->setCodeMessage(StateCode::SHOP_ENTERING);

					return $this->response();
				}
			}

			$status = ShopAPI::enterShopFunctionV10($userInfo);
			if ($status) {
				if (YII_ENV_DEV) {
					@file_get_contents("http://admin.dev.281.com.cn/admin.php?s=/Bmob/shop_notice&shops_id=".$shop['id']);
				}
				if (YII_ENV == 'beta') {
					@file_get_contents("http://admin.beta.281.com.cn/admin.php?s=/Bmob/shop_notice&shops_id=".$shop['id']);
				}
				if (YII_ENV_PROD) {
					@file_get_contents("http://admin.281.com.cn/admin.php?s=/Bmob/shop_notice&shops_id=".$shop['id']);
				}

				$this->_message = '您的申请资料已成功提交到无忧帮帮,我司将会在24小时内审核您提交的资料,请耐心等待!';
			} else {
				$this->setCodeMessage(StateCode::SHOP_ENTER_FAILED);
			}
		}

		return $this->response();
	}
}