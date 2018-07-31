<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/3/29
 */

namespace api_user\modules\v1\helpers;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\utils\RegionHelper;
use common\helpers\utils\UrlHelper;
use yii\db\Query;

class NoticeHelper extends HelperBase
{
	/**
	 * 获取公告链接
	 * @return array|bool
	 */
	public static function getTip($location)
	{
		$result  = false;
		$city_id = RegionHelper::getCityIdByLocation($location, 95);
		$posts   = (new Query())->select('id,title,link,is_link')->where(['type' => Ref::POSTS_TYPE_NOTICE, 'status' => 1])->orderBy('sort desc,create_time desc')->from("wy_posts")->all();
		if (!$posts) {
//			$posts = (new Query())->select('id,title')->where(['type' => Ref::POSTS_TYPE_NOTICE, 'status' => 1, 'city_id' => 95])->orderBy('sort desc,create_time desc')->from("wy_posts")->all();
		}
		if ($posts) {
			foreach ($posts as $key => $val) {
				$result[$key]['name'] = $val['title'];
				$result[$key]['link'] = $val['link'];
				if (!$val['is_link']) {
					$result[$key]['link'] = UrlHelper::webLink(['notice/view', 'id' => $val['id']]);
				}
			}

		}

		if (!$result) {
			$result = [
				['name' => '无忧帮帮面向全国诚招代理商',
				 'link' => UrlHelper::webLink(['notice/index', 'doc' => '20180323']),
				]
			];
		}

		return $result;
	}

}