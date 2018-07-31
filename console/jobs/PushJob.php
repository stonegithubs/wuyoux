<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: 2017/7/2 23:41
 */

namespace console\jobs;

use common\components\Ref;
use common\helpers\users\UserHelper;
use common\helpers\utils\PushHelper;
use common\helpers\utils\QueueHelper;
use common\helpers\utils\UrlHelper;
use common\helpers\wechat\WechatHelper;
use common\models\users\UserToken;
use common\models\util\PushLog;
use common\models\push\BackerPushLog;
use Yii;
use yii\db\Query;

class PushJob extends JobBase
{
	public $default_page_size = 100;

	//推送给用户端
	public function toOneTransMissionForUser()
	{
		$push_info = isset($this->params['push_info']) ? $this->params['push_info'] : [];
		$user_id   = isset($this->params['user_id']) ? $this->params['user_id'] : null;
		$tag       = isset($this->params['tag']) ? $this->params['tag'] . $user_id : 'toOneTransmission' . $user_id;
		if ($user_id) {
			$pushRes    = [];
			$clientData = UserHelper::getRedisUserInfo($user_id);
			if ($clientData) {
				$pushRes = PushHelper::oldUserSendOneTransmission($clientData['client_id'], $push_info, $push_info['inform_content']);

			} else {

				$userData = (new Query())->select("gtui,tool")->from('bb_51_userdata')->where(['uid' => $user_id])->one();
				if (count($userData) > 0 && isset($userData['gtui'])) {
					$pushRes = PushHelper::oldUserSendOneTransmission($userData['gtui'], $push_info, $push_info['inform_content']);
				}
			}

			//加上新用户端的数据
			$model = UserToken::findOne(['user_id' => $user_id, 'role' => 'user']);
			if ($model) {
				PushHelper::userSendOneTransmission($model->client_id, $push_info, $push_info['inform_content']);
			}

			$res = array_merge($pushRes, $push_info);

			Yii::$app->debug->job_info($tag, $res);
			var_dump($res);
		}
	}

	//推送给小帮端
	public function toOneTransMissionForProvider()
	{
		//兼容模式下的解决方案
		//1、shop表 查出小帮的user_id
		//2、优先查出wy_user_token 对应的token数据
		//2.1、采用商家端的推送
		//3、查出51_userdata的数据，采用用户端的推送

		$provider_id = isset($this->params['provider_id']) ? $this->params['provider_id'] : null;
		$push_info   = isset($this->params['push_info']) ? $this->params['push_info'] : null;
		$tag         = isset($this->params['tag']) ? $this->params['tag'] : 'toOneTransMissionForProvider';
		$user_id     = self::getShopUserId($provider_id);
		if ($user_id) {

			$push_info['push_user_id']     = $user_id;
			$push_info['provider_user_id'] = $user_id;
			$model                         = UserToken::findOne(['user_id' => $user_id, 'role' => 'provider']);
			$logParams                     = [
				'provider_id' => $provider_id,
				'user_id'     => $user_id,
				'role'        => 'provider',
				'tag'         => $tag,
				'data'        => json_encode($push_info),
			];

			$pushRes = false;
			if ($model) {

				if ($tag == "biz_send_order") {    //企业送订单 低于订单号不推送
					if (version_compare($model->app_version, "1.0.2", "<")) {
						return false;
					}
				}

				$pushRes                  = PushHelper::providerSendOneTransmission($model->client_id, $push_info, $push_info['inform_content']);
				$logParams['app_type']    = $model->app_type;
				$logParams['app_version'] = $model->app_version;
				$logParams['client_id']   = $model->client_id;
				$this->savePushLog($pushRes, $logParams);
			} else {

				$userData = (new Query())->select("gtui,tool,versionName")->from('bb_51_userdata')->where(['uid' => $user_id])->one();
				if (count($userData) > 0) {
					$pushRes                  = PushHelper::oldUserSendOneTransmission($userData['gtui'], $push_info, $push_info['inform_content']);
					$logParams['tag']         = $tag . "old";
					$logParams['app_type']    = $userData['tool'] == 1 ? 2 : 1;
					$logParams['app_version'] = $userData['versionName'];
					$logParams['client_id']   = $userData['gtui'];
					$this->savePushLog($pushRes, $logParams);
				} else {
					Yii::$app->debug->job_info("无效小帮数据user_id", $user_id);
				}
			}

			if (is_array($pushRes)) {
				$res = array_merge($pushRes, $push_info);
				Yii::$app->debug->job_info($tag, $res);
				var_dump($res);
			}

		} else {
			Yii::$app->debug->job_info("无效小帮user_id", $user_id);
		}
		echo "provider";
	}

