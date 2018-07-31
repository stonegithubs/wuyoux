<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/30
 */

namespace common\helpers\utils;


use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\shop\ShopHelper;
use yii\db\Query;

class DocumentHelper extends HelperBase
{

	/**
	 * 获取说明文档
	 * @param $document_type
	 *
	 * @return bool
	 */
	public static function getDocument($document_type)
	{
		$result = false;
		switch ($document_type) {
			case Ref::DOCUMENT_ERRAND_PROTOCOL:
				$result['html'] = self::errandProtocol();
				break;
			case Ref::DOCUMENT_PROVIDER_USE:
				$result['html'] = self::providerUse();
				break;
			case Ref::DOCUMENT_PROVIDER_ENTER:
				$result['html'] = self::providerEnter();
				break;
			case Ref::DOCUMENT_PROVIDER_AGREEMENT:
				$result['html'] = self::providerAgreement();
				break;
			case Ref::DOCUMENT_USER_USE:
				$result['html'] = self::userUse();
				break;
			case Ref::DOCUMENT_WY_ABOUT:
				$about_data     = (new Query())->from("bb_document as bd")->select(['bda.content'])->leftJoin("bb_document_article as bda", "bd.id = bda.id")->where(['bd.name' => 'ABOUTWUYOUBANGBAGN', 'bd.status' => 1])->one();
				$result['html'] = isset($about_data['content']) ? $about_data['content'] : null;
				break;
			case Ref::DOCUMENT_WY_DECLARATION:
				$about_data     = (new Query())->from("bb_document as bd")->select(['bda.content'])->leftJoin("bb_document_article as bda", "bd.id = bda.id")->where(['bd.name' => 'WUYOUBANGBANGDECLARE', 'bd.status' => 1])->one();
				$result['html'] = isset($about_data['content']) ? $about_data['content'] : null;
				break;
			case Ref::DOCUMENT_CASH_WITHDRAWAL:
				$result['html'] = self::cashWithdrawal();
				break;
			case Ref::DOCUMENT_MATCH_CARD_RULE:
				$result['html'] = self::matchCardRule();
				break;
			case Ref::DOCUMENT_RECHARGE_RULE:
				$result['html'] = self::rechargeRule();
				break;
			default:
				break;

		}

		return $result;
	}

	public static function errandProtocol()
	{
		return $result['html']
			= '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>配送服务说明</title>
			</head>
			<body style="padding:20px;">
				<div class="">
					<h1 style="font-size:14px;margin-top: 1.5em;color: #666666; text-align:center">配送服务说明</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">用户申请成为小帮之前，应当仔细阅读本协议。本协议将成为您和帮帮之间具有法律效应的文件。
				</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">用户勾选“我同意《帮帮商家协议》”并通过培训、考核，经帮帮审核成功认证成为帮帮小帮后，本协议条款即构成了对双方具有法律约束力的文件。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">商店管理者/服务者应是具备完全民事行为能力的自然人。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">小帮申请注册并经帮帮审核通过后，通过帮帮信息自主选择接受、完成任务事项，并在任务事项完成后获得相应报酬。小帮自愿利用闲暇时间并根据自己的行程安排，自主选择是否接受帮帮信息上的任务事项信息，为帮帮用户提供服务完成任务事项。
				</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">帮帮作为信息发布平台，仅为平台用户提供信息服务，供用户自主选择发布、接受任务事项信息，不对任务事项信息的真实性或准确性及所涉物品的质量、安全或合法性等提供担保。
				</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">用户应自行谨慎判断确定相关信息的真实性、合法性和有效性，并自行承担因此产生的责任与损失。用户对本平台上任何信息资料的选择、接受，取决于用户自己，并由其自行承担所有风险和责任。
				</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">一、协议条款的确认和约束</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 用户在申请小帮认证之前必须认真阅读本协议全部条款内容，如对条款有任何疑问的，可向帮帮客服咨询。用户提交认证申请，即表示已阅读并接受本协议所有内容。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 用户必须完全同意所有协议条款并完成认证、培训程序，才能成为帮帮的认证小帮。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 用户需保证提交信息的真实性、合法性，并对其负全部责任。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 当用户在线下提供服务的同时，用户也承认了用户拥有相应的服务能力和行为能力。用户对提供或接受的任务事项的真实性、合法性负责，并能够独立承担法律责任。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">二、小帮认证申请条件</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 用户申请服务者认证时，必须年满18周岁。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 用户需身体健康，行动自如、无传染疾病、无听觉或视觉障碍，心智健康，能完整操作手机APP、语言表达自如，具备相应民事行为能力，否则将不予审核、快服务。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 用户提交认证申请时，需提供真实身份信息，包括但不限于身份证、机动车驾驶证等合法有效证件</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 若用户认证过程中（包括但不限于其所提交信息）存在欺骗、刻意隐瞒等行为，一切因此而导致的后果全部由用户承担，快服务不承担任何责任（或连带责任），并且保留单方面对该用户账号及其线上信息采取（包括但不限于）注销、封禁、限制使用等的权利。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">三、小帮着装与装备</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 帮帮不提供包括工服、工作证、交通工具、智能终端等设备在内的任何实物装备。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 小帮通过快为其他用户提供服务时，着装应大方得体。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 小帮自备智能手机安装快服务APP，并保证手机畅通，在平台上接单时开启网络连接、GPS定位，以方便用户对任务事项跟踪，以及快进行服务辅助与实时监管。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 小帮在保证自身安全与及时完成任务情况下，需自备相应交通工具，步行、自行车、电动车、摩托车、乘坐公共交通工具、驾车等皆可。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">四、双方的权利与义务</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 帮帮根据客户要求，合理调配取送任务：小帮依据帮帮操作手册完成平台任务，并有义务向客户推广帮帮的服务和产品。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 若小帮违反操作流程及规范，造成不良影响的，帮帮有权单方终止双方的合作关系。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 在使用过程中出现问题，小帮须及时向帮帮客服反馈并协助解决处理。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 在配送过程中因服务者造成的货物丢失、毁损，服务者承担全部责任。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">5. 小帮取件时须提醒客户不得接受国家明令禁止递送的物品。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">6. 如遇涉及公安机关或相应主管部门介入调查等情况出现，帮帮有权利、有义务配合相关部门提供小帮的相关身份信息。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">7. 不允许利用帮帮资源侵害帮帮客户的利益，如有发现，一经核实，严肃处理，帮帮保留终止与小帮的服协议并诉诸法律的权利。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">8. 如小帮充当用户或伙同不良用户通过下假单等恶意手段，非法牟取公司补贴（优惠券等）、奖励等行为，一经发现立即冻结系统账户，并处以相应罚款（第一次扣除收益 50 元，第二次扣除收益 100 元，第三次解除合作关系）。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">五、余额提现</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 小帮钱包余额大于（包括）10元时方可提现，具体提现金额不限。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 在“商家收入”里发起提现，帮帮核实订单与提现信息后，将在最近一个提现日予以转账。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 每周二周五为快服务统一提现日，所有提现申请统一在提现日处理。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">六、余额提现</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. “接私单”的定义：通过快服务平台获取相关信息后，绕开平台私下交易。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 严禁接私单行为，包括接私单</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 发现接私单行为，平台和各地分站将严肃处理，处罚措施包括但不限于不同程度的封号、罚款等，具体以各地分站发布信息为准。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">七、其他说明事项</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 平台从订单价格中抽取一定比例的费用，作为平台服务费，具体比例以各城市实际设定为准。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 随着市场的变化以及竞争情况的改变，甲方有权合理调整每单的分成比例。</p>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 小帮接单时间自由，但必须完成所有已抢订单，方可继续接下一个订单。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">八、小帮提供服务过程中出现意外情况</h1>
					<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 帮帮平台仅为用户提供信息服务，不承担服务者在提供服务过程中出现的任何情况而导致的任何损失、赔付等责任。</p>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">九、小帮工作过程中如遇突发情况，无法继续完成订单时，应及时向平台反馈。</h1>
					<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">十、服务者同意本协议的同时，还应遵守快发布的服务者手册、管理条例等其他规定与文件。</h1>
		
				</div>
			</body>
			</html>';
	}

