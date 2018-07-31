<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/10
 * Time: 18:06
 */

namespace m\helpers;

use common\helpers\HelperBase;
use yii\data\Pagination;
use yii\db\Query;
use Yii;


class MemberHelper extends HelperBase
{
	/**
	 * 获取用户收支信息
	 * @param $param
	 * @return array
	 */
	public static function get_user_bill($param)
	{
		$year  = $param['year'];
		$month = $param['month'];

		if (isset($year) && isset($month)) {
			$start_month     = strtotime($year . '-' . $month);
			$start_month_day = date("t", $start_month);
			$end_month       = $start_month + $start_month_day * 24 * 3600;
		} else {
			$start_month = null;
			$end_month   = null;
		}

		$where = ['status' => 1, 'uid' => $param['uid'], 'type' => $param['type']];

		$count = (new Query())
			->select('*')
			->from('bb_51_income_pay')
			->where($where)
			->andWhere(['!=', 'title', '小帮快送-商品费用'])
			->andFilterWhere(['between', 'create_time', $start_month, $end_month])
			->orderBy('create_time desc')
			->count();

		$pages = new Pagination(['totalCount' => $count]);

		$page = 0;
		if ($param['page'] > 0) {
			$page = $param['page'] - 1;
		}

		$pages->page     = $page;
		$pages->pageSize = $param['pagesize'];

		$data = (new Query())
			->select('*')
			->from('bb_51_income_pay')
			->where($where)
			->andWhere(['!=', 'title', '小帮快送-商品费用'])
			->andFilterWhere(['between', 'create_time', $start_month, $end_month])
			->orderBy('create_time desc')
			->offset($pages->offset)
			->limit($pages->limit)
			->all();

		$type = intval($param['type']);

		$back_data = [];
		if ($data) {
			//第一页,并且存储的数据不相等时,清除session
			$session_str = 'month' . '-' . $type;
			if ($param['page'] == 1) {
				$session = Yii::$app->session;
				$session->remove($session_str);
			}

			foreach ($data as $key => $value) {
				$loop_year  = intval(date("Y", $value['create_time']));
				$loop_month = intval(date("m", $value['create_time']));

				//处理显示金额
				$back_data[$key] = (new Query())->select('id,create_time,title,type,money')->from('bb_51_income_pay')->where(['id' => $value['id']])->one();
				if ($back_data[$key]['type'] == 1) {
					$back_data[$key]['show_money'] = '+' . $back_data[$key]['money'];
				} else {
					$back_data[$key]['show_money'] = '-' . $back_data[$key]['money'];
				}

				//标志月份
				$session       = Yii::$app->session;
				$session_month = $session->get($session_str);

				if (isset($session_month)) {
					$arr_s_month = explode(',', $session_month);
					if (!in_array($loop_month, $arr_s_month)) {
						$session->set($session_str, $session_month . ',' . $loop_month);
						$back_data[$key]['month_key'] = 1;
					} else {
						$back_data[$key]['month_key'] = 0;
					}
				} else {
					$_SESSION[$session_str]       = $loop_month;
					$back_data[$key]['month_key'] = 1;
				}
				$back_data[$key]['month'] = $loop_month;
				$back_data[$key]['year']  = $loop_year;
			}
		}

		return $back_data;
	}

	/**
	 * 用户收支明细详情
	 * @param $income_id
	 * @return array|bool
	 */
	public static function get_income_bill_info_by_user($income_id)
	{
		$result = WalletHelper::getIncome($income_id);
		//图标
		$icon = WalletHelper::incomeUserStyle($result['income']['icon']);

		if ($result['order'] == null) {
			$html
				= '<tr>
                <td colspan="2" class="txt_center"><i class="' . $icon . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['income']['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['income']['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td class="content">交易完成</td>
				</tr>
				<tr>
					<td>创建时间</td>
					<td class="content">' . $result['income']['create_time'] . '</td>
				</tr>
				<tr>
					<td>交易单号</td>
					<td class="content">' . $result['income']['transaction_no'] . '</td>
				</tr>';

			if ($result['income']['trade_no']) {
				$html
					.= '<tr>
					<td>商户单号</td>
					<td class="content">' . $result['income']['trade_no'] . '</td>
				</tr>';
			}

			if ($result['many_orders'] == 1) {
				$html
					.= '<tr>
					<td>交易内容</td>
					<td><a href="find-orders?income_id=' . $income_id . '">' . '查看' . '</a></td>
				</tr>';
			}

			$html
				.= '<tr>
				<td colspan="2"></td>
			</tr>';
		} else {
			$html
				= '<tr>
                <td colspan="2" class="txt_center"><i class="' . $icon . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['income']['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['income']['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态:</td>
					<td class="content">交易完成</td>
				</tr>
				<tr>
					<td>创建时间</td>
					<td class="content">' . $result['income']['create_time'] . '</td>
				</tr>
				<tr>
					<td>交易单号</td>
					<td class="content">' . $result['income']['transaction_no'] . '</td>
				</tr>';

			if ($result['income']['trade_no']) {
				$html
					.= '<tr>
					<td>商户单号</td>
					<td class="content">' . $result['income']['trade_no'] . '</td>
				</tr>';
			}

			$html
				.= '<tr>
                   <td colspan="2" style="text-align: center;"><hr style="height:1px;border:none;border-top:1px dashed #C9C9C9;"></td>
                </tr>
				<tr>
					<td>' . '订单号' . '</td>
					<td class="content">' . $result['order']['order_no'] . '</td>
				</tr>
				<tr>
					<td>' . '下单时间' . '</td>
					<td class="content">' . $result['order']['order_time'] . '</td>
				</tr>
				<tr>
					<td>' . '订单金额' . '</td>
					<td class="content">￥' . $result['order']['order_amount'] . '</td>
				</tr>';

			if ($result['order']['discount'] != '0.00') {
				$html
					.= '<tr>
						<td>' . '订单优惠' . '</td>
						<td class="content">-￥' . $result['order']['discount'] . '</td>
					</tr>';
			}

			$html
				.= '<tr>
					<td>' . '实付金额' . '</td>
					<td class="content">￥' . $result['order']['amount_payable'] . '</td>
				</tr>';


			if ($result['order']['tips_fee'] != '0.00') {
				$html
					.= '<tr>
					<td>' . '小费金额' . '</td>
					<td class="content">￥' . $result['order']['tips_fee'] . '</td>
				</tr>';
			}

			$html
				.= '<tr>
					<td>' . '订单类型' . '</td>
					<td class="content">' . $result['order']['order_name'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
		}

		$extend = '<div class="txt_center p20 txt_blue f13 "><a href="http://51bangbang.udesk.cn/im_client/?web_plugin_id=43064" class="item-link item-content external">对此订单有疑问</a></div>';
		$result = ['html' => $html, 'extend' => $extend];

		return $result;
	}
}