<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/30
 */

namespace common\helpers\utils;


use common\helpers\HelperBase;
use common\models\users\Token;
use yii\db\Query;
use yii\helpers\StringHelper;

class UtilsHelper extends HelperBase
{


	/**
	 * 显示 带今日的日期
	 *
	 * @param $time
	 *
	 * @return false|string
	 */
	public static function todayTimeFormat($time)
	{

		$today = strtotime(date('Y-m-d', time()));
		$_time = strtotime(date('Y-m-d', $time));
		if ($today == $_time) {
			$result = '今天' . date('H:i', $time);
		} else {
			$result = date('m-d H:i', $time);
		}

		return $result;
	}

	/**
	 * @param $dis
	 *
	 * @return string
	 */
	public static function distance($dis)
	{
		$result = isset($dis) ? $dis . "米" : "0米";
		if ($dis > 1000) {
			$result = round(($dis / 1000), 2) . "公里";
		}

		return $result;
	}

	/**
	 * @param $dis
	 *
	 * @return string
	 */
	public static function distanceLabel($dis)
	{
		$result = isset($dis) ? $dis . "m" : "0m";
		if ($dis > 1000) {
			$result = round(($dis / 1000), 2) . "km";
		}

		return $result;
	}

	//计算时长
	public static function durationLabel($duration)
	{
		if ($duration < 60) {
			$result = $duration . '秒';
		} elseif ($duration < 3600) {
			$result = floor($duration / 60) . '分钟';
		} else {
			$result = floor($duration / 3600) . '小时';
		}

		return $result;
	}

	/**
	 * 判断版本，更新版本
	 * @param $params
	 * @return array
	 */
	public static function checkVersion($params)
	{
		$result       = [];
		$version_data = (new Query())->from("bb_51_appupdate")->select(['versionname', 'base_version', 'url', 'content'])->where(['tool' => $params['app_type'], 'status' => 1])->orderBy(['time' => SORT_DESC])->one();
		if ($version_data) {
			if ($version_data['versionname'] > $params['version']) {
				$result['update']  = self::judgeBaseVersion($params['version'], $version_data['base_version']) ? 1 : 2;//1:强制更新,2:有更新但不强制
				$result['title']   = 'v' . $version_data['versionname'] . '更新内容';
				$result['content'] = $version_data['content'];
				$result['url']     = $version_data['url'];
			} else {
				$result['update'] = 0;
			}
		}

		return $result;
	}


	public static function judgeBaseVersion($version, $base_version)
	{
		$result           = false;
		$int_version      = intval(str_replace(".", "", $version));
		$int_base_version = intval(str_replace(".", "", $base_version));
		if ($int_version > $int_base_version) {
			$result = false;
		} elseif ($int_version < $int_base_version) {
			$result = true;
		}

		return $result;
	}

	/**
	 * 检查传过来的参数是否有空值
	 * @param $params
	 * @return bool  有空值返回false
	 */
	public static function checkEmptyParams($params)
	{
		$result = true;
		foreach ($params as $key => $value) {
			if (!isset($value)) {
				return $result = false;
			}
		}

		return $result;
	}

	/**
	 * 自定义APP版本返回对应的坐标系
	 * IOS1.0.5开始小帮端坐标快送类统一返回高德坐标系。
	 */
	public static function checkVersionCoord($params, $location)
	{
		if (isset($params['app_data']) && $params['app_data']) {
			$app_version = $params['app_data']['app_version'];
			if (version_compare($app_version, "1.0.2", ">")) {    //android和IOS 1.0.2后续版本默认返回高德给前端
				return $location;
			}
		}

		return LBSHelper::convert_Amap2Baidu($location);
	}

	/**
	 * 多维数组字段排序
	 * @param     $multi_array
	 * @param     $sort_key
	 * @param int $sort
	 * @return bool
	 */
	public static function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
	{
		if (is_array($multi_array)) {
			foreach ($multi_array as $row_array) {
				if (is_array($row_array)) {
					$key_array[] = $row_array[$sort_key];
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
		array_multisort($key_array, $sort, $multi_array);

		return $multi_array;
	}

	/**
	 * APP 能读取的webviewURL 主要是Android端
	 * @return array
	 */
	public static function appAllowDomain()
	{
		return [
			'281.com.cn',
			'wuyoubangbang.com.cn',
			'51bangbang.com.cn',
			'ecitic.com',
			'baobeihuijia.com',
			'eqxiu.com',
			'eqh5.com',
			'qq.com',
			'bootcss.com',
			'qbox.me',
			#新版企业送
			'wcd.im',
			'faiusr.com',
			'faisys.com',
			'faisco.cn',
		];
	}

	/**
	 * 自定义字符串截取
	 * @param string $string 字符串
	 * @param string $position 截取位置
	 * @param string $symbol 符号
	 * @param string $limit 截取位数
	 * @param int    $startNum 开始截取位数
	 * @param int    $endNum 结束截取位数
	 * @return string
	 */
	public static function customString($string, $position, $symbol = false, $limit, $startNum = 1, $endNum = 1)
	{
		mb_internal_encoding("UTF-8");
		$result = $string;
		$length = mb_strlen($string, 'utf8');
		if ($length > $limit) {
			switch ($position) {
				case "left":
					$result = "";
					if ($symbol) {
						for ($i = 0; $i < ($limit - 2); $i++) {
							$result .= $symbol;
						}
					}
					$result .= mb_substr($string, ($length - $endNum), $endNum);
					break;
				case "middle":
					$result = mb_substr($string, 0, $startNum);
					if ($symbol) {
						for ($i = 0; $i < ($limit - 2); $i++) {
							$result .= $symbol;
						}
					}
					$result .= mb_substr($string, ($length - $endNum), $endNum);
					break;
				case "right":
					$result = mb_substr($string, 0, $startNum);
					if ($symbol) {
						for ($i = 0; $i < ($limit - 2); $i++) {
							$result .= $symbol;
						}
					}

					break;
			}
		}

		return $result;
	}
}