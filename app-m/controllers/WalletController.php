<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/1/10
 */

namespace m\controllers;

use common\helpers\utils\UrlHelper;
use m\helpers\MemberHelper;
use m\helpers\WalletHelper;
use yii\db\Query;
use Yii;
use m\helpers\ShopHelper;

class WalletController extends ControllerAccess
{
	public $layout = 'wallet';

	/**
	 * 用户账单列表
	 * @return string
	 */
	public function actionUserBill()
	{
		$uid  = $this->user_id;
		$type = Yii::$app->request->get('type', 2);

		$code = 0;
		if (empty($uid)) //没有传参数则返回数据为空
		{
			$code = 20000;
			$data = [
				'code' => $code,
			];

			return $this->render('user-bill', $data);
		} else {
			//查询当前用户信息,并检查是否有记录是错误的,
			//若有错误则删除旧的用户详情,并新建新的用户支出收入明细
			$currentYear = strtotime(date('Y', time()) . '-01-01');
			$nextYear    = strtotime((date('Y', time()) + 1) . '-01-01');

			if ($type == 3) {
				$count = (new Query())
					->select('')
					->from('bb_51_income_pay')
					->where(['status' => 1, 'uid' => $uid])
					->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
					->count();
			} else {
				$count = (new Query())
					->select('')
					->from('bb_51_income_pay')
					->where(['status' => 1, 'uid' => $uid, 'type' => $type])
					->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
					->count();
			}

			$ajax_user_bill_num_url = UrlHelper::webLink('wallet/ajax-user-bill-num');
			$ajax_user_bill_url     = UrlHelper::webLink('wallet/ajax-user-bill');

			$data = [
				'uid'                    => $uid,
				'type'                   => $type,
				'code'                   => $code,
				'count'                  => $count,
				'ajax_user_bill_num_url' => $ajax_user_bill_num_url,
				'ajax_user_bill_url'     => $ajax_user_bill_url
			];

			return $this->render('user-bill', $data);
		}
	}

	/**
	 * 小帮账单列表
	 * @return string
	 */
	public function actionShopBill()
	{
		$uid  = $this->user_id;
		$type = Yii::$app->request->get('type', 1);

		$code = 0;
		if (empty($uid)) //没有传参数则返回数据为空
		{
			$code = 20000;
			$data = [
				'code' => $code,
			];

			return $this->render('shop-bill', $data);
		} else {
			//查询当前用户信息,并检查是否有记录是错误的,
			//若有错误则删除旧的用户详情,并新建新的用户支出收入明细
			$currentYear = strtotime(date('Y', time()) . '-01-01');
			$nextYear    = strtotime((date('Y', time()) + 1) . '-01-01');

			if ($type == 3) {
				$count = (new Query())
					->select('')
					->from('bb_51_income_shop')
					->where(['staffuid' => $uid])
					->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
					->count();
			} else {
				$count = (new Query())
					->select('')
					->from('bb_51_income_shop')
					->where(['staffuid' => $uid, 'account_type' => $type])
					->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
					->count();
			}

			$ajax_shop_bill_num_url = UrlHelper::webLink('wallet/ajax-shop-bill-num');
			$ajax_shop_bill_url     = UrlHelper::webLink('wallet/ajax-shop-bill');

			$data = [
				'uid'                    => $uid,
				'type'                   => $type,
				'code'                   => $code,
				'count'                  => $count,
				'ajax_shop_bill_num_url' => $ajax_shop_bill_num_url,
				'ajax_shop_bill_url'     => $ajax_shop_bill_url
			];

			return $this->render('shop-bill', $data);
		}
	}

	/**
	 * 用户收支明细详情
	 * @return string
	 */
	public function actionUserBillDetail()
	{
		$uid       = $this->user_id;
		$income_id = Yii::$app->request->get('income_id');

		$code   = 0;
		$extend = '';
		if (empty($uid)) //没有传参数则返回数据为空
		{
			$code = 20000;
		} else {
			if (empty($income_id)) {
				$code = 20000;
			} else {
				$incomeUserInfoBack = MemberHelper::get_income_bill_info_by_user($income_id);
				$incomeUserInfo     = $incomeUserInfoBack['html'];
				$extend             = $incomeUserInfoBack['extend'];
			}
		}
		$info = ['code' => $code, 'data' => $incomeUserInfo, 'extend' => $extend];

		return $this->render('user-bill-detail', $info);
	}

