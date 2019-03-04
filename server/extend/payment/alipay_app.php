<?php
/**
 * 支付宝APP支付接口类-旧版
 * 回调使用alipay_app/lib/alipay_notify.class.php验证签名
 */

class alipay_app{
    //配置
    private $config = [
        'partner'     => '',
        'seller_id'     => '',
    ];

    //回调配制
    private  $notify_config=[
        'alipay_public_key'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmLo5catqqLXWcf+LhRs/WDziyCAB+HPb/+xls2BAtNtvfLHCM9xej5VGTzX7mw6e5/Et3yVAhFnnTZ9U9RWq1m3MiEv19n17/yIbGMXpxSSujYnL0drFBY6Z4f19tzfqWQPETpEf1atFSHbcJQfpaslyr9W2NmS5dbWIe+sJVmZjRN5cYEhFY7U0JHqIPr653XSDzsQ152rHZIb0wJmEVfkr0yyOZl1ja0sx+Gv3/BcHDK1brK94mi9I6J78dDXQS6WSQY7mup9l74Z78FLHf22LtS9GvpkzlL5zAKh0LzTVsgGlyJNMnh0/aRYK4p4IKiSAvQRhLXjfbWLc9XFAzQIDAQAB',
        'service'=>'mobile.securitypay.pay',
        'transport'=>'http',
    ];

    public function __construct($config=array()){
        $paymentItem=model('Payment')->getPayment(['payment_code'=>'alipay_app','payment_state'=>1]);
        if(!$paymentItem){
            die('payment not exist');
        }
        $payment_config=json_decode($paymentItem['payment_config'],true);
        $this->config=$payment_config;
    }

    /**
     * 发起支付
     * @param subject 交易主题
     * @param body 交易详细说明
     * @param out_trade_no 商户订单号
     * @param total_fee 金额-元
     * @param notify_url 异步通知地址
     */
    public function toPay($param){
        if (!extension_loaded('openssl')) $this->alipay_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
        //json格式
        $jsonParams=json_encode($this->getParam($param));

        //同步回调地址
        $paySuccessUrl='';
        $payErrorUrl='';

        echo <<<EOB
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8" />
<title>支付宝安全支付</title>
</head>
<body>
支付宝安全支付
<br/>
<span id="txt"></span>
<script>
window.setTimeout(
function(){
    javaObject.javaDoIt($jsonParams);
},100)

function theBtnOnClicked(){
	var timestamp1 = Date.parse(new Date());
	document.getElementById("txt").innerHTML=timestamp1;
}

function app_return(order,result){
    switch(result){
    case '9000':
        msg='订单支付成功'; window.location.href='$paySuccessUrl'; break;
    case '4000':
        msg='支付失败'; alert('订单支付失败'); history.go(-1); break;
    case '8000':
        msg='正在处理中'; alert('正在处理中'); history.go(-1); break;
    case '6001':
        msg='用户中途取消'; alert('用户中途取消'); history.go(-1); break;
    case '6002':
        msg='网络连接出错'; alert('网络连接出错'); history.go(-1); break;
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

    /*
     * App支付请求参数
     */
    public function getParam($param){
        $data = array();
        $data['partner'] = $this->config['partner'];//你的pid
        $data['seller_id'] = $this->config['seller_id'];//seller_id
        $data['out_trade_no'] = $param['pay_out_trade_no'];
        $data['subject'] = $param['pay_subject'];
        $data['body'] = $param['pay_body'];
        $data['total_fee'] = $param['pay_total_fee'];
        $data['notify_url'] = $param['pay_notify_url'];
        $data['service'] = 'mobile.securitypay.pay';
        $data['payment_type'] = '1';
        $data['_input_charset'] = 'utf-8';

        //参数排序+拼接
        $unsign_str =$this->createLinkstring_two($this->argSort($data));

        //签名
        $rsa_path = dirname(__FILE__).'/alipay_app/rsa_private_key.pem';  //rsa私钥路径
        $sign =$this->rsaSign($unsign_str, $rsa_path);

        //需要进行utf8格式转换
        $sign = urlencode(mb_convert_encoding($sign, "UTF-8"));

        //组合最终参数
        $pay_params = $unsign_str . "&sign=\"" . $sign . "\"&sign_type=\"RSA\"";

        return $pay_params;
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    public function notify($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = array();
        foreach($para_temp as $key=>$val){
            if($key == "sign" || $key == "sign_type" || $val == ""){
                continue;
            }
            else{
                $para_filter[$key] = $para_temp[$key];
            }
        }

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $isSgin = false;
        $isSgin = $this->rsaVerify($prestr, trim($this->notify_config['alipay_public_key']), $sign);
        if($isSgin){
            return $para_temp;
        }else{
            return false;
        }

    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    public function getResponse($notify_id) {
        $transport = strtolower(trim($this->notify_config['transport']));
        $partner = trim($this->config['partner']);
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
        }
        else {
            $veryfy_url = 'http://notify.alipay.com/trade/notify_query.do?';
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = getHttpResponseGET($veryfy_url, getcwd().'/alipay_app/cacert.pem');

        return $responseTxt;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para mixed 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    public static function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    public static function createLinkstring_two($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=\"".$val."\"&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * 数组排序 按照ASCII字典升序
     * @param $para mixed 排序前数组
     * @return mixed 排序后数组
     */
    public static function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * RSA签名
     * @param $data string 待签名数据
     * @param $private_rsa_path string 用户私钥地址
     * @return mixed
     *      失败:false
     *      成功:签名结果
     */
    public static function rsaSign($data, $private_rsa_path) {
        $private_rsa = file_get_contents($private_rsa_path);
        $res = openssl_get_privatekey($private_rsa);
        if(!$res) {
            echo "您的私钥格式不正确!"."<br/>"."The format of your private_key is incorrect!";
            exit();
            //return false;
        }
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;

    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $alipay_public_key 支付宝的公钥字符串
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $alipay_public_key, $sign)  {
        //以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $alipay_public_key=str_replace("-----BEGIN PUBLIC KEY-----","",$alipay_public_key);
        $alipay_public_key=str_replace("-----END PUBLIC KEY-----","",$alipay_public_key);
        $alipay_public_key=str_replace("\n","",$alipay_public_key);

        $alipay_public_key='-----BEGIN PUBLIC KEY-----'.PHP_EOL.wordwrap($alipay_public_key, 64, "\n", true) .PHP_EOL.'-----END PUBLIC KEY-----';
        $res=openssl_get_publickey($alipay_public_key);
        if($res)
        {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }
        else {
            echo "您的支付宝公钥格式不正确!"."<br/>"."The format of your alipay_public_key is incorrect!";
            exit();
        }
        openssl_free_key($res);
        return $result;
    }

}
