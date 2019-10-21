<?php
// +----------------------------------------------------------------------
// | 支付宝APP支付
// +----------------------------------------------------------------------
namespace app\common\service;

use think\Loader;

class AlipayApp
{
    //应用ID
    public $appId;

    //私钥值
    public $rsaPrivateKey;

    //使用读取字符串格式，请只传递该值
    public $alipayrsaPublicKey;

    public function __construct($_config = array())
    {
        $paymentItem = model('Payment')->getPayment(['payment_code' => 'alipay_app', 'payment_state' => 1]);
        if (!$paymentItem) {
            throw new Exception('payment not exist');
        }
        $payment_config = json_decode($paymentItem['payment_config'], true);
        $this->appId = $payment_config['appId'];
        $this->rsaPrivateKey = $payment_config['rsaPrivateKey'];
        $this->alipayrsaPublicKey = $payment_config['alipayrsaPublicKey'];
    }

    /**
     *
     * 发起支付
     * @param body 商品描述
     * @param subject    商品的标题/交易标题/订单标题/订单关键字等
     * @param passback_params 附加数据
     * @param out_trade_no 商户订单号
     * @param total_amount 金额
     * @param notify_url 异步通知地址
     */
    public function toPay($param)
    {
        Loader::import('alipay_app.AopClient');
        Loader::import('alipay_app.AlipayTradeAppPayRequest');

        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\": \"" . $param['body'] . "\","
            . "\"subject\": \"" . $param['subject'] . "\","
            . "\"out_trade_no\": \"" . $param['out_trade_no'] . "\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"" . $param['total_amount'] . "\","
            . "\"passback_params\": \"" . urlencode($param['passback_params']) . "\"," //附加参数，必须进行UrlEncode
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($param['notify_url']); //回调地址
        $request->setBizContent($bizcontent);
        $response = $aop->sdkExecute($request);
        return $response;
    }

    /**
     * 回调
     * @data $_POST
     */
    public function notify($data)
    {
        Loader::import('alipay_app.AopClient');

        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey; //请填写支付宝公钥，一行字符串
        $flag = $aop->rsaCheckV1($data, NULL, "RSA2"); //RSA2与上面一致
        return $flag;
    }
}