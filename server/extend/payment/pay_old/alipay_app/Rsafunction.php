<?php
/* *
 * RSA
 * 详细：RSA加密
 * 版本：3.3
 * 日期：2014-02-20
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/

/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * return 签名结果
*/
require_once 'AppAlipayConfig.php';
function rsaSign($prestr) {
    AppAlipayConfig::init();
    $alipay_config = AppAlipayConfig::$alipay_config;
    //echo $alipay_config['private_key_path'];
    $private_key=file_get_contents($alipay_config['private_key_path']);
    $pkeyid=openssl_get_privatekey($private_key);
    openssl_sign($prestr, $sign, $pkeyid);
    openssl_free_key($pkeyid);
    $sign=base64_encode($sign);
    return $sign;
}

/**
 * RSA验签
 * @param $data 待签名数据
 * @param $alipay_public_key 支付宝的公钥字符串
 * @param $sign 要校对的的签名结果
 * return 验证结果
 */
 
function rsaVerify($data, $sign)  {
    AppAlipayConfig::init();
    $alipay_config = AppAlipayConfig::$alipay_config;
    $alipay_public_key = file_get_contents($alipay_config['alipay_public_key_path']);
   
    $res = openssl_get_publickey($alipay_public_key);
    if($res)
    {
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
    }
    else {
        M('Url')->add(array('url' => get_order_sn('RSA'), 'short' => "您的支付宝公钥格式不正确!"."<br/>"."The format of your alipay_public_key is incorrect!" . "数据：". $data, 'create_time' => time(), 'status' => 1));
        exit();
    }
    openssl_free_key($res);    
    return $result;
}