	/**
	 * 推送链接给用户
	 */
	public function pushLinkToUser()
	{
		$city_id     = $this->params['city_id'];
		$title       = $this->params['title'];
		$content     = $this->params['content'];
		$link        = $this->params['link'];
		$start_time  = $this->params['start_time'];
		$end_time    = $this->params['end_time'];
		$mobile      = $this->params['mobile'];
		$msg_title   = $this->params['msg_title'];
		$msg_content = $this->params['msg_content'];
		$now         = time();
		$page_limit  = $this->default_page_size;
		$push_num    = 0;


		$condition = "";
		//地区条件
		if ($city_id) $condition = " and user.city_id IN ({$city_id})";
		//注册时间条件
		if ($start_time && $end_time) $condition .= " and user.reg_time between {$start_time} and {$end_time}";
		//电话号码
		if ($mobile) $condition .= " and user.mobile IN ({$mobile})";

		$sql       = "SELECT count(token.user_id) as num FROM `bb_51_user` as user left join `wy_user_token` as token on user.uid = token.user_id WHERE token.role = 'user' {$condition}";
		$total_num = Yii::$app->getDb()->createCommand($sql)->queryOne();

		$total_num = $total_num['num'];
		$page      = ['total_num' => intval($total_num), 'page_limit' => $page_limit, 'group' => ceil($total_num / $page_limit)];

		if ($total_num > 0) {
			for ($i = 0; $i < $page['group']; $i++) {
				$curr = $page_limit * $i;
				$sql  = "SELECT token.client_id,token.user_id FROM `bb_51_user` as user left join `wy_user_token` as token on user.uid = token.user_id WHERE token.role = 'user' {$condition} limit $curr,$page_limit ";

				$list = Yii::$app->getDb()->createCommand($sql)->queryAll();

				QueueHelper::pushPageMessageToUser($list,$link,$title,$content,$msg_title,$msg_content,Ref::USER_TYPE_USER,1,$this->params['push_id']);

//				foreach ($list as $k => $v) {
//					$push = PushHelper::pushLinkToUser($v['client_id'], $title, $content, $link);
//					if ($push) {
//						//添加消息
//						self::addUserMessageLog($v['user_id'], $msg_title, $msg_content);
//						$push_num++;
//					}
//				}
			}
		}
		//更新后台操作记录
		$push_data = ['id' => $this->params['push_id'], 'push_time' => $now, 'push_num' => $total_num, 'success_num' => $push_num, 'status' => 1];

		$this->updatePushLog($push_data);

		echo "总共推送人数:{$total_num}人\n";
	}

	/**
	 * 更新后台推送记录
	 * @param $data
	 * @return bool
	 */
	public function updatePushLog($data)
	{
		$result  = false;
		$pushLog = BackerPushLog::find()->where(['id' => $data['id']])->one();
		if ($pushLog) {
			$pushLog->attributes = $data;
			$pushLog->finish_time = time();
			$result               = $pushLog->save() ? true : false;
		}

		return $result;
	}

