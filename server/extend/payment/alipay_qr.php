<?php
/**
 * 支付宝当面付扫码支付接口类
 */

class alipay_qr{
    //配置-数据库
    private $config = [
        'app_id'     => '',//账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
    ];

    //编码
    protected $charset='utf-8';
    //私钥值
    protected $rsaPrivateKey='MIIEowIBAAKCAQEAr1JJql1ZpibRw+3ecSExGS4iPuZO+BCF/sNQXwbcGLK1t67KpQ5XecI4yqWer9ADb98qsi47xYjlBvhrvVEuPzyx2V8ilS7zh+GvSky57bIM8AXUTpnGCeID63TegJ6/8a6UkdYDFqCUwJFt6UP8/izq0gdUAKKSOPqUGkx77REj7LtUowyRw3/QXCm9PTD5Cpzk7U5XSHu/LlhLiI14IeBHdF8DUtYIzo+qZS3GZ3d/Ot4edV1WBK5iDGvgZOH5KhEMt0qIJ6p/6KSoi3KNd/IRVInDahvgJY3WX7g2YwetAgnMSWY412g2GVqA8vdrZUOloBgo07M2IWYNVmOR1wIDAQABAoIBAQCK+AMEU7J4DVVApRApRFISz4q9qPj3kMFly5Otf1Z/DUkVLCvc4Z1dGiLCHr3GRnAzAQZq7lLapURFXiyoh+zMm2OuFcbn0augDbhQgLjwPK4co8JB+4oK02if/qdP9IXZILULVERmbyr56UziFc43+4q2qLBhQHv/RkCXCAFDS379pb+qOVxTIhAlZM56dCq3Uf85F14bNzTxrZv9+6ncKZl7545NoUuiJ3bph4OSaTnQxA/uyDGvaBywhJ+0SaCBV4Dkoi2ITj+TW+aRUrDI7QXfqrX1q4CRv32uOEIorSYlTyGUkl7Oo9YiTssK+MQcrPAmCkIxhM9VvSswcFNhAoGBANTfC3bpeNCmhKHaocAFOebLWRdDzhgVEFj0iCcsRc6DgcIdc0OKwZOKh7vs2RixeamYiYkw31XcMupLg8hbwm/5ltw4UKN0Y3DoZqAHkfZwctvX0UYlqx1wN+X1h8vwSwqgFy44ByhSsjJSj0HlmcUfl9cQTdknE/UbP4KajVvVAoGBANLXpxZ519jI7+PmDszeYXOHSgAH45lzECvaq5Z0pUuh8JQs3rp0Fsch6fbImxqrYe5X2WEnl/+bsLCUh0RxPEq1FHvu59OT+djiLx8ZXfe9BBf0hwYbNyapbelomkR6bY40gVaDwqwjyy6Aat4ZjH1h37Rek8RUxUErTiOIjGj7AoGACXBid8FehBH+K5cgOP2CVcOKjceJ6fN1KByftOz3o3KdXFgl9ka+0qX/4stbzQaBmI2U+6pZHNuIvm7exxGai5CLqfQtTzIn/qevdUUgqcbOsQTe2Ldu4P1K3T2H6fkburrijEAbwSP5ltWmWIuvx9fgyb6FVS2watvscwQbZIkCgYAbg1iibjPkbhQfwR2dTkeZM8ZMSrtAgutRTa+maoJHZFJog5Js8kkmox5MvdC/oi2dVIlhTDFhgYeFA1zDaaNcfFS392z5Bw5LZviPLlg8w8WM+rPD8Dk6KlHVa3j2BqPVMrTN/VOh8unIn6lSYjMbCXKPrufJbuZuoqAHswHfkQKBgFsEPamvQ2h5WwxEm+W+WabA22O4UIvP54avC7WOpT+KP/mMfpCQrvNqZ53IY1YQAo6lQFrRiSfqfHsHRUko7ouVhEKU/xz43yFiCagIB3R/ZfGTvftotK9lHbyonwSIrWNW3e5SVvfW1BhlPOAWNtG3JD9tvcv0quItERWON9y0';