	/**
	 * 小帮收支明细详情
	 * @return string
	 */
	public function actionShopBillDetail()
	{
		$uid       = $this->user_id;
		$income_id = Yii::$app->request->get('income_id');

		$code   = 0;
		$extend = '';
		if (empty($uid)) //没有传参数则返回数据为空
		{
			$code = 20000;
		} else {
			if (empty($income_id)) {
				$code = 20000;
			} else {
				$incomeShopInfoBack = ShopHelper::getIncomeBillFromUser($income_id);
				$incomeShopInfo     = $incomeShopInfoBack['html'];
				$extend             = $incomeShopInfoBack['extend'];
			}
		}
		$info = ['code' => $code, 'data' => $incomeShopInfo, 'extend' => $extend];

		return $this->render('shop-bill-detail', $info);
	}

	/**
	 * 获取用户收支明细数目,并返回数据----API
	 */
	public function actionAjaxUserBillNum()
	{
		$uid  = $this->user_id;
		$type = Yii::$app->request->get('type');

		$userinfo = (new Query())->select('uid')->from('bb_51_user')->where(['uid' => $uid])->one();

		if (empty($userinfo)) {
			$code = 20000;
		} else {
			$count = 0;
			if ($userinfo) {
				$currentYear = strtotime(date('Y', time()) . '-01-01');
				$nextYear    = strtotime((date('Y', time()) + 1) . '-01-01');


				if ($type == 3) {
					$count = (new Query())
						->select('')
						->from('bb_51_income_pay')
						->where(['status' => 1, 'uid' => $uid])
						->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
						->count();
				} else {
					$count = (new Query())
						->select('')
						->from('bb_51_income_pay')
						->where(['status' => 1, 'uid' => $uid, 'type' => $type])
						->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
						->count();
				}
			}

			$code = 200;
			$data = ['count' => $count ? $count : 0, 'user' => $userinfo, 'is_shop' => 0];
		}

		if ($code == 200) {
			$result['status'] = 200;
			$result['msg']    = 'success';
			$result['data']   = $data;
		} else {
			$result['status'] = 0;
			$result['msg']    = 'fail';
			$result['data']   = '';
		}

		return json_encode($result);
	}

	/**
	 * 获取小帮收支明细数目,并返回数据----API
	 */
	public function actionAjaxShopBillNum()
	{
		$uid  = $this->user_id;
		$type = Yii::$app->request->get('type');

		if (empty($uid)) {
			$code = 20000;
		} else {
			if ($uid) {
				$currentYear = strtotime(date('Y', time()) . '-01-01');
				$nextYear    = strtotime((date('Y', time()) + 1) . '-01-01');
				if ($type == 3) {
					$count = (new Query())
						->select('')
						->from('bb_51_income_shop')
						->where(['staffuid' => $uid])
						->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
						->count();
				} else {
					$count = (new Query())
						->select('')
						->from('bb_51_income_shop')
						->where(['staffuid' => $uid, 'account_type' => $type])
						->andFilterWhere(['between', 'create_time', $currentYear, $nextYear])
						->count();
				}
				$code = 200;
				$data = ['count' => $count ? $count : 0, 'user' => $uid, 'is_shop' => 0];
			}
			if ($code == 200) {
				$result['status'] = 200;
				$result['msg']    = 'success';
				$result['data']   = $data;
			} else {
				$result['status'] = 0;
				$result['msg']    = 'fail';
				$result['data']   = '';
			}

			return json_encode($result);
		}
	}