	/**
	 * 推送链接给商家
	 */
	public function pushLinkToProvider()
	{
		$province_id = $this->params['province_id'];
		$city_id     = $this->params['city_id'];
		$area_id     = $this->params['area_id'];
		$title       = $this->params['title'];
		$content     = $this->params['content'];
		$link        = $this->params['link'];
		$start_time  = $this->params['start_time'];
		$end_time    = $this->params['end_time'];
		$mobile      = $this->params['mobile'];
		$msg_title   = $this->params['msg_title'];
		$msg_content = $this->params['msg_content'];
		$now         = time();
		$page_limit  = $this->default_page_size;
		$push_num    = 0;
		$condition   = "";
		//地区条件
		if ($area_id) {
			$condition = "and shops.area_id = {$area_id}";
		} elseif (!empty($city_id) && empty($area_id)) {
			$condition = "and shops.city_id = {$city_id}";
		} elseif (empty($city_id) && empty($city_id) && !empty($province_id)) {
			$condition = "and shops.province_id = {$province_id}";
		}
		//注册时间条件
		if ($start_time && $end_time) $condition .= " and shops.create_time between {$start_time} and {$end_time}";
		//电话号码
		if ($mobile) $condition .= " and shops.utel IN ({$mobile})";

		$sql = "SELECT count(token.user_id) as num FROM `bb_51_shops` as shops left join `wy_user_token` as token on shops.uid = token.user_id WHERE token.role = 'provider' {$condition}";

		$total_num = Yii::$app->getDb()->createCommand($sql)->queryOne();
		$total_num = $total_num['num'];
		$page      = ['total_num' => intval($total_num), 'page_limit' => $page_limit, 'group' => ceil($total_num / $page_limit)];
		if ($total_num > 0) {
			for ($i = 0; $i < $page['group']; $i++) {
				$curr = $page_limit * $i;
				$sql  = "SELECT token.client_id,token.user_id FROM `bb_51_shops` as shops left join `wy_user_token` as token on shops.uid = token.user_id WHERE token.role = 'provider'  {$condition} limit $curr,$page_limit ";
				$list = Yii::$app->getDb()->createCommand($sql)->queryAll();

				QueueHelper::pushPageMessageToUser($list,$link,$title,$content,$msg_title,$msg_content,Ref::USER_TYPE_PROVIDER,1,$this->params['push_id']);
//				foreach ($list as $k => $v) {
//					$push = PushHelper::pushLinkToPrivider($v['client_id'], $title, $content, $link);
//					if ($push) {
//						//添加消息中心
//						self::addUserMessageLog($v['user_id'], $msg_title, $msg_content);
//						$push_num++;
//					}
//				}
			}
		}
		//更新后台操作记录
		$push_data = ['id' => $this->params['push_id'], 'push_time' => $now, 'push_num' => $total_num, 'success_num' => $push_num, 'status' => 1];
		$this->updatePushLog($push_data);
		echo "总共推送人数:{$total_num}人\n";
	}

