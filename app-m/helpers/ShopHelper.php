<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/1/15
 * Time: 18:01
 */


namespace m\helpers;

use common\helpers\HelperBase;
use yii\data\Pagination;
use yii\db\Query;
use Yii;


class ShopHelper extends HelperBase
{
	/**
	 * 获取小帮收支明细详情
	 * @param $income_id
	 * @return array
	 *
	 */
	public static function getIncomeBillFromUser($income_id)
	{
		$result = WalletHelper::getShopIncomeDetail($income_id);
		//END
		$extend = '';
		//展示数据

		switch ($result['type']) {
			case 1:
				//订单收款
				$html
						= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>已存入钱包</td>
				</tr>
				<tr>
					<td>订单号</td>
					<td>' . $result['order_id'] . '</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td>订单类型</td>
					<td>' . $result['cate_name'] . '</td>
				</tr>
				<tr>
					<td>说明</td>
					<td>' . $result['title'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				$extend = '<div class="txt_center p20 txt_blue f13 "><a href="http://51bangbang.udesk.cn/im_client/?web_plugin_id=43064" class="item-link item-content external">对此订单有疑问</a></div>';
				break;
			case 2:
				try {
					//$drawInfo = M('51_retixian') ->field("trade_no,t_money,brankname,zfbname,zfbnum") -> where(['id'=>$result['order_id']]) -> find();
					$drawInfo = (new Query())->select('*')->from('bb_51_retixian')->where(['id' => $result['order_id']])->one();
				}
				catch (\Exception $e) {
					$drawInfo = ['trade_no' => '', 't_money' => $result['money'], 'brankname' => '', 'zfbname' => '', 'zfbnum' => ''];
				}
				//提现
				if ($result['status'] == 1) {
					$DrawExtend
							= '
									<tr>
									<td>&nbsp;</td><td></td>
									</tr>
									<tr>
										<td>到账订单号</td>
										<td>' . $drawInfo['trade_no'] . '</td>
									</tr>
									<tr>
										<td>到账时间</td>
										<td>' . $result['update_time_data'] . '</td>
									</tr>
									<tr>
										<td>返还金额</td>
										<td>￥' . $drawInfo['t_money'] . '</td>
									</tr>
									';
					$extend = '<div class="txt_center p20 txt_red f13 ">款项到账,存在延时,最迟24小时到账。</div>';

				} else {
					$DrawExtend = '';
				}

				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>' . $result['list_statusname'] . '</td>
				</tr>
				<tr>
					<td>订单号</td>
					<td>' . $result['order_id'] . '</td>
				</tr>
				<tr>
					<td>创建时间</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td>收款类型</td>
					<td>' . $drawInfo['brankname'] . '</td>
				</tr>
				<tr>
					<td>收款用户名</td>
					<td>' . $drawInfo['zfbname'] . '</td>
				</tr>
				<tr>
					<td>收款卡号</td>
					<td>' . $drawInfo['zfbnum'] . '</td>
				</tr>' . $DrawExtend . '
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 3:
				//平台赠送
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>已存入钱包</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 4:
				//平台回收
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>已回收</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 5:
				//红包发放
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>支付成功</td>
				</tr>
				<tr>
					<td>支付方式</td>
					<td>余额支付</td>
				</tr>
				<tr>
					<td>订单号</td>
					<td>' . $result['order_id'] . '</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 6:
				//红包退回
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>已存入钱包</td>
				</tr>
				<tr>
					<td>订单号</td>
					<td>' . $result['order_id'] . '</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 7:
				//解冻保证金
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>解冻成功</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 8 :
				//交纳保证金
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>支付成功</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 9:
				//在线宝转入余额
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>已存入钱包</td>
				</tr>
				<tr>
					<td>订单号</td>
					<td>' . $result['order_id'] . '</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';

				break;
			case 10 :
				//余额支付保证金
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>支付成功</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 11 :
				//保险扣费
				$html
					= '<tr>
					<td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态</td>
					<td>支付成功</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
			case 12:
				//全民营销
				$html
					= '<tr>
                <td colspan="2" class="txt_center"><i class="' . $result['list_style'] . ' f32"></i><span style="padding-left: 10px;top: -5px;position: relative;" class="txt_666">' . $result['title'] . '</span></td>
				</tr>
				<tr>
					<td colspan="2" class="txt_center f32 txt_333">' . $result['money'] . '</td>
				</tr>
				<tr>
					<td>当前状态:</td>
					<td>已存入钱包</td>
				</tr>
				<tr>
					<td>说明</td>
					<td>' . $result['title'] . '</td>
				</tr>
				<tr>
					<td>' . '创建时间' . '</td>
					<td>' . $result['create_time_date'] . '</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>';
				break;
		}

		//END

		return ['html' => $html, 'extend' => $extend];
	}

	/**
	 * 获取小帮账单列表
	 * @param $params
	 * @return array
	 */
	public static function get_shop_bill($params)
	{
		$year  = $params['year'];
		$month = $params['month'];

		if (isset($year) && isset($month)) {
			$start_month     = strtotime($year . '-' . $month);
			$start_month_day = date("t", $start_month);
			$end_month       = $start_month + $start_month_day * 24 * 3600;
		} else {
			$start_month = null;
			$end_month   = null;
		}

		$type = intval($params['type']);

		$count = (new Query())
			->select('*')
			->from('bb_51_income_shop')
			->where(['account_type' => $params['type'], 'staffuid' => intval($params['uid'])])
			->andFilterWhere(['between', 'create_time', $start_month, $end_month])
			->orderBy('create_time desc')
			->count();

		$pages = new Pagination(['totalCount' => $count]);

		$page = 0;
		if ($params['page'] > 0) {
			$page = $params['page'] - 1;
		}
		$pages->page     = $page;
		$pages->pageSize = $params['pagesize'];

		$data = (new Query())
			->select('*')
			->from('bb_51_income_shop')
			->where(['account_type' => $params['type'], 'staffuid' => intval($params['uid'])])
			->andFilterWhere(['between', 'create_time', $start_month, $end_month])
			->orderBy('create_time desc')
			->offset($pages->offset)
			->limit($pages->limit)
			->all();

		$back_data = [];
		if ($data) {
			$session = Yii::$app->session;
			//第一页,并且存储的数据不相等时,清除session
			$session_str = 'month' . '-' . $type;
			if ($params['page'] == 1) {
				$session->remove($session_str);
			}
			foreach ($data as $key => $value) {
				$loop_year       = intval(date("Y", $value['create_time']));
				$loop_month      = intval(date("m", $value['create_time']));
				$back_data[$key] = WalletHelper::getShopIncomeDetail($value['id']);

				//标志月份
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
					$session->set($session_str, $loop_month);
					$back_data[$key]['month_key'] = 1;
				}
				$back_data[$key]['month'] = $loop_month;
				$back_data[$key]['year']  = $loop_year;
				//end
			}
		}
		return $back_data;
	}
}