	/**
	 * 获取用户账单列表，并返回数据
	 */
	public function actionAjaxUserBill()
	{
		$uid      = $this->user_id;
		$type     = Yii::$app->request->get('type');
		$page     = Yii::$app->request->get('page', 1);
		$pagesize = Yii::$app->request->get('pagesize', 20);
		$year     = Yii::$app->request->get('year');
		$month    = Yii::$app->request->get('month');

		$params = [
			'uid'      => $uid,
			'type'     => $type,
			'page'     => $page,
			'pagesize' => $pagesize,
			'year'     => $year,
			'month'    => $month
		];

		$listData = MemberHelper::get_user_bill($params);

		$html = '';
		if (!empty($listData)) {
			foreach ($listData as $key => $value) {
				$url = UrlHelper::webLink('wallet/user-bill-detail') . '?income_id=' . $value['id'];
				if ($value['month_key'] == 1) {
					//查询月收入,月支出
					$currentYear  = date('Y', $value['create_time']);
					$currentMonth = intval(date('m', time()));

					$start_month     = strtotime($currentYear . '-' . $value['month']);
					$start_month_day = date("t", $start_month);
					$end_month       = $start_month + $start_month_day * 24 * 3600;

					$month_income = (new Query())
						->select('*')
						->from('bb_51_income_pay')
						->where(['status' => 1, 'type' => 1, 'uid' => $params['uid']])
						->andFilterWhere(['between', 'create_time', $start_month, $end_month])
						->sum('money');

					$month_outcome = (new Query())
						->select('*')
						->from('bb_51_income_pay')
						->where(['status' => 1, 'type' => 2, 'uid' => $params['uid']])
						->andFilterWhere(['between', 'create_time', $start_month, $end_month])
						->sum('money');

					$month_income  = isset($month_income) ? $month_income : '0.00';
					$month_outcome = isset($month_outcome) ? $month_outcome : '0.00';
					//END

					if ($currentMonth == $value['month'] && $value['year'] == intval(date('Y', time()))) {
						$year_month = '本月';
					} else {
						$year_month = $value['year'] . '年' . $value['month'] . '月';
					}

					if ($params['type'] == 3) {
						$html
							.= '
							<li class="list-group-title item-content">
							<div class="item-inner">
							<div class="item-title-row">
								<div class="item-title">' . $year_month . '</div>
								<div class="item-after">收入:￥' . $month_income . '，支出:￥' . $month_outcome . '</div>
							</div>
							<!--<div class="item-text"><i class="iconfont icon-02"></i></div>-->
							</div>
							</li>
						';
					} elseif ($params['type'] == 1) {
						$html
							.= '
							<li class="list-group-title item-content">
							<div class="item-inner">
							<div class="item-title-row">
								<div class="item-title">' . $year_month . ' <span class="sub">总入账:￥' . $month_income . '</span></div>
							</div>
							<!--<div class="item-text"><i class="iconfont icon-02"></i></div>-->
							</div>
							</li>
						';
					} else {
						$html
							.= '
							<li class="list-group-title item-content">
							<div class="item-inner">
							<div class="item-title-row">
								<div class="item-title">' . $year_month . ' <span class="sub">总支出:￥' . $month_outcome . '</span></div>
							</div>
							<!--<div class="item-text"><i class="iconfont icon-02"></i></div>-->
							</div>
							</li>
						';
					}
				}


				$html
					.= '<li class="count_list">
							<a href="' . $url . '" class="item-link item-content external">
								<div class="item-inner">
									<div class="item-title-row">
										<div class="item-title">' . $value['title'] . '</div>
									</div>
									<div class="item-text">' . date('m-d', $value['create_time']) . '&nbsp;' . date('H:i', $value['create_time']) . '</div>
								</div>
								<div class="item-after">' . $value['show_money'] . ' </div>
							</a>
						</li>';
			}
			$page = $params['page'];

			$data['status'] = 200;
			$data['msg']    = 'success';
			$data['list']   = $html;
			$data['page']   = $page++;
			$data['count']  = count($listData);

			return json_encode($data);
		} else {
			if ($params['page'] == 1) {
				//第一页无数据则显示无数据
				$html
					= '<div class="page-content" style="background: #F1F1F1;">
                        <div class="logo1" style="width:50%; margin:20% auto 0">
                            <img src="/static/images/no_order.png" style="width:100%;"/>
                        </div>
                        <p class="txt_center">暂无数据</p>
                    </div>';
			} else {
				$html = '';
			}

			$data['status'] = 404;
			$data['msg']    = 'fail';
			$data['list']   = $html;

			return json_encode($data);
		}
	}