    //回调配制
    protected  $notify_config=[
        'alipay_public_key'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyppfKr4J9VuY/rTmNhWvcFxqnv+IbgtWIW7fV7UR6dPIlrFctME4f1yshNd6+OXUJR9NGvPG2a/nx5f5UhMzm8R35a5H+ZiXZKWkyUFy3IJ/8xpA1sJ/JhpFhKSRS98fGvY0JrmJJD7NUpZHkQl43eT38TWT/TjpNjGzggmI4hHrszTkUy4EJCfo6dp4C8gVfx6+OT4sIvk2Pbje5TBvAyI3hUurCXy//Ggmai0RwnSL6MntwrgdihcShl7ZxMeXKIzmEsa5jnatwr9K+56Y5Qes5VUnWF4YCTYbtadZKq+HAU3aRbosy6JH1LQol9LVYOm2VyxaWdBz7EqgQ+s6EwIDAQAB',
    ];

    public function __construct($config=array()){
        $paymentItem=model('Payment')->getPayment(['payment_code'=>'alipay_qr','payment_state'=>1]);
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
        //请求参数
        $requestConfigs = array(
            'out_trade_no'=>$param['pay_out_trade_no'],
            'total_amount'=>$param['pay_total_fee'], //单位 元
            'subject'=>$param['pay_subject'],  //订单标题
        );
        $commonConfigs = array(
            //公共参数
            'app_id' => $this->config['app_id'],
            'method' => 'alipay.trade.precreate',             //接口名称
            'format' => 'JSON',
            'charset'=>$this->charset,
            'sign_type'=>'RSA2',
            'timestamp'=>date('Y-m-d H:i:s'),
            'version'=>'1.0',
            'notify_url' => $param['pay_notify_url'],
            'biz_content'=>json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = $this->generateSign($commonConfigs, $commonConfigs['sign_type']);

        $result = $this->curlPost('https://openapi.alipay.com/gateway.do',$commonConfigs);
        return json_decode($result,true);

    }

    /**
     * 通知地址验证
     * @return bool
     */
    public function notify1() {
        $param	= $_POST;
        $sign = $param['sign'];
        unset($param['sign_type']);
        unset($param['sign']);
        \think\Log::record($param);
        $signContent=$this->getSignContent($param);
        \think\Log::record($signContent);
        return $this->verify($signContent, $sign, 'RSA2');
    }

    //-----------------------------------回调-------------------
    public function notify() {
        $para_temp=$_POST;
        if($para_temp['trade_status']!='TRADE_SUCCESS'){
            return false;
        }
        $sign=$para_temp['sign'];
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
        $isSgin = $this->rsaVerify($prestr, trim($this->notify_config['alipay_public_key']), $sign,'RSA2');
        if($isSgin){
            return $para_temp;
        }else{
            return false;
        }

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

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $alipay_public_key 支付宝的公钥字符串
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $alipay_public_key, $sign,$signType = 'RSA')  {
        //以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $alipay_public_key=str_replace("-----BEGIN PUBLIC KEY-----","",$alipay_public_key);
        $alipay_public_key=str_replace("-----END PUBLIC KEY-----","",$alipay_public_key);
        $alipay_public_key=str_replace("\n","",$alipay_public_key);

        $alipay_public_key='-----BEGIN PUBLIC KEY-----'.PHP_EOL.wordwrap($alipay_public_key, 64, "\n", true) .PHP_EOL.'-----END PUBLIC KEY-----';
        $res=openssl_get_publickey($alipay_public_key);
        if($res)
        {
            if ("RSA2" == $signType) {
                $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
            } else {
                $result = (bool)openssl_verify($data, base64_decode($sign), $res);
            }
        }
        else {
            echo "您的支付宝公钥格式不正确!"."<br/>"."The format of your alipay_public_key is incorrect!";
            exit();
        }
        openssl_free_key($res);
        return $result;
    }

    //-----------------------------------回调 end-------------------

    function verify($data, $sign, $signType = 'RSA') {
        $pubKey= $this->notify_config['alipay_public_key'];
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }
//        if(!$this->checkEmpty($this->alipayPublicKey)) {
//            //释放资源
//            openssl_free_key($res);
//        }
        return $result;
    }

    public function generateSign($params, $signType = "RSA") {
        return $this->sign($this->getSignContent($params), $signType);
    }

    protected function sign($data, $signType = "RSA") {
        $priKey=$this->rsaPrivateKey;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
        } else {
            openssl_sign($data, $sign, $res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    public function getSignContent($params) {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                //$v = $this->characet($v, $this->charset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }
    public function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


}
