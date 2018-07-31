<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/4/23
 */

namespace m\controllers;

use common\helpers\utils\UrlHelper;
use m\helpers\MessageHelper;
use yii\data\Pagination;
use yii\db\Query;
use Yii;

class MessageController extends ControllerAccess
{
	public $layout = 'message';

	/**
	 * 消息列表
	 * @return string
	 */
	public function actionIndex()
	{
		$user_id    = $this->user_id;
		$end_time   = time();
		$start_time = time() - 3 * 30 * 24 * 3600;
		$count      = (new Query())
			->from('bb_51_message')
			->where(['uid' => $user_id, 'type' => 1, 'is_del' => 0])
			->andWhere(['<>', 'status', 2])
			->andWhere(['between', 'create_time', $start_time, $end_time])
			->count();

		$data = [
			'count'                => $count,
			'user_id'              => $user_id,
			'ajax_get_message_url' => UrlHelper::webLink('message/get-message')

		];

		return $this->render('index', $data);
	}

	/**
	 * 用户消息列表
	 */
	public function actionUserIndex()
	{
		$this->actionIndex();
	}

	/**
	 * 小帮消息列表
	 */
	public function actionProviderIndex()
	{
		$this->actionIndex();
	}

	/**
	 * 获取消息列表，并返回数据
	 */
	public function actionGetMessage()
	{
		$uid      = Yii::$app->request->get('uid');
		$type     = Yii::$app->request->get('type', 1);
		$page     = Yii::$app->request->get('page', 1);
		$pagesize = Yii::$app->request->get('pagesize', 10);

		$end_time   = time();
		$start_time = time() - 3 * 30 * 24 * 3600;

		$count = (new Query())
			->from('bb_51_message')
			->where(['uid' => $uid, 'type' => $type, 'is_del' => 0])
			->andWhere(['<>', 'status', 2])
			->andWhere(['between', 'create_time', $start_time, $end_time])
			->orderBy('status asc, id desc')
			->count();

		$pagination           = new Pagination(['totalCount' => $count]);
		$pagination->pageSize = $pagesize;
		$pagination->page     = $page - 1;
		$listData             = (new Query())
			->from('bb_51_message')
			->offset($pagination->offset)
			->limit($pagination->limit)
			->where(['uid' => $uid, 'type' => $type, 'is_del' => 0])
			->andWhere(['<>', 'status', 2])
			->andWhere(['between', 'create_time', $start_time, $end_time])
			->orderBy('status asc, id desc')
			->all();

		foreach ($listData as $k => $v) {
			if ($v['status'] == 0)    //如果是未读状态，更改为已读
			{
				Yii::$app->db->createCommand()
					->update("bb_51_message", ['status' => 1], ['id' => $v['id']])
					->execute();
			}
		}
		$html = '';
		if (!empty($listData)) {
			foreach ($listData as $key => $value) {
				if ($value['status'] == 1)    //已读
				{
					$html .= '<li><div class="data">' . date('Y-m-d', $value['create_time']) . '</div><div class="message"><div class="message-title">' . $value['title'] . '</div><div class="messages-date">' . date('Y-m-d H:i:s', $value['create_time']) . '</div><div class="message-text">' . $value['content'] . '</div></div></li>';
				} else    //未读
				{
					$html .= '<li><div class="data">' . date('Y-m-d', $value['create_time']) . '</div><div class="message"><div class="message-title txt_orange">' . $value['title'] . '</div><div class="messages-date">' . date('Y-m-d H:i:s', $value['create_time']) . '</div><div class="message-text">' . $value['content'] . '</div></div></li>';
				}
			}
			$data['list']  = $html;
			$data['page']  = $page++;
			$data['count'] = count($listData);
		}

		!empty($html) ? MessageHelper::response(200, 'success', $data) : MessageHelper::response(404, 'fail', $html);
	}


	/**
	 * 详细详情
	 * @return string|\yii\web\Response
	 */
	public function actionDetail()
	{
		$id  = Yii::$app->request->get('id');
		$msg = (new Query())
			->from('bb_51_message')
			->where(['id' => $id])
			->one();

		if (empty($msg)) return $this->redirect('/site/error');
		$msg['create_time'] = date('Y-m-d H:i:s', $msg['create_time']);

		//如果是未读状态，更改为已读
		if ($msg['status'] == 0) {
			Yii::$app->db->createCommand()
				->update("bb_51_message", ['status' => 1], ['id' => $id])
				->execute();
		}
		$count = (new Query())
			->from('bb_51_message')
			->where(['uid' => $msg['uid'], 'status' => 0, 'type' => 1, 'is_del' => 0])
			->count();

		$data = ['msg' => $msg, 'count' => $count];

		return $this->render('detail', $data);
	}

}