	public static function providerUse()
	{
		return $result['html']
			= '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>小帮使用手册</title>
				<style>
					body{ padding: 20px; margin: 0;}
					h1{font-size:15px;margin-top: 1.6em;color: #666666; text-align:center;}
					h2{font-size:14px;margin-top: 1.6em;color: #666666;}
					p{font-size: 14px;line-height: 24px;color: #999999; text-indent:2em}
				</style>
			</head>
			<body >
				<P>用户申请成为小帮之前，应当仔细阅读本协议。本协议将成为您和无忧帮帮之间具有法律效应的文件。无忧帮帮以下简称“帮帮”，无忧帮帮商家以下简称“小帮”。</P>
				<p>用户勾选“我同意《帮帮商家协议》”并通过培训、考核，经帮帮审核成功认证成为帮帮小帮后，本协议条款即构成了对双方具有法律约束力的文件。</p>
				<p>小帮应是具备完全民事行为能力的自然人。</p>
				<p>小帮申请注册并经帮帮审核通过后，通过帮帮信息自主选择接受、完成任务事项，并在任务事项完成后获得相应报酬。小帮自愿利用闲暇时间并根据自己的行程安排，自主选择是否接受帮帮信息上的任务事项信息，为帮帮用户提供服务完成任务事项。</p>
				<p>帮帮作为信息发布平台，仅为平台用户提供信息服务，供用户自主选择发布、接受任务事项信息，不对任务事项信息的真实性或准确性及所涉物品的质量、安全或合法性等提供担保。</p>
				<p>用户应自行谨慎判断确定相关信息的真实性、合法性和有效性，并自行承担因此产生的责任与损失。用户对本平台上任何信息资料的选择、接受，取决于用户自己，并由其自行承担所有风险和责任。</p>
				<h2>一、协议条款的确认和约束</h2>
				<p>1. 用户在申请小帮认证之前必须认真阅读本协议全部条款内容，如对条款有任何疑问的，可向帮帮客服咨询。用户提交认证申请，即表示已阅读并接受本协议所有内容。</p>
				<p>2. 用户必须完全同意所有协议条款并完成认证、培训程序，才能成为帮帮的认证小帮。</p>
				<p>3. 用户需保证提交信息的真实性、合法性，并对其负全部责任。</p>
				<p>4. 当用户在线下提供服务的同时，用户也承认了用户拥有相应的服务能力和行为能力。用户对提供或接受的任务事项的真实性、合法性负责，并能够独立承担法律责任。</p>
				<h2>二、小帮认证申请条件</h2>
				<p>1. 用户申请小帮认证时，必须年满18周岁。</p>
				<p>2. 用户需身体健康，行动自如、无传染疾病、无听觉或视觉障碍，心智健康，能完整操作手机。App、语言表达自如，具备相应民事行为能力，否则将不予审核。</p>
				<p>3. 用户提交认证申请时，需提供真实身份信息，包括但不限于身份证、机动车驾驶证等合法有效证件。</p>
				<p>4. 若用户认证过程中（包括但不限于其所提交信息）存在欺骗、刻意隐瞒等行为，一切因此而导致的后果全部由用户承担，帮帮不承担任何责任（或连带责任），并且保留单方面对该用户账号及其线上信息采取（包括但不限于）注销、封禁、限制使用等的权利。</p>
				<h2>三、小帮着装与装备</h2>
				<p>1. 帮帮不提供包括工服、工作证、交通工具、智能终端等设备在内的任何实物装备。</p>
				<p>2. 小帮通过帮帮为其他用户提供服务时，着装应大方得体。</p>
				<P>3. 小帮自备智能手机安装帮帮App，并保证手机畅通，在平台上接单时开启网络连接、GPS定位，以方便用户对任务事项跟踪，以及进行服务辅助与实时监管。</P>
				<P>4. 小帮在保证自身安全与及时完成任务情况下，需自备相应交通工具，步行、自行车、电动车、摩托车、乘坐公共交通工具、驾车等皆可。</P>
				<h2>四、双方的权利与义务</h2>
				<p>1. 帮帮根据客户要求，合理调配取送任务：小帮依据帮帮操作手册完成平台任务，并有义务向客户推广帮帮的服务和产品。</p>
				<p>2. 若小帮违反操作流程及规范，造成不良影响的，帮帮有权单方终止双方的合作关系。</p>
				<p>3. 在使用过程中出现问题，小帮须及时向帮帮客服反馈并协助解决处理。</p>
				<p>4. 在配送过程中因小帮造成的货物丢失、毁损，服务者承担全部责任。</p>
				<P>5. 小帮取件时须提醒客户不得接受国家明令禁止递送的物品。</P>
				<p>6. 如遇涉及公安机关或相应主管部门介入调查等情况出现，帮帮有权利、有义务配合相关部门提供小帮的相关身份信息。</p>
				<p>7. 不允许利用帮帮资源侵害帮帮客户的利益，如有发现，一经核实，严肃处理，帮帮保留终止与小帮的服务协议并诉诸法律的权利。</p>
				<p>8. 如小帮充当用户或伙同不良用户通过下假单等恶意手段，非法牟取公司补贴（优惠券等）、奖励等行为，一经发现立即冻结系统账户，并处以相应罚款（第一次扣除收益 50 元，第二次扣除收益 100 元，第三次解除合作关系）。</p>
				<h2>五、余额提现</h2>
				<p>1. 小帮钱包余额大于（包括）10元时方可提现，具体提现金额不限。</p>
				<p>2. 在“小帮收入”里发起提现，帮帮核实订单与提现信息后，将在最近一个提现日予以转账。</p>
				<p>3. 每周二周五为帮帮统一提现日，所有提现申请统一在提现日处理。</p>
				<h2>六、禁止行为</h2>
				<p>1. 严禁从平台获取订单信息后，通过恶意完成订单或恶意取消订单的方式，私自与接受服务一方交易。</p>
				<p>2. 发现上述行为，平台和各地分站将严肃处理，处罚措施包括但不限于不同程度的封号、罚款等，具体以各地分站发布信息为准。</p>
				<h2>七、其他说明事项</h2>
				<P>1. 平台从订单价格中抽取一定比例的费用，作为平台服务费，具体比例以各城市实际设定为准。</P>
				<p>2. 随着市场的变化以及竞争情况的改变，甲方有权合理调整每单的分成比例。</p>
				<p>3. 小帮接单时间自由，但必须完成所有已抢订单，方可继续接下一个订单。</p>
				<h2>八、小帮提供服务过程中出现意外情况</h2>
				<p>帮帮平台仅为用户提供信息服务，不承担服务者在提供服务过程中出现的任何情况而导致的任何损失、赔付等责任。</p>
				<h2>九、小帮工作过程中如遇突发情况，无法继续完成订单时，应及时向平台反馈。</h2>
				<h2>十、服务者同意本协议的同时，还应遵守服务者手册、管理条例等其他规定与文件。</h2>
					
			</body>
			</html>';
	}

	public static function providerEnter()
	{
		return $result['html']
			= '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>小帮入驻协议</title>
			</head>
			<body style="padding:20px;">
				<p class="MsoNormal" align="left" style="text-align:left;text-indent:20.0pt;">
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						用户申请成为小帮之前，应当仔细阅读本协议。本协议将成为您和无忧帮帮之间具有法律效应的文件。无忧帮帮以下简称“帮帮”，无忧帮帮商家以下简称“小帮”。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						用户勾选“我同意《帮帮商家协议》”并通过培训、考核，经帮帮审核成功认证成为帮帮小帮后，本协议条款即构成了对双方具有法律约束力的文件。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						小帮应是具备完全民事行为能力的自然人。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						小帮申请注册并经帮帮审核通过后，通过帮帮信息自主选择接受、完成任务事项，并在任务事项完成后获得相应报酬。小帮自愿利用闲暇时间并根据自己的行程安排，自主选择是否接受帮帮信息上的任务事项信息，为帮帮用户提供服务完成任务事项。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						帮帮作为信息发布平台，仅为平台用户提供信息服务，供用户自主选择发布、接受任务事项信息，不对任务事项信息的真实性或准确性及所涉物品的质量、安全或合法性等提供担保。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						用户应自行谨慎判断确定相关信息的真实性、合法性和有效性，并自行承担因此产生的责任与损失。用户对本平台上任何信息资料的选择、接受，取决于用户自己，并由其自行承担所有风险和责任。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>一、</b><b>协议</b><b>条款的确</b><b>认</b><b>和</b><b>约</b><b>束</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1.&nbsp;用户在申请小帮认证之前必须认真阅读本协议全部条款内容，如对条款有任何疑问的，可向帮帮客服咨询。用户提交认证申请，即表示已阅读并接受本协议所有内容。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;用户必须完全同意所有协议条款并完成认证、培训程序，才能成为帮帮的认证小帮。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						3.&nbsp;用户需保证提交信息的真实性、合法性，并对其负全部责任。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						4.&nbsp;当用户在线下提供服务的同时，用户也承认了用户拥有相应的服务能力和行为能力。用户对提供或接受的任务事项的真实性、合法性负责，并能够独立承担法律责任。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>二、小帮</b><b>认证</b><b>申</b><b>请</b><b>条件</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1.&nbsp;用户申请小帮认证时，必须年满18周岁。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;用户需身体健康，行动自如、无传染疾病、无听觉或视觉障碍，心智健康，能完整操作手机。App、语言表达自如，具备相应民事行为能力，否则将不予审核。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						3.&nbsp;用户提交认证申请时，需提供真实身份信息，包括但不限于身份证、机动车驾驶证等合法有效证件。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						4.&nbsp;若用户认证过程中（包括但不限于其所提交信息）存在欺骗、刻意隐瞒等行为，一切因此而导致的后果全部由用户承担，帮帮不承担任何责任（或连带责任），并且保留单方面对该用户账号及其线上信息采取（包括但不限于）注销、封禁、限制使用等的权利。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>三、小帮着装与装</b><b>备</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1.&nbsp;帮帮不提供包括工服、工作证、交通工具、智能终端等设备在内的任何实物装备。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;小帮通过帮帮为其他用户提供服务时，着装应大方得体。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						3.&nbsp;小帮自备智能手机安装帮帮App，并保证手机畅通，在平台上接单时开启网络连接、GPS定位，以方便用户对任务事项跟踪，以及进行服务辅助与实时监管。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						4.&nbsp;小帮在保证自身安全与及时完成任务情况下，需自备相应交通工具，步行、自行车、电动车、摩托车、乘坐公共交通工具、驾车等皆可。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>四、双方的</b><b>权</b><b>利与</b><b>义务</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1.&nbsp;帮帮根据客户要求，合理调配取送任务：小帮依据帮帮操作手册完成平台任务，并有义务向客户推广帮帮的服务和产品。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;若小帮违反操作流程及规范，造成不良影响的，帮帮有权单方终止双方的合作关系。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						3.&nbsp;在使用过程中出现问题，小帮须及时向帮帮客服反馈并协助解决处理。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						4.&nbsp;在配送过程中因小帮造成的货物丢失、毁损，服务者承担全部责任。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						5.&nbsp;小帮取件时须提醒客户不得接受国家明令禁止递送的物品。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						6.&nbsp;如遇涉及公安机关或相应主管部门介入调查等情况出现，帮帮有权利、有义务配合相关部门提供小帮的相关身份信息。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						7.&nbsp;不允许利用帮帮资源侵害帮帮客户的利益，如有发现，一经核实，严肃处理，帮帮保留终止与小帮的服务协议并诉诸法律的权利。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						8. 如小帮充当用户或伙同不良用户通过下假单等恶意手段，非法牟取公司补贴（优惠券等）、奖励等行为，一经发现立即冻结系统账户，并处以相应罚款（第一次扣除收益&nbsp;50&nbsp;元，第二次扣除收益&nbsp;100&nbsp;元，第三次解除合作关系）。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>五、余</b><b>额</b><b>提</b><b>现</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1.&nbsp;小帮钱包余额大于（包括）10元时方可提现，具体提现金额不限。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;在“小帮收入”里发起提现，帮帮核实订单与提现信息后，将在最近一个提现日予以转账。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						3.&nbsp;每周二周五为帮帮统一提现日，所有提现申请统一在提现日处理。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>六、</b><b>禁止行为</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1. 严禁从平台获取订单信息后，通过恶意完成订单或恶意取消订单的方式，私自与接受服务一方交易。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;发现上述行为，平台和各地分站将严肃处理，处罚措施包括但不限于不同程度的封号、罚款等，具体以各地分站发布信息为准。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>七、其他</b><b>说</b><b>明事</b><b>项</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						1.&nbsp;平台从订单价格中抽取一定比例的费用，作为平台服务费，具体比例以各城市实际设定为准。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						2.&nbsp;随着市场的变化以及竞争情况的改变，甲方有权合理调整每单的分成比例。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						3.&nbsp;小帮接单时间自由，但必须完成所有已抢订单，方可继续接下一个订单。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>八、小帮提供服</b><b>务过</b><b>程中出</b><b>现</b><b>意外情况</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;text-indent:24pt;">
						帮帮平台仅为用户提供信息服务，不承担服务者在提供服务过程中出现的任何情况而导致的任何损失、赔付等责任。
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>九、小帮工作</b><b>过</b><b>程中如遇突</b><b>发</b><b>情况，无法</b><b>继续</b><b>完成</b><b>订单时</b><b>，</b><b>应</b><b>及</b><b>时</b><b>向平台反</b><b>馈</b><b>。</b><b></b>
					</p>
					<p class="MsoNormal" align="left" style="text-align:left;">
						<b>十、服</b><b>务</b><b>者同意本</b><b>协议</b><b>的同</b><b>时</b><b>，</b><b>还应</b><b>遵守服</b><b>务</b><b>者手册、管理条例等其他</b><b>规</b><b>定与文件。</b><b></b>
					</p>
					<p class="MsoNormal">
						<span>&nbsp;</span>
					</p>
				</p>
			</body>
			</html>';
	}

	public static function providerAgreement()
	{
		return $result['html']
			= '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>小帮入驻协议</title>

			</head>
			<body style="padding:20px;">
			<div class="">
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">用户申请成为小帮之前，应当仔细阅读本协议。本协议将成为您和帮帮之间具有法律效应的文件。
			</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">用户勾选“我同意《帮帮商家协议》”并通过培训、考核，经帮帮审核成功认证成为帮帮小帮后，本协议条款即构成了对双方具有法律约束力的文件。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">商店管理者/服务者应是具备完全民事行为能力的自然人。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">小帮申请注册并经帮帮审核通过后，通过帮帮信息自主选择接受、完成任务事项，并在任务事项完成后获得相应报酬。小帮自愿利用闲暇时间并根据自己的行程安排，自主选择是否接受帮帮信息上的任务事项信息，为帮帮用户提供服务完成任务事项。
			</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">帮帮作为信息发布平台，仅为平台用户提供信息服务，供用户自主选择发布、接受任务事项信息，不对任务事项信息的真实性或准确性及所涉物品的质量、安全或合法性等提供担保。
			</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">用户应自行谨慎判断确定相关信息的真实性、合法性和有效性，并自行承担因此产生的责任与损失。用户对本平台上任何信息资料的选择、接受，取决于用户自己，并由其自行承担所有风险和责任。
			</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">一、协议条款的确认和约束</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 用户在申请小帮认证之前必须认真阅读本协议全部条款内容，如对条款有任何疑问的，可向帮帮客服咨询。用户提交认证申请，即表示已阅读并接受本协议所有内容。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 用户必须完全同意所有协议条款并完成认证、培训程序，才能成为帮帮的认证小帮。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 用户需保证提交信息的真实性、合法性，并对其负全部责任。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 当用户在线下提供服务的同时，用户也承认了用户拥有相应的服务能力和行为能力。用户对提供或接受的任务事项的真实性、合法性负责，并能够独立承担法律责任。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">二、小帮认证申请条件</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 用户申请服务者认证时，必须年满18周岁。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 用户需身体健康，行动自如、无传染疾病、无听觉或视觉障碍，心智健康，能完整操作手机APP、语言表达自如，具备相应民事行为能力，否则将不予审核、快服务。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 用户提交认证申请时，需提供真实身份信息，包括但不限于身份证、机动车驾驶证等合法有效证件</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 若用户认证过程中（包括但不限于其所提交信息）存在欺骗、刻意隐瞒等行为，一切因此而导致的后果全部由用户承担，快服务不承担任何责任（或连带责任），并且保留单方面对该用户账号及其线上信息采取（包括但不限于）注销、封禁、限制使用等的权利。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">三、小帮着装与装备</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 帮帮不提供包括工服、工作证、交通工具、智能终端等设备在内的任何实物装备。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 小帮通过快为其他用户提供服务时，着装应大方得体。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 小帮自备智能手机安装快服务APP，并保证手机畅通，在平台上接单时开启网络连接、GPS定位，以方便用户对任务事项跟踪，以及快进行服务辅助与实时监管。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 小帮在保证自身安全与及时完成任务情况下，需自备相应交通工具，步行、自行车、电动车、摩托车、乘坐公共交通工具、驾车等皆可。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">四、双方的权利与义务</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 帮帮根据客户要求，合理调配取送任务：小帮依据帮帮操作手册完成平台任务，并有义务向客户推广帮帮的服务和产品。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 若小帮违反操作流程及规范，造成不良影响的，帮帮有权单方终止双方的合作关系。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 在使用过程中出现问题，小帮须及时向帮帮客服反馈并协助解决处理。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">4. 在配送过程中因服务者造成的货物丢失、毁损，服务者承担全部责任。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">5. 小帮取件时须提醒客户不得接受国家明令禁止递送的物品。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">6. 如遇涉及公安机关或相应主管部门介入调查等情况出现，帮帮有权利、有义务配合相关部门提供小帮的相关身份信息。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">7. 不允许利用帮帮资源侵害帮帮客户的利益，如有发现，一经核实，严肃处理，帮帮保留终止与小帮的服协议并诉诸法律的权利。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">8. 如小帮充当用户或伙同不良用户通过下假单等恶意手段，非法牟取公司补贴（优惠券等）、奖励等行为，一经发现立即冻结系统账户，并处以相应罚款（第一次扣除收益 50 元，第二次扣除收益 100 元，第三次解除合作关系）。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">五、余额提现</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 小帮钱包余额大于（包括）10元时方可提现，具体提现金额不限。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 在“商家收入”里发起提现，帮帮核实订单与提现信息后，将在最近一个提现日予以转账。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 每周二周五为快服务统一提现日，所有提现申请统一在提现日处理。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">六、余额提现</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. “接私单”的定义：通过快服务平台获取相关信息后，绕开平台私下交易。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 严禁接私单行为，包括接私单</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 发现接私单行为，平台和各地分站将严肃处理，处罚措施包括但不限于不同程度的封号、罚款等，具体以各地分站发布信息为准。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">七、其他说明事项</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 平台从订单价格中抽取一定比例的费用，作为平台服务费，具体比例以各城市实际设定为准。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">2. 随着市场的变化以及竞争情况的改变，甲方有权合理调整每单的分成比例。</p>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">3. 小帮接单时间自由，但必须完成所有已抢订单，方可继续接下一个订单。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">八、小帮提供服务过程中出现意外情况</h1>
				<p style="font-size: 12px;line-height: 24px;padding:0;color: #999999; text-indent:2em">1. 帮帮平台仅为用户提供信息服务，不承担服务者在提供服务过程中出现的任何情况而导致的任何损失、赔付等责任。</p>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">九、小帮工作过程中如遇突发情况，无法继续完成订单时，应及时向平台反馈。</h1>
				<h1 style="font-size:13px;margin-top: 1.5em;color: #666666;">十、服务者同意本协议的同时，还应遵守快发布的服务者手册、管理条例等其他规定与文件。</h1>
			   
					
			</div>
			</body>
			</html>';
	}

	public static function userUse()
	{
		return $result['html']
			= '<!DOCTYPE html>
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>用户使用说明</title>
		<style>
			body{ padding: 20px; margin: 0;}
			h1{font-size:15px;margin-top: 1.6em;color: #666666; text-align:center;}
			h2{font-size:14px;margin-top: 1.6em;color: #666666;}
			p{font-size: 14px;line-height: 24px;color: #999999; text-indent:2em}
		</style>
		</head>
		<body >
			<p>无忧帮帮平台由无忧帮帮（深圳）科技有限公司研发提供，平台所提供的各项服务的所有权和运营权，均归属于无忧帮帮（深圳）科技有限公司（以下简称“无忧帮帮”）所有。</p>
			<p>用户使用协议（以下简称“本协议”）系用户因使用无忧帮帮平台的各项服务，而与无忧帮帮平台订立的正式的、完整的协议。用户在平台注册，即表示接受本协议的所有条款和内容。用户部分或全部不接受本协议条款的，不得使用无忧帮帮平台或使用无忧帮帮平台所提供的服务。</p>
			<p>用户与无忧帮帮平台签订的书面合同与本协议不一致的，以书面合同为准。</p>
			<h2>一、定义</h2>
			<p>1.无忧帮帮平台：包括无忧帮帮App、冠以“无忧帮帮”字样的微信公众号与订阅号、无忧帮帮官方网站（http://www.51bangbang.com.cn）等，以及“无忧帮帮”更新和推出的其他平台，以下简称“无忧帮帮”。</p>
			<p>2.无忧帮帮平台服务：通过互联网以无忧帮帮平台网站、客户端、微信公众号等在内的各种形态（包括未来技术发展出现的新的服务形态）向用户提供的各项服务。</p>
			<p>3.无忧帮帮平台规则：包括（1）所有无忧帮帮平台规则频道内已经发布的，以及后续发布的全部规则、解读、公告等内容；（2）各平台上发布的各类规则、实施细则、产品流程说明、公告等；（3）以及上述规则的不时修订文本。</p>
			<p>4.用户：在无忧帮帮平台注册或使用无忧帮帮平台服务的主体，都是本协议所指用户。凡使用同一身份认证信息或经无忧帮帮排查认定多个无忧帮帮账户的实际控制人为同一人的，均视为同一用户。</p>
			<h2>二、协议条款的确认和约束</h2>
			<p>1.用户在使用无忧帮帮平台的服务之前必须认真阅读本协议全部条款内容，如对条款有任何疑问的，应向无忧帮帮咨询。用户注册、使用无忧帮帮平台的服务，即表示已阅读并接受本协议所有内容，不应以未阅读本协议条款的内容或者未获得无忧帮帮对用户问询的解答等理由，主张本协议条款无效，或要求撤销本协议条款。</p>
			<p>2.用户必须完全同意所有服务条款并完成注册程序，才能成为无忧帮帮的注册用户。在用户按照注册页面提示填写信息、阅读并同意本协议条款并完成全部注册程序后或以其他无忧帮帮允许的方式实际使用无忧帮帮平台的服务时，用户即受本协议条款的约束。</p>
			<p>3.用户须确认，在完成注册程序或以其他无忧帮帮允许的方式实际使用无忧帮帮平台的服务时，应当是具备相应民事行为能力的自然人、法人或其他组织。如果不具备前述主体资格（如用户在18周岁以下），只能在父母或监护人的监护参与下才能使用无忧帮帮创建或接受任务事项，且用户及其监护人应承担因此而导致的一切后果；无忧帮帮有权注销该用户的账户。</p>
			<p>4.当用户在线下提供或接受服务的同时，用户也承认了用户拥有相应的权利能力和行为能力。用户对提供或接受的任务事项的真实性、合法性负责，并能够独立承担法律责任。</p>
			<p>5.用户确认：本协议条款是处理双方权利义务的约定依据，除非违反国家强制性法律，否则始终有效。</p>
			<p>6.无忧帮帮保留在中华人民共和国法律允许的范围内单方决定拒绝服务、关闭用户账户或取消任务事项的权利。</p>
			<p>7.本协议内容包括协议正文及所有无忧帮帮已经发布的或将来可能发布的各类规则。所有规则为本协议不可分割的组成部分，与协议正文具有同等法律效力。除另行明确声明外，任何无忧帮帮平台的服务均适用于本协议，用户承诺接受并遵守本协议的约定。如果用户不同意本协议的约定，用户应立即停止注册程序或停止使用无忧帮帮平台的服务。</p>
			<h2>三、责任信息与相关限制</h2>
			<p>1.用户个人明确同意对网络服务的使用承担风险。无忧帮帮对此不作任何类型的担保，不论是明确的或隐含的。包括但不限于：不担保服务一定能满足用户的要求，也不担保服务不会受中断，对服务的及时性、安全性、出错发生等都不作担保；对在无忧帮帮上以及相关产品得到的任何服务或交易进程，不作担保；对平台服务所涉及的技术及信息的有效性、准确性、正确性、可靠性、稳定性、完整性和及时性不作出任何承诺和保证；不担保平台服务的适用性、无错误或疏漏、持续性、准确性、可靠性、适用于某一特定用途。</p>
			<p>2.用户理解并接受：无忧帮帮平台作为信息发布、服务平台，无法控制每一任务事项所涉及的物品的质量、安全或合法性，任务事项内容的真实性或准确性，以及任务事项所涉各方履行任务事项的能力。用户应自行谨慎判断确定相关信息的真实性、合法性和有效性，并自行承担因此产生的责任与损失。任何信息资料（下载或通过无忧帮帮平台服务取得），取决于用户自己并由其承担系统受损或资料丢失的所有风险和责任。</p>
			<p>3.除非法律法规明确要求、司法行政等政府机关要求，或出现以下情况，否则，无忧帮帮没有义务对所有用户的信息数据、服务信息、任务发布行为以及与任务事项有关的其它事项进行事先审查：</p>
			<p>（1）无忧帮帮有合理的理由认为特定用户及具体任务事项可能存在重大违法或违约情形。</p>
			<p>（2）无忧帮帮有合理的理由认为用户在无忧帮帮平台的行为涉嫌违法或有违道德等不当情形。</p>
			<p>4.用户了解并同意，无忧帮帮不对因下述情况而导致的任何损害赔偿承担责任，包括但不限于利润、商誉、使用、数据等方面的损失或其它无形损失的损害赔偿：</p>
			<p>（1）使用或未能使用无忧帮帮平台的服务；</p>
			<p>（2）第三方未经批准地使用用户的账户或更改用户的数据；</p>
			<p>（3）通过无忧帮帮平台的服务购买或获取任何商品、样品、数据、信息等行为或替代行为产生的费用及损失；</p>
			<p>（4）用户对无忧帮帮平台的服务的误解；</p>
			<p>（5）任何非因无忧帮帮的原因而引起的与无忧帮帮平台的服务有关的其它损失。</p>
			<p>5.如因不可抗力或其他无忧帮帮无法控制的原因使无忧帮帮系统崩溃或无法正常使用导致网上交易无法完成或丢失有关的信息、记录等，无忧帮帮不承担责任。但是无忧帮帮会尽可能合理地协助处理善后事宜，并努力使客户免受经济损失。</p>
			<p>6. 用户同意在发现无忧帮帮平台任何内容不符合法律规定，或不符合本用户协议条款规定的，有义务及时通知无忧帮帮。如果用户发现个人信息被盗用或者其他权利被侵害，请将此情况告知无忧帮帮并同时提供如下信息和材料：</p>
			<p>（1）侵犯用户权利的信息的网址，编号或其他可以找到该信息的细节；</p>
			<p>（2）用户的联系方式，包括联系人姓名，地址，电话号码和电子邮件；</p>
			<p>（3）用户的身份证复印件、营业执照等其他相关资料。</p>
			<p>7. 经审查得到证实的，我们将及时删除相关信息。我们仅接受邮寄、电子邮件或传真方式的书面侵权通知。情况紧急的，用户可以通过客服电话先行告知，我们会视情况采取相应措施。</p>
			<p>8.用户应当严格遵守本协议及无忧帮帮发布的其他协议条款、活动规则，因用户违反协议或规则的行为给第三方、或无忧帮帮造成损失的，用户应当承担全部责任。</p>
			<h2>四、平台使用规范</h2>
			<p> 1.用户无论是作为信息方发布方或接收方，须严格按照无忧帮帮平台提供、发布、更新的服务条款和业务规则执行。无忧帮帮平台不时发布的成文或非成文的业务规则，以及既成的交易模式或惯例，视为本协议的重要组成部分。</p>
			<p>2.在使用无忧帮帮平台服务过程中，用户承诺遵守以下约定：</p>
			<p>（1）在使用无忧帮帮平台的服务过程中实施的所有行为均遵守国家法律、法规等规范文件及无忧帮帮平台的各项规则的规定和要求，不违背社会公共利益或公共道德，不损害他人的合法权益，不违反本协议及相关规则。用户如果违反前述承诺，产生任何法律后果的，用户应以自己的名义独立承担所有的法律责任，并确保无忧帮帮平台免于因此产生任何损失。</p>
			<p>（2）不发布国家禁止发布的信息，不发布涉嫌侵犯他人知识产权或其它合法权益的信息，不发布违背社会公共利益或公共道德、公序良俗的信息，不发布其它涉嫌违法或违反本协议及各类规则的信息。</p>
			<p>（3）不使用任何装置、软件或例行程序干预或试图干预无忧帮帮平台的正常运作或正在无忧帮帮平台上进行的任何活动。</p>
			<p>（4）不得发表、传送、传播、储存侵害他人知识产权、商业秘密权等合法权利的内容或包含病毒、木马、定时炸弹等可能对无忧帮帮服务系统造成伤害或影响其稳定性的内容。</p>
			<p>（5）不得进行危害计算机网络安全的行为，包括但不限于：使用未经许可的数据或进入未经许可的服务器帐号；不得未经允许进入公众计算机网络或者他人计算机系统并删除、修改、增加存储信息；不得未经许可，企图探查、扫描、测试本平台系统或网络的弱点或其它实施破坏网络安全的行为；不得企图干涉、破坏本平台系统或手机APP的正常运行。</p>
			<h2>五、禁止通过无忧帮帮平台递送、传播违禁品。违禁品包括但不限于下列物品：</h2>
			<p>1.各类武器、弹药。如枪支、子弹、炮弹、手榴弹、地雷、炸弹等。</p>
			<p>2.各类易爆炸性物品。如雷管、炸药、火药、鞭炮等。</p>
			<p>3.各类易燃烧性物品，包括液体、气体和固体。如汽油、煤油、桐油、酒精、生漆、柴油、气雾剂、气体打火机、瓦斯气瓶、磷、硫磺、火柴等。</p>
			<p>4.各类易腐蚀性物品。如火硫酸、盐酸、硝酸、有机溶剂、农药、双氧水、危险化学品等。</p>
			<p>5.各类放射性元素及容器。如铀、钴、镭、钚等。</p>
			<p>6.各类烈性毒药。如铊、氰化物、砒霜等。</p>
			<p>7.各类麻醉药物。如鸦片（包括罂粟壳、花、苞、叶）、吗啡、可卡因、海洛因、大麻、冰毒、麻黄素及其它制品等。</p>
			<p>8. 各类生化制品和传染性物品。如炭疽、危险性病菌、医药用废弃物等。</p>
			<p>9.各种危害国家安全和社会政治稳定以及淫秽的出版物、宣传品、印刷品等。</p>
			<h2>六、平台服务内容的所有权</h2>
			<p>无忧帮帮平台各项服务的所有权和运作权归北京无忧帮帮。</p>
			<p>无忧帮帮平台服务内容包括：文字、软件、声音、图片、录像、图表、广告中的全部内容，电子邮件的全部内容，无忧帮帮平台为用户提供的其他信息。所有这些内容受版权、商标、标签和其它财产权法律的保护。</p>
			<p>用户可将与本人相关的包括订单在内的服务信息，通过无忧帮帮提供的分享入口自由分享给他人，但不得用于商业用途。分享时不得进行如遮挡“无忧帮帮”标识、擅自添加其他信息等加工处理。</p>
			<p>用户接受本协议条款，即表明该用户将其在无忧帮帮平台发表的任何形式的信息的著作权或其他合法权利，包括并不限于：复制权、发行权、出租权、展览权、表演权、放映权、广播权、信息网络传播权、摄制权、改编权、翻译权、汇编权以及应当由著作权人享有的其他可转让权利无偿独家转让给无忧帮帮平台所有，同时表明该用户许可无忧帮帮平台有权就任何主体侵权而单独提起诉讼，并获得全部赔偿。本协议效力及于用户在无忧帮帮平台发布的任何受著作权法保护的作品内容，无论该内容形成于本协议签订前还是本协议签订后。同时，无忧帮帮平台保留删除站内各类不符合规定的信息而不通知用户的权利。</p>
			<h2>七、用户信息授权及保护</h2>
			<p>1.无忧帮帮非常重视用户个人信息（即能够独立或与其他信息结合后识别用户身份的信息）的保护，在用户使用无忧帮帮提供的服务时，用户同意无忧帮帮按照在无忧帮帮平台上公布的隐私权政策收集、存储、使用、披露和保护用户的个人信息。无忧帮帮希望通过隐私权政策向用户清楚地介绍无忧帮帮对用户个人信息的处理方式，因此无忧帮帮建议用户完整地阅读隐私权政策，以帮助用户更好地保护用户的隐私权。</p>
			<p>2.用户声明并保证，用户对用户所发布的信息拥有相应、合法的权利。否则，无忧帮帮可对用户发布的信息依法或依本协议进行删除或屏蔽。用户应当确保用户所发布的信息不包含以下内容：</p>
			<p>（1）违反国家法律法规禁止性规定的；</p>
			<p>（2）政治宣传、封建迷信、淫秽、色情、赌博、暴力、恐怖或者教唆犯罪的；</p>
			<p>（3）欺诈、虚假、不准确或存在误导性的；</p>
			<p>（4）侵犯他人知识产权或涉及第三方商业秘密及其他专有权利的；</p>
			<p>（5）侮辱、诽谤、恐吓、涉及他人隐私等侵害他人合法权益的；</p>
			<p>（6）存在可能破坏、篡改、删除、影响无忧帮帮平台任何系统正常运行或未经授权秘密获取无忧帮帮平台及其他用户的数据、个人资料的病毒、木马、爬虫等恶意软件、程序代码的；</p>
			<p>（7）其他违背社会公共利益或公共道德或依据相关无忧帮帮平台协议、规则的规定不适合在无忧帮帮平台上发布的。</p>
			<p>3.信息的授权使用</p>
			<p>（1）用户了解并同意，无忧帮帮有权应政府部门（包括司法及行政部门）的要求，向其提供用户向无忧帮帮提供的用户信息和交易记录等必要信息。如用户涉嫌侵犯他人知识产权等合法权益，则无忧帮帮亦有权在初步判断涉嫌侵权行为存在的情况下，向权利人提供用户必要的身份信息。</p>
			<p>（2）对于用户提供及发布除个人信息外的文字、图片、视频、音频等非个人信息，在版权保护期内，用户同意无偿授予无忧帮帮许可使用权利，并以已知或日后开发的形式、媒体或技术将上述信息纳入其它产品内。</p>
			<p>（3）为方便用户使用无忧帮帮相关服务，用户授权无忧帮帮将用户在账户注册和使用无忧帮帮平台服务过程中提供、形成的信息传递给无忧帮帮相关服务提供者，或从无忧帮帮相关服务提供者获取用户在注册、使用相关服务期间提供、形成的信息。</p>
			<p>4.用户完全理解并授权无忧帮帮、无忧帮帮授权的第三方以及其他用户与无忧帮帮一致同意的第三方，根据本协议及无忧帮帮规则的规定，处理用户在无忧帮帮平台上发生的所有交易及可能产生的交易纠纷。用户同意接受无忧帮帮或无忧帮帮授权的第三方或用户与无忧帮帮一致同意的第三方的判断和调处决定，该决定将对用户具有法律约束力。</p>
			<p>5.一旦用户违反本协议，或违反与无忧帮帮签订的其他协议之约定，无忧帮帮有权对用户的权益采取限制措施，包括但不限于要求支付机构将用户账户内的款项支付给无忧帮帮指定的对象，并在无忧帮帮平台暂停、终止对用户提供部分或全部服务，且在经营或实际控制的任何网站公示用户的违约情况，无忧帮帮无须就此承担任何责任。</p>
			<h2>八、协议变更及通知</h2>
			<p>1. 无忧帮帮可根据国家法律法规变化及维护交易秩序、保护消费者权益的需要，单方面不时修改、补充本协议。</p>
			<p>如您对变更事项不同意的，您应当于变更事项确定的生效之日起停止使用无忧帮帮平台服务，变更事项对您不产生效力；如您在变更事项生效后仍继续使用无忧帮帮平台服务，则视为您同意已生效的变更事项。</p>
			<p>您同意无忧帮帮以下列任何一种方式向您送达各类通知：</p>
			<p>（1）在无忧帮帮官方网站等公开平台公示的文案；</p>
			<p>（2）站内信、弹出消息、客户端推送消息；</p>
			<p>（3）根据您预留于无忧帮帮平台的联系方式发出的电子邮件、短信、函件等；</p>
			<p>（4）其他法律或商业惯例认定的通知方式。</p>
			<h2>九、法律管辖及适用</h2>
			<p>1.本协议之效力、解释、变更、执行与争议解决均适用中华人民共和国法律，如无相关法律规定的，则应参照通行惯例或公众道德标尺。</p>
			<p>2.如双方就本条款内容或其执行发生任何争议，双方应尽力友好协商解决；协商不成时，任何一方均可向无忧帮帮平台所在地的人民法院提起诉讼。</p>
			<p>3.本协议所有条款的标题仅为阅读方便，本身并无实际涵义，不能作为本协议涵义解释的依据。</p>
			<p>4.本协议条款无论因何种原因部分无效或不可执行，其余条款仍有效，对双方具有约束力。</p>
		</body>
		</html>';
	}

	public static function getBailDocument($provider_id)
	{
		$account_info   = ShopHelper::getThawAccount($provider_id);
		$bail_data      = (new Query())->from("bb_51_shops")->select(['bail_time', 'bail_money'])->where(['id' => $provider_id, 'status' => 1])->one();
		$now_time       = time();
		$withdraw_time  = $bail_data['bail_time'] + 3600 * 24 * 15;
		$left_bail_time = 0;
		if ($bail_data['bail_time']) {
			if ($withdraw_time > $now_time) {
				$now_date       = new \DateTime(date('Y-m-d', $now_time));
				$withdraw_date  = new \DateTime(date('Y-m-d', $withdraw_time));
				$interval       = $now_date->diff($withdraw_date);
				$left_bail_time = $interval->format('%a');
			}
		}
		$withdraw_str           = '<p style="text-align: right;font-size: 14px; font-weight: bold;margin-bottom: -16px;">距解冻保证金需<span style="color: #FF6600;font-size: 25px;">' . $left_bail_time . '</span>天</p>';
		$result['html']         = '<body style="padding:20px;">' . $withdraw_str . '
    <h1 style="font-size:13px;margin-top: 1.5em;color: #FF9900;">1、为什么要缴纳保证金？</h1>
    <p style="font-size: 13px;line-height: 22px;padding:0;color: #999999; ">为了保障消费者的利益，防止弃单、乱接单、接私单等现象出现，同时也需保消费者的人身及货物安全，注册平台的服务者，必须先缴纳保证金。</p>
    <h1 style="font-size:13px;margin-top: 1.5em;color: #FF9900;">2、保证金要缴纳多少钱？</h1>
    <p style="font-size: 13px;line-height: 22px;padding:0;color: #999999;">保证金需要缴纳99元。若发生弃单、乱接单等情况，需扣除保证金，作为消费者的赔偿。如发生货物损坏情况，经核实责任则扣除货品相关费用赔偿消费者，小帮保证金不足，需缴纳补足保证金99元，方可再次接单。</p>
    <h1 style="font-size:13px;margin-top: 1.5em;color: #FF9900;">3、保证金缴纳后的钱还是我的吗？</h1>
    <p style="font-size: 13px;line-height: 22px;padding:0;color: #999999;">保证金的钱只是暂时保管在本平台，若您需要取回，可于缴纳保证金15天后解冻，保证金解冻后会与下一提现日内退还至您绑定的提现账户。无忧帮帮的提现日为每周二及每周五，提现时间为17：00（如遇节假日提现日会延至下一提现日）。提现日17：00前提现的资金会于当日转账，具体入账时间根据银行的入账时间而定，不同银行入账时间不一样。提现日17：00之后及非提现日提交的申请统一纳入下一提现日实现提现。即：提现日17：00之前解冻的保证金可当天转账，提现日17：00之后或非提现日解冻的保证金需下一提现日方可转账</p>
</body>';
		$result['bail_money']   = isset($bail_data['bail_money']) ? $bail_data['bail_money'] : 0.00;
		$result['binding_text'] = isset($account_info['binding_text']) ? $account_info['binding_text'] : 0;//提现绑定的账户文本
		$result['binding_id']   = isset($account_info['binding_id']) ? $account_info['binding_id'] : 0;//提现账户ID
		$result['is_binding']   = isset($account_info['is_binding']) ? 1 : 0;//供前端判断是否绑定

		return $result;
	}


	public static function cashWithdrawal()
	{
		return $result['html']
			= '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>提现收费说明</title>
			</head>
			<body >
			<p><span style="line-height:1.5;">提现规则：</span></p>
			<p><span style="line-height:1.5;"></span><span style="line-height:1.5;">1、提现时间：每个星期二及星期五的0：00-17：00</span></p>
			<p><span style="line-height:1.5;">2、如遇提现日是节假日则延后至下个提现日进行处理。（例：如星期五为国家法定假日小帮申请提现，提现时间将会推迟到下个星期二进行处理）</span></p>
			<p><span style="line-height:1.5;">3、到账时间：在提现的时间内发起的提现会在3个工作日内到账。</span></p>
			<p><span style="line-height:1.5;">4、提现申请后，资金会处于冻结状态。</span></p>
			<p><span style="line-height:1.5;">备注：通常提现到账时间为提现日当天17：00-次日17：00（具体以银行放款时间为准）</span></p>
			</body>
			</html>';
	}

	public static function matchCardRule()
	{
		return $result['html']
			= '<!DOCTYPE html>
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>优惠券的匹配规则</title>
		<style>
			body{ padding: 20px; margin: 0;}
			h1{font-size:15px;margin-top: 1.6em;color: #666666; text-align:center;}
			h2{font-size:14px;margin-top: 1.6em;color: #666666;}
			p{font-size: 14px;line-height: 24px;color: #999999; text-indent:2em}
		</style>
		</head>
		<body >
			<h2>一、定义</h2>
			<p>1、如单张订单总价等于优惠券价格，系统优先匹配该类订单优惠券。</p>
			<p>2、如单张订单总价大于优惠券价格，系统第二步匹配该类订单优惠券。</p>
			<p>3、如单张订单总价小于优惠券价格，系统不做匹配，需用户手动选择该优惠券。</p>
			<p>4、如手动配置单张订单总价小于优惠券价格的情况，优惠券差价不做退还。</p>
			<p>5、优惠券匹配最终解释权归无忧帮帮所有。</p>
		</body>
		</html>';
	}

	public static function rechargeRule()
	{
		return $result['html']
			= '<!DOCTYPE html>
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>充值协议</title>
		<style>
			body{ padding: 20px; margin: 0;}
			h1{font-size:15px;margin-top: 1.6em;color: #666666; text-align:center;}
			h2{font-size:14px;margin-top: 1.6em;color: #666666;}
			p{font-size: 14px;line-height: 24px;color: #999999; text-indent:2em}
		</style>
		</head>
		<body >
			<p>尊敬的无忧帮帮用户，为保障您的合法权益，请您在点击“充值”按钮前，完整、仔细地阅读本充值协议。当你继续点击“充值”按钮，即视为您已阅读、理解本协议，并同意按照本协议内容执行。</p>
			<p>1、您充值后，账户余额仅可用于支付无忧帮帮平台提供的服务；账户余额使用不设有效期，不能提现、转移、转赠，不能用于手机充值，请根据您的实际需求选择充值金额。</p>
			<p>2、购买优惠券后，需在有效期内使用，优惠券仅可用于相对应的服务（具体看优惠券上说明）；优惠券有效期不可延长、转赠、退券，请根据您的实际需求购买相应优惠券数量。</p>
			<p>3、您在下单接受服务前，请您认真查看价格提示，确认无异议后下单，订单服务费金额以您最终支付的金额为准，如您对服务费有异议，请在下单前联系客服。</p>
			<p>4、我们包含充值赠送在内的所有优惠推广活动仅面向正当、合法使用服务的商户。一旦您存在利用我们的规则漏洞进行任何形式的作弊行为（包括但不限于通过我们的活动获得不正当的经济利益），我们有权追回您作弊所得的不正当经济利益、关闭作弊账户或与您相关的所有账户，并保留取消您后续使用我们服务的权利，及依据严重程度追究您的法律责任。</p>
		</body>
		</html>';
	}

}