	/**
	 * 添加信息记录
	 * @param $user_id
	 * @param $title
	 * @param $content
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public function addUserMessageLog($user_id, $title, $content)
	{
		$now  = time();
		$data = "'{$user_id}',1,'{$title}','{$content}','{$now}'";
		$sql  = "INSERT INTO `bb_51_message`(`uid`,`type`,`title`,`content`,`create_time`) VALUES ({$data})";
		Yii::$app->getDb()->createCommand($sql)->execute();

		return true;
	}


	/**
	 * 推送活动给商家
	 */
	public function pushActivityToProvider()
	{
		$province_id = $this->params['province_id'];
		$city_id     = $this->params['city_id'];
		$area_id     = $this->params['area_id'];
		$title       = $this->params['title'];
		$content     = $this->params['content'];
		$start_time  = $this->params['start_time'];
		$end_time    = $this->params['end_time'];
		$mobile      = $this->params['mobile'];
		$msg_title   = $this->params['msg_title'];
		$msg_content = $this->params['msg_content'];
		$now         = time();
		$page_limit  = $this->default_page_size;
		$push_num    = 0;
		$condition   = "";
		//地区条件
		if ($area_id) {
			$condition = "and shops.area_id = {$area_id}";
		} elseif (!empty($city_id) && empty($area_id)) {
			$condition = "and shops.city_id = {$city_id}";
		} elseif (empty($city_id) && empty($city_id) && !empty($province_id)) {
			$condition = "and shops.province_id = {$province_id}";
		}
		//注册时间条件
		if ($start_time && $end_time) $condition .= " and shops.create_time between {$start_time} and {$end_time}";
		//电话号码
		if ($mobile) $condition .= " and shops.utel IN ({$mobile})";

		$sql = "SELECT count(token.user_id) as num FROM `bb_51_shops` as shops left join `wy_user_token` as token on shops.uid = token.user_id WHERE token.role = 'provider' {$condition}";

		$total_num = Yii::$app->getDb()->createCommand($sql)->queryOne();
		$total_num = $total_num['num'];
		$page      = ['total_num' => intval($total_num), 'page_limit' => $page_limit, 'group' => ceil($total_num / $page_limit)];
		if ($total_num > 0) {
			for ($i = 0; $i < $page['group']; $i++) {
				$curr = $page_limit * $i;
				$sql  = "SELECT token.client_id,token.user_id FROM `bb_51_shops` as shops left join `wy_user_token` as token on shops.uid = token.user_id WHERE token.role = 'provider'  {$condition} limit $curr,$page_limit ";
				$list = Yii::$app->getDb()->createCommand($sql)->queryAll();


				QueueHelper::pushPageMessageToUser($list,$this->params['push_info'],$title,$content,$msg_title,$msg_content,Ref::USER_TYPE_PROVIDER,2,$this->params['push_id']);

//				foreach ($list as $k => $v) {
//					$push = PushHelper::providerSendOneTransmission($v['client_id'], $this->params['push_info'], $content, $title);
//					if ($push) {
//						self::addUserMessageLog($v['user_id'], $msg_title, $msg_content);
//						$push_num++;
//					}
//				}
			}
		}
		//更新后台操作记录
		$push_data = ['id' => $this->params['push_id'], 'push_time' => $now, 'push_num' => $total_num, 'success_num' => $push_num, 'status' => 1];
		$this->updatePushLog($push_data);

		echo "总共推送人数:{$total_num}人\n";
	}

	/**
	 * 推送活动给用户
	 */
	public function pushActivityToUser()
	{
		$city_id     = $this->params['city_id'];
		$title       = $this->params['title'];
		$content     = $this->params['content'];
		$start_time  = $this->params['start_time'];
		$end_time    = $this->params['end_time'];
		$mobile      = $this->params['mobile'];
		$msg_title   = $this->params['msg_title'];
		$msg_content = $this->params['msg_content'];
		$page_limit  = $this->default_page_size;
		$push_num    = 0;
		$now         = time();

		$condition = "";
		//地区条件
		if ($city_id) $condition = " and user.city_id IN ({$city_id})";
		//注册时间条件
		if ($start_time && $end_time) $condition .= " and user.reg_time between {$start_time} and {$end_time}";
		//电话号码
		if ($mobile) $condition .= " and user.mobile IN ({$mobile})";

		$sql = "SELECT count(token.user_id) as num FROM `bb_51_user` as user left join `wy_user_token` as token on user.uid = token.user_id WHERE token.role = 'user' {$condition}";

		$total_num = Yii::$app->getDb()->createCommand($sql)->queryOne();
		$total_num = $total_num['num'];
		$page      = ['total_num' => intval($total_num), 'page_limit' => $page_limit, 'group' => ceil($total_num / $page_limit)];

		if ($total_num > 0) {
			for ($i = 0; $i < $page['group']; $i++) {
				$curr = $page_limit * $i;
				$sql  = "SELECT token.client_id,token.user_id FROM `bb_51_user` as user left join `wy_user_token` as token on user.uid = token.user_id WHERE token.role = 'user' {$condition} limit $curr,$page_limit ";

				$list = Yii::$app->getDb()->createCommand($sql)->queryAll();

				QueueHelper::pushPageMessageToUser($list,$this->params['push_info'],$title,$content,$msg_title,$msg_content,Ref::USER_TYPE_USER,2,$this->params['push_id']);

//				foreach ($list as $k => $v) {
//					$push = PushHelper::userSendOneTransmission($v['client_id'], $this->params['push_info'], $content, $title);
//					//添加消息中心
//					if ($push) {
//						self::addUserMessageLog($v['user_id'], $msg_title, $msg_content);
//						$push_num++;
//					}
//				}

			}
		}
		//更新后台操作记录
		$push_data = ['id' => $this->params['push_id'], 'push_time' => $now, 'push_num' => $total_num, 'success_num' => $push_num, 'status' => 1];
		$this->updatePushLog($push_data);

		echo "总共推送人数:{$total_num}人\n";
	}