	/**
	 * 获取小帮账单列表，并返回数据
	 */
	public function actionAjaxShopBill()
	{
		$uid      = $this->user_id;
		$type     = Yii::$app->request->get('type');
		$page     = Yii::$app->request->get('page', 1);
		$pagesize = Yii::$app->request->get('pagesize', 20);
		$year     = Yii::$app->request->get('year');
		$month    = Yii::$app->request->get('month');

		$params = [
			'uid'      => $uid,
			'type'     => $type,
			'page'     => $page,
			'pagesize' => $pagesize,
			'year'     => $year,
			'month'    => $month
		];

		$listData = ShopHelper::get_shop_bill($params);

		$html = '';
		if (!empty($listData)) {
			foreach ($listData as $key => $value) {
				$url = UrlHelper::webLink('wallet/shop-bill-detail') . '?is_shop=1&income_id=' . $value['id'];
				if ($value['month_key'] == 1) {
					//查询月收入,月支出
					$currentYear  = date('Y', $value['create_time']);
					$currentMonth = intval(date('m', time()));

					$start_month     = strtotime($currentYear . '-' . $value['month']);
					$start_month_day = date("t", $start_month);
					$end_month       = $start_month + $start_month_day * 24 * 3600;

					$month_income = (new Query())
						->select('*')
						->from('bb_51_income_shop')
						->where(['status' => 1, 'account_type' => 1, 'staffuid' => $params['uid']])
						->andFilterWhere(['between', 'create_time', $start_month, $end_month])
						->sum('money');

					$month_outcome = (new Query())
						->select('*')
						->from('bb_51_income_shop')
						->where(['staffuid' => $params['uid']])
						->andFilterWhere(['between', 'create_time', $start_month, $end_month])
						->andFilterWhere(['in', 'account_type', 2, 3])
						->sum('money');

					$month_income  = isset($month_income) ? $month_income : '0.00';
					$month_outcome = isset($month_outcome) ? $month_outcome : '0.00';

					//END
					if ($currentMonth == $value['month'] && $value['year'] == intval(date('Y', time()))) {
						$year_month = '本月';
					} else {
						$year_month = $value['year'] . '年' . $value['month'] . '月';
					}

					if ($params['type'] == 3) {
						$html
							.= '
							<li class="list-group-title item-content">
							<div class="item-inner">
							<div class="item-title-row">
								<div class="item-title">' . $year_month . '</div>
								<div class="item-after">收入:￥' . $month_income . '，支出:￥' . $month_outcome . '</div>
							</div>
							<!--<div class="item-text"><i class="iconfont icon-02"></i></div>-->
							</div>
							</li>
						';
					} elseif ($params['type'] == 1) {
						$html
							.= '
							<li class="list-group-title item-content">
							<div class="item-inner">
							<div class="item-title-row">
								<div class="item-title">' . $year_month . ' <span class="sub">总入账:￥' . $month_income . '</span></div>
							</div>
							<!--<div class="item-text"><i class="iconfont icon-02"></i></div>-->
							</div>
							</li>
						';
					} else {
						$html
							.= '
							<li class="list-group-title item-content">
							<div class="item-inner">
							<div class="item-title-row">
								<div class="item-title">' . $year_month . ' <span class="sub">总支出:￥' . $month_outcome . '</span></div>
							</div>
							<!--<div class="item-text"><i class="iconfont icon-02"></i></div>-->
							</div>
							</li>
						';
					}
				}
				$html
					.= '<li class="count_list">
							<a href="' . $url . '" class="item-link item-content external">
								<div class="item-inner">
									<div class="item-title-row">
										<div class="item-title">' . $value['title'] . '</div>
									</div>
									<div class="item-text">' . date('m-d', $value['create_time']) . '&nbsp;' . date('H:i', $value['create_time']) . '</div>
								</div>
								<div class="item-after">' . $value['show_money'] . ' </div>
							</a>
						</li>';
			}

			$page           = $params['page'];
			$data['status'] = 200;
			$data['msg']    = 'success';
			$data['list']   = $html;
			$data['page']   = $page++;
			$data['count']  = count($listData);

			return json_encode($data);
		} else {

			if ($params['page'] == 1) {
				//第一页无数据则显示无数据
				$html
					= '<div class="page-content" style="background: #F1F1F1;">
                        <div class="logo1" style="width:50%; margin:20% auto 0">
                            <img src="/static/images/no_order.png" style="width:100%;"/>
                        </div>
                        <p class="txt_center">暂无数据</p>
                    </div>';
			} else {
				$html = '';
			}
			$data['status'] = 404;
			$data['msg']    = 'fail';
			$data['list']   = $html;

			return json_encode($data);
		}
	}

	/**
	 * 获取多个订单信息
	 * @return string
	 */
	public function actionFindOrders()
	{
		$income_id = Yii::$app->request->get('income_id');
		$result    = WalletHelper::findOrders($income_id);

		return $this->renderPartial('find-order', $result);
	}

}