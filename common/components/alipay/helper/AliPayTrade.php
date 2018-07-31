<?php
/**
 * @link http://www.hgrtechnology.com/
 * @copyright Copyright (c) 2015 HGR Technology 珠海海归人科技有限公司
 * @license http://www.hgrtechnology.com/license/
 * User: andywong
 * Date: 15/10/28
 * Time: 17:50
 */
namespace pay\alipay\lib;

use common\components\Ref;
use common\helpers\UrlHelper;
use common\models\pay\Transcation;
use pay\alipay\lib\wappay\buildermodel\AlipayTradeWapPayContentBuilder;
use pay\alipay\lib\wappay\service\AlipayTradeService;
use Yii;

class AlipayTrade
{


    //创建预下单
    public static function tradePrecreate($data)
    {
        $url = UrlHelper::getAddress("pay") . "/alipay_f2f_notify.php";

        require \Yii::getAlias('@pay/alipay/aop/request/AlipayTradePrecreateRequest.php');
        $request = new \AlipayTradePrecreateRequest();
        $biz = json_encode($data);
        $request->setBizContent($biz);
        $request->setNotifyUrl($url);
        $result = AlipayTools::aopclient_request_execute($request);
        if ($result->alipay_trade_precreate_response->code == 10000) {
            return $result->alipay_trade_precreate_response->qr_code;
        } else {
            return false;
        }
    }


    //查询订单
    public static function tradeQuery($data)
    {
        $url = UrlHelper::getAddress("pay") . "/alipay_f2f_notify.php";
        require \Yii::getAlias('@pay/alipay/aop/request/AlipayTradePrecreateRequest.php');
        $request = new \AlipayTradePrecreateRequest();
        $biz = json_encode($data);
        $request->setBizContent($biz);
        $request->setNotifyUrl($url);
        $result = AlipayTools::aopclient_request_execute($request);
        if ($result->alipay_trade_precreate_response->code == 10000) {
            return $result->alipay_trade_precreate_response->qr_code;
        } else {
            return false;
        }
    }


    /**
     * 即时到账
     */
    public static function tradeDirectPay($data)
    {
        header("Content-type:text/html;charset=utf-8");
        $alipay_config = null;

        require_once \Yii::getAlias('@pay/alipay/alipay.config.php');

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $data['out_trade_no'];

        //订单名称，必填
        $subject = $data['subject'];

        //付款金额，必填
        $total_fee = $data['total_amount'];

        //商品描述，可空
        $body = isset($data['body']) ? $data['body'] : null;


        $pay_domain = UrlHelper::getAddress("pay");
        $notify_url = $pay_domain . "/alipay_direct_pay_notify.php";
        $return_url = $pay_domain . "/alipay_return.php";

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => $alipay_config['service'],
            "partner" => $alipay_config['partner'],
            "seller_id" => $alipay_config['seller_id'],
            "payment_type" => $alipay_config['payment_type'],
            "notify_url" => isset($data['notify_url']) ? $data['notify_url'] : $notify_url,
            "return_url" => isset($data['return_url']) ? $data['return_url'] : $return_url,
            "anti_phishing_key" => $alipay_config['anti_phishing_key'],
            "exter_invoke_ip" => $alipay_config['exter_invoke_ip'],
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))

            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"

        );
        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
        return $html_text;
    }

    public static function tradeWapPay($data)
    {

        header("Content-type: text/html; charset=utf-8");
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $data['out_trade_no'];

        //订单名称，必填
        $subject = $data['subject'];

        //付款金额，必填
        $total_amount = $data['total_amount'];

        //商品描述，可空
        $body = isset($data['body']) ? $data['body'] : $subject . "描述";

        //超时时间
        $timeout_express = "1m";

        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);

        $pay_domain = UrlHelper::getAddress("pay");
        $notify_url = $pay_domain . "/alipay_wap_notify.php";
        $return_url = $pay_domain . "/alipay_return_wap.php";
//
//        $return_url = $data['return_url'];
//        $notify_url = $data['notify_url'];
        $alipay_config = null;
        require_once \Yii::getAlias('@pay/alipay/alipay.config.php');


        $payResponse = new AlipayTradeService($alipay_config);
        $result = $payResponse->wapPay($payRequestBuilder, $return_url, $notify_url);
//        var_dump($result);
        return $result;
    }

    /*
     * 无密退款
     */
    public static function tradeRefund($data)
    {
        require \Yii::getAlias('@pay/alipay/aop/request/AlipayTradeRefundRequest.php');
        $request = new \AlipayTradeRefundRequest();

        /*$data=[
            'out_trade_no'=>'20150320010101001',
            'trade_no'=>'2014112611001004680073956707',
            'refund_amount'=>'27',
            'refund_reason'=>'正常退款',
            'out_request_no'=>null

        ];*/
        $biz = json_encode($data);

        $request->setBizContent($biz);

        $result = AlipayTools::aopclient_request_execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            echo "成功";
        } else {
            echo "失败";
        }
    }

    /**
     * 参考 https://doc.open.alipay.com/docs/doc.htm?spm=a219a.7629140.0.0.lNZz5p&treeId=62&articleId=104744&docType=1
     * @param $data
     * @return 提交表单HTML文本
     */
    public static function tradeRefundPwd($data)
    {
        header("Content-type:text/html;charset=utf-8");
        $alipay_config = null;

        require_once \Yii::getAlias('@pay/alipay/alipay.config.php');

        $notify_url =UrlHelper::getAddress("pay") . "/alipay_refund_pwd.php";

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "refund_fastpay_by_platform_pwd",
            "partner" => $alipay_config['partner'],
            "notify_url"	=> $notify_url,
            "seller_user_id"	=> $alipay_config['seller_id'],
            "refund_date"	=> date("Y-m-d H:i:s",time()),
            "batch_no"	=> $data['batch_no'],
            "batch_num"	=> $data['batch_num'],
            "detail_data"	=> $data['detail_data'],
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))

        );

        Yii::error("tradeRefundPwd".json_encode($data));
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
        return $html_text;
    }
}