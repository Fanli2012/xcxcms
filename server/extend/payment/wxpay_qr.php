<?php
/**
 *
 * 微信扫码支付实现类
 * author ssp
 */

class wxpay_qr{

    //回调调试
    const DEBUG = 0;

    private $config = [
        'appid'     => '',
        'mchid'     => '',
        'key'       => '',
    ];
    public function __construct()
    {
        $paymentItem=model('Payment')->getPayment(['payment_code'=>'wxpay_native','payment_state'=>1]);
        if(!$paymentItem){
            throw new WxPayException('payment not exist');
        }
        $payment_config=json_decode($paymentItem['payment_config'],true);
        $this->config=$payment_config;
    }
    /**
     * 发起订单
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 订单发起时间
     * @return array
     */
    public function toPay($Param)
    {
        $unified = array(
            'appid' => $this->config['appid'],
            'attach' => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $Param['pay_body'],
            'mch_id' => $this->config['mchid'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $Param['pay_notify_url'],
            'out_trade_no' => $Param['pay_out_trade_no'],
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'total_fee' => intval($Param['pay_total_fee'] * 100),       //单位 转为分
            'trade_type' => 'NATIVE',
        );
        $unified['sign'] = self::getSign($unified, $this->config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }
        $codeUrl = (array)($unifiedOrder->code_url);
        if(!$codeUrl[0]) exit('get code_url error');
        $arr = array(
            "appId" => $this->config['appid'],
            "timeStamp" => time(),
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
            "code_url" => $codeUrl[0],
        );
        $arr['paySign'] = self::getSign($arr, $this->config['key']);
        return $arr;
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
        return $sign == self::getSign($data, $this->config['key']);
    }

    public function notify()
    {
        try {
            //验证成功数据
            $data = $this->getNotifyData();
            //发回通知
            $resultXml = $this->arrayToXml(array(
                'return_code' => "SUCCESS",
                'return_msg' => "OK",
            ));
            if (self::DEBUG) {
                file_put_contents(__DIR__ . '/log.txt', var_export($data, true), FILE_APPEND | LOCK_EX);
            }
        } catch (WxPayException $ex) {
            $data = null;
            //发回通知
            $resultXml = $this->arrayToXml(array(
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
     * curl get
     *
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
    public static function curlPost($url = '', $postData = '', $options = array())
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
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    /**
     * 获取签名
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}

/**
 * 微信支付API异常类
 * @author ssp
 */
class WxPayException extends Exception {
    public function errorMessage()
    {
        return $this->getMessage();
    }
}