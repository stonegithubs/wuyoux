<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2018/6/14
 */

namespace common\helpers\utils;


use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\images\ImageHelper;
use common\models\posts\Posts;
use Yii;
use yii\db\Query;

class PostsHelper extends HelperBase
{
	private static function _getData()
	{
		$posts = (new Query())->select('id,title')->where(['type' => Ref:: POSTS_TYPE_NOTICE, 'status' => 1])->orderBy('sort desc,create_time desc')->from("wy_posts")->all();

	}

	//启动页数据
	public static function getStartUpData()
	{
		$params = [
			'type'		=> Ref::POSTS_TYPE_START_UP,
			'status'	=> 1
		];
		$data = (new Query())->select([
			'title',
			'main_pic AS pic_url',
			'is_link AS open_type',
			'link AS open_link',
		])->where($params)->orderBy('sort desc,create_time desc')
			->from("wy_posts")->all();
		if($data){
			foreach ($data as $key => $value){
				$data[$key]['pic_url'] = $value['pic_url'] ? ImageHelper::getCateImageUrl($value['pic_url']) : '';
			}
		}
		return $data;
	}

	//APP弹窗广告
	public static function getPopupAdData()
	{
		$params = [
			'type'		=> Ref::POSTS_TYPE_POPUP,
			'status'	=> 1
		];
		$data = (new Query())->select([
			'title',
			'main_pic AS pic_url',
			'is_link AS open_type',
			'link AS open_link',
		])->where($params)->orderBy('sort desc,create_time desc')
			->from("wy_posts")->all();
		if($data){
			foreach ($data as $key => $value){
				$data[$key]['pic_url'] = $value['pic_url'] ? ImageHelper::getCateImageUrl($value['pic_url']) : '';
			}
		}
		return $data;
	}

}