	/**
	 * 推送信息-分页推送
	 * @throws \yii\db\Exception
	 */
	public function pushPageMessageToUser()
	{
		$list       = $this->params['list'];
		$pushInfo  = $this->params['push_info'];
		$title      = $this->params['title'];
		$content    = $this->params['content'];
		$msgTitle   = $this->params['msg_title'];
		$msgContent = $this->params['msg_content'];
		$userType = $this->params['user_type'];
		$pushType = $this->params['push_type'];
		$pushId = $this->params['push_id'];
		$push_num = 0;
		if($list){
			foreach ($list as $k => $v) {
				if($pushType == 1){
					//Android链接
					if($userType == Ref::USER_TYPE_USER){
						$push = PushHelper::pushLinkToUser($v['client_id'], $title, $content, $pushInfo);
					}else{
						$push = PushHelper::pushLinkToPrivider($v['client_id'], $title, $content, $pushInfo);
					}
				}else{
					//APP内推送
					if($userType == Ref::USER_TYPE_USER){
						$push = PushHelper::userSendOneTransmission($v['client_id'], $pushInfo, $content, $title);
					}else{
						$push = PushHelper::providerSendOneTransmission($v['client_id'], $pushInfo, $content, $title);
					}

				}
				//添加消息中心
				if (isset($push['result']) && ($push['result'] == "ok")) {
					$push_num++;
					self::addUserMessageLog($v['user_id'], $msgTitle, $msgContent);
				}
			}
		}
		$push = BackerPushLog::findOne(['id'=>$pushId]);
		$push_data = ['id' => $pushId,'success_num' => bcadd($push->success_num,$push_num),'finish_time'=>time()];
		$this->updatePushLog($push_data);
		echo "推送成功人数:{$push_num}\n";
	}

	//获取小帮的用户ID
	private function getShopUserId($id)
	{
		$key = "getShopUserId" . $id;
		Yii::$app->cache->delete($key);
		$key     = "newGetShopUserId" . $id;
		$user_id = Yii::$app->cache->get($key);
		if (!$user_id) {                        //更新，更新失败执行删除
			$shopData = (new Query())->select(['uid'])->from('bb_51_shops')->where(['id' => $id])->one();
			if ($shopData) {
				$user_id = $shopData['uid'];
				Yii::$app->cache->set($key, $user_id, 86400);
			}
		}

		return $user_id;
	}

	//保存日志
	private function savePushLog($pushRes, $logParams)
	{
		$model                = new PushLog();
		$logParams['status']  = isset($pushRes['status']) && $pushRes['status'] == 'successed_online' ? 1 : 0;
		$logParams['task_id'] = isset($pushRes['taskid']) ? $pushRes['taskid'] : '推送失败无TaskID';
		$model->attributes    = $logParams;
		$model->create_time   = time();
		$model->save();
	}


	//新小帮入驻
	public function newApplyProvider()
	{
		$userId = isset($this->params['userId']) ? $this->params['userId'] : null;

		if ($userId) {

			@file_get_contents(UrlHelper::adminDomain() . '/admin.php?s=/Bmob/shop_notice&uid=' . $userId);
		}

	}

	public function sendWechatDispatchMessageToProvider()
	{
		$orderId = $this->params['orderId'];
		$cateId = $this->params['cateId'];
		$option = $this->params['option'];

		$result = WechatHelper::sendWechatDispatchMessageToProvider($orderId,$cateId,$option);
		if($result){
			echo "推送成功\n";
		}else{
			echo "推送失败\n";
		}
	}

	public function sendWechatDispatchMessageToUser()
	{
		$orderId = $this->params['orderId'];
		$cateId = $this->params['cateId'];
		$option = $this->params['option'];

		$result = WechatHelper::sendWechatDispatchMessageToUser($orderId,$cateId,$option);
		if($result){
			echo "推送成功\n";
		}else{
			echo "推送失败\n";
		}
	}
}