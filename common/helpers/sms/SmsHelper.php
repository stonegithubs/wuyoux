<?php

/**
 * 短信模块
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy (林先富)
 * Date: Andy 2017/7/18
 */

namespace common\helpers\sms;

use common\components\Ref;
use common\helpers\HelperBase;
use common\helpers\security\SecurityHelper;
use common\models\util\SmsLog;
use Yii;
use yii\helpers\ArrayHelper;

class SmsHelper extends HelperBase
{

	public static function demoSend($mobile, $code, $tpl)
	{
		$result = false;
		$params = [
			'tpl'  => $tpl,
			'data' => ['code' => "$code", 'product' => '无忧帮帮']
		];
		$res    = self::send($mobile, $params);
		if ($res) {
			$result = true;
		} else {
			//TODO 日志
		}

		return $result;
	}

	//注册获取code
	public static function sendSignUpCode($mobile)
	{
		$code   = SecurityHelper::getCode();
		$result = SecurityHelper::saveToken($mobile, $code, Ref::SMS_CODE_REGISTER);
		$params = [
			'tpl' => "SMS_60025252", 'data' => ['code' => "$code", 'product' => '无忧帮帮']    //TODO 对应的验证码
		];
		$res    = self::send($mobile, $params);

		return $result;
	}

	//找回密码获取code
	public static function sendFindPasswordCode($mobile)
	{
		$code   = SecurityHelper::getCode();
		$result = SecurityHelper::saveToken($mobile, $code, Ref::SMS_CODE_FIND_PASSWORD);
		$params = [
			'tpl' => "SMS_132385928", 'data' => ['code' => "$code"]
		];
		$res    = self::send($mobile, $params);

		return $result;
	}

	//修改支付密码获取code
	public static function sendPayPasswordCode($mobile)
	{
		$code   = SecurityHelper::getCode();
		$result = SecurityHelper::saveToken($mobile, $code, Ref::SMS_CODE_PAY_PASSWORD);
		$params = [
			'tpl' => "SMS_132385928", 'data' => ['code' => "$code"] //TODO 通用模板
		];
		$res    = self::send($mobile, $params);

		return $result;
	}

	//登录验证码
	public static function sendLoginCode($mobile)
	{
		$code   = SecurityHelper::getCode();
		$result = SecurityHelper::saveToken($mobile, $code, Ref::SMS_CODE_LOGIN_PASSWORD);
		$params = [
			'tpl' => "SMS_132385928", 'data' => ['code' => "$code"]
		];
		$res    = self::send($mobile, $params);

		return $result;
	}

	//统一发送验证码 根据type不同显示不同的验证码
	public static function sendCode($mobile, $type = Ref::SMS_CODE_LOGIN_PASSWORD)
	{
		switch ($type) {
			case  Ref::SMS_CODE_REGISTER:
				$result = self::sendSignUpCode($mobile);
				break;

			case Ref::SMS_CODE_FIND_PASSWORD:
				$result = self::sendFindPasswordCode($mobile);
				break;
			case Ref::SMS_CODE_PAY_PASSWORD:
				$result = self::sendPayPasswordCode($mobile);
				break;
			case Ref::SMS_CODE_LOGIN_PASSWORD:
				$result = self::sendLoginCode($mobile);
				break;
			default:
				$code   = SecurityHelper::getCode();
				$result = SecurityHelper::saveToken($mobile, $code, $type);
				self::demoSend($mobile, $code, 'SMS_132385928');
		}

		return $result;
	}

	//发送小帮认证信息
	public static function sendShopVerify($mobile, $tplType)
	{
		$tplType == 1 ? $tpl = 'SMS_132386131' : $tpl = 'SMS_132990318';    //SMS_132386131成功   SMS_132990318失败
		$params = ['tpl' => $tpl];
		self::send($mobile, $params);
	}

	//发送企业送入驻信息
	public static function sendBizVerify($mobile, $tplType)
	{
		$tplType == 1 ? $tpl = 'SMS_132391165' : $tpl = 'SMS_132401140';    //SMS_132391165成功   SMS_132401140失败
		$params = ['tpl' => $tpl];
		self::send($mobile, $params);
	}

	//发送提现和保证金解冻信息
	public static function sendBailNotice($mobile, $tplType)
	{
		//商家提现失败 SMS_132391153 保证金解冻失败 SMS_132401139
		$tplType == 1 ? $tpl = 'SMS_132391153' : $tpl = 'SMS_132401139';    //SMS_132391165成功   SMS_132401140失败
		$params = ['tpl' => $tpl];
		self::send($mobile, $params);
	}

	//发送配送收货短信
	public static function sendReceiverSmsNotice($mobile, $params)
	{
//		if (YII_ENV_PROD) {
		$params = ['tpl' => 'SMS_137687214', 'data' => ['provider' => '', 'provider_mobile' => $params['provider_mobile'], 'code' => $params['order_no'], 'market_code' => $params['market_code']]];
		$res    = self::send($mobile, $params);
//		}

		//SMS_132401123
	}

	//发送企业送自动扣款短信
	public static function sendBizCutPayment($mobile, $type = 0)
	{
		$tpl = 'SMS_135044128';        //余额不足
		if ($type == 1) {
			$tpl = 'SMS_135029218';        //部分扣
		} elseif ($type == 2) {
			$tpl = 'SMS_135029214';        //全部扣
		}

		$params = ['tpl' => $tpl];
		self::send($mobile, $params);
	}

	//企业送-全部扣款
	public static function sendBizCutPaymentAll($mobile, $params)
	{
		$p['data'] = $params;
		$p['tpl']  = "SMS_135791422";
		$res       = self::send($mobile, $p);
	}

	//企业送-部分扣款
	public static function sendBizCutPaymentPart($mobile, $params)
	{
		$p['data'] = $params;
		$p['tpl']  = "SMS_135801378";

		$res = self::send($mobile, $p);
	}

	//企业送-余额不足
	public static function sendBizNotPay($mobile)
	{
		$params = ['tpl' => "SMS_135805900"];
		$res    = self::send($mobile, $params);
	}


	/**
	 * 统一发送
	 *
	 * @param $mobile
	 * @param $params
	 */
	private static function send($mobile, $params)
	{

		$res  = Yii::$app->sms->send($mobile, $params);
		$data = ArrayHelper::toArray($res);

		$model              = new SmsLog();
		$model->mobile      = $mobile;
		$model->content     = isset($params['data']) ? json_encode($params['data']) : '';
		$model->tpl         = isset($params['tpl']) ? $params['tpl'] : '';
		$model->create_time = time();
		$model->status      = 2;

		if (isset($data['Message']) && $data['Message'] == 'OK') {

			$model->status = 1;
		}
		$model->result = json_encode($data);

		$model->save();
	}
}