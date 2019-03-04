<?php
/**
 *
 * 微信APP支付实现类
 * author ssp
 */

class wxpay_app{

    //回调调试
    const DEBUG = 0;

    //=======【证书路径设置】=====================================
    /**
     * TODO：设置商户证书路径
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
     * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
     * @var path
     */
    const SSLCERT_PATH = '../cert/apiclient_cert.pem';
    const SSLKEY_PATH = '../cert/apiclient_key.pem';
    //=======【curl代理设置】===================================
    const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
    const CURL_PROXY_PORT = 0;//8080;
    //=======【上报信息配置】===================================
    /**
     * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
     * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少开启错误上报。
     * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
     * @var int
     */
    const REPORT_LEVENL = 1;

    //=======【基本信息设置】=====================================
    /**
     * 微信公众号信息配置
     * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     * MCHID：商户号（必须配置，开户邮件中可查看）
     * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
     * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
     * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
     * @var string
     */
    private $config = [
        'appid'     => '',
        'mchid'     => '',
        'key'       => '',
        'appsecret' => '',
    ];

    /**
     * 初始化配制参数
     * @param array $_config
     */
    public function __construct($_config=array()){
        $paymentItem=model('Payment')->getPayment(['payment_code'=>'wxpay_app','payment_state'=>1]);
        if(!$paymentItem){
            throw new WxPayException('payment not exist');
        }
        $payment_config=json_decode($paymentItem['payment_config'],true);
        $this->config=$payment_config;
    }

    /**
     *
     * 发起支付
     * @param body 商品描述
     * @param attach 附加数据
     * @param out_trade_no 商户订单号
     * @param total_fee 金额-分
     * @param notify_url 异步通知地址
     */
    public function toPay($param){
        //统一下单
        $order = $this->unifiedOrder($param);
        //组装参数
        $apiParameters = $this->GetAppApiParameters($order);
        //同步回调地址
        $paySuccessUrl='';
        $payErrorUrl='';

        return <<<EOB
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8" />
<title>微信安全支付</title>
</head>
<body>
正在加载…
<br/>
<span id="txt"></span>
<script>
function theBtnOnClicked(){
	var timestamp1 = Date.parse(new Date());
	document.getElementById("txt").innerHTML=timestamp1;
}
//调用java方法
javaObject.javaDoIt('$apiParameters');
function app_return(order,result){
    switch(result){
    case '0':
        msg='支付成功'; window.location.href='$paySuccessUrl'; break;
    case '-1':
        msg='支付错误'; alert('支付错误'); history.go(-1); break;
    case '-2':
        msg='支付取消'; alert('支付取消'); history.go(-1); break;
    default:
        msg='未知错误'; alert('未知错误');  history.go(-1); break;
    }
	//document.getElementById("txt").innerHTML='orderid:'+ order + ' result:' + msg+result;
}
</script>
</body>
</html>
EOB;

    }

    /**
     * 统一下单
     * @param array $signParam
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function unifiedOrder($Param)
    {
        //生成支付参数
        $data = array();
        $data['appid'] = $this->config['appid'];
        $data['mch_id'] =$this->config['mchid'];
        $data['body'] = $Param['pay_body'];
        $data['attach'] =$Param['pay_extend'];
        $data['out_trade_no'] =$Param['pay_out_trade_no'];
        $data['total_fee'] =intval($Param['pay_total_fee'] * 100);
        $data['notify_url'] = $Param['pay_notify_url'];
        $data['nonce_str'] = $this->getNonceStr();
        $data['spbill_create_ip'] =$_SERVER['REMOTE_ADDR'];
        $data['trade_type'] = "APP";
        $data['sign'] = $this->MakeSign($data);

        $xml = $this->ToXml($data);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $timeOut=6;
        //统一下单
        $response = $this->postXmlCurl($xml, $url, false, $timeOut);
        //结果转成数组
        $result = $this->FromXml($response);
        return $result;
    }

    /**
     * 调起App支付接口参数
     * @param array $signParam
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function GetAppApiParameters($UnifiedOrderResult)
    {
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
            throw new WxPayException("参数错误");
        }
        //生成支付参数
        $data = array();
        $data['appid'] = $UnifiedOrderResult["appid"];
        $data['partnerid'] =$this->config['mchid'];
        $data['prepayid'] = $UnifiedOrderResult['prepay_id'];
        $data['package'] = "Sign=WXPay";
        $data['noncestr'] = $this->getNonceStr();
        $data['timestamp'] = time();
        $data['sign'] =$this->MakeSign($data);
        $parameters = json_encode($data);
        return $parameters;
    }

    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }

    /**
     *
     * 微信回调通知验证
     * @return arrar 通知数据及发回数据
     */
    public function notify(){
        try {
            //验证成功数据
            $data = $this->getNotifyData();
            //发回通知
            $resultXml = $this->ToXml(array(
                'return_code' => "SUCCESS",
                'return_msg' => "OK",
            ));
            if (self::DEBUG) {
                file_put_contents(__DIR__ . '/log.txt', var_export($data, true), FILE_APPEND | LOCK_EX);
            }
        } catch (WxPayException $ex) {
            $data = null;
            //发回通知
            $resultXml = $this->ToXml(array(
                'return_code' => 'FAIL',
                'return_msg' => $ex->getMessage(),
            ));
            if (self::DEBUG) {
                file_put_contents(__DIR__ . '/log_err.txt', $ex . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }
        return array($data,$resultXml);
    }

    /**
     *
     * 获取通知的数据
     * @param array $data
     * @return array
     */
    public function getNotifyData()
    {
        $result = $this->FromXml(file_get_contents('php://input'));
        if ($result['return_code'] != 'SUCCESS') {
            throw new WxPayException($result['return_msg']);
        }
        if ($result['result_code'] != 'SUCCESS') {
            throw new WxPayException("[{$result['err_code']}]{$result['err_code_des']}");
        }
        if (!$this->checkNotifySign($result)) {
            throw new WxPayException("Invalid signature");
        }
        return $result;
    }

    /**
     *
     * 验证签名
     * @param array $data 微信POST数组
     * @return bool
     */
    public function checkNotifySign($data)
    {
        if (empty($data['sign'])) {
            return false;
        }
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign == $this->MakeSign($data);
    }

    /**
     * 生成签名
     * @param array $signParam
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($signParam)
    {
        //签名步骤一：按字典序排序参数
        ksort($signParam);
        $string = $this->ToUrlParams($signParam);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->config['key'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    private function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    private function ToXml($data)
    {
        if(!is_array($data)
            || count($data) <= 0)
        {
            throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        if(self::CURL_PROXY_HOST != "0.0.0.0"
            && self::CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, self::CURL_PROXY_HOST);
            curl_setopt($ch,CURLOPT_PROXYPORT, self::CURL_PROXY_PORT);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, self::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, self::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    private function FromXml($xml)
    {
        if(!$xml){
            throw new WxPayException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    private function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(self::CURL_PROXY_HOST != "0.0.0.0"
            && self::CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, self::CURL_PROXY_HOST);
            curl_setopt($ch,CURLOPT_PROXYPORT, self::CURL_PROXY_PORT);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $openid = $data['openid'];
        return $openid;
    }

    /**
     *
     * 格式化参数格式化成url参数
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->config['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->config['appid'];
        $urlObj["secret"] = $this->config['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }
}

/**
 *
 * 微信支付API异常类
 * @author widyhu
 *
 */
class WxPayException extends Exception {
    public function errorMessage()
    {
        return $this->getMessage();
    }
}