<?php

class AlipayConfig
{

    //=======【基本信息设置】=====================================
    //
	/**
     * TODO: 修改这里配置为您自己申请的商户信息
     * 微信公众号信息配置
     * 
     * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     * 
     * MCHID：商户号（必须配置，开户邮件中可查看）
     * 
     * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
     * 
     * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
     * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
     * @var string
     */
    public static $alipay_config;

    public static function init() {
        $info = M('Payment')->where(array('payid' => 6, 'status' => 1))->Field('appid,merchantid,key,callback_url')->find();
        self::$alipay_config = array(
            'payment_type' => "1",
            'partner' => $info['merchantid'], //这里是你在成功申请支付宝接口后获取到的PID；签约的支付宝账号对应的支付宝唯一用户号
            'seller_id'	=> $info['merchantid'], //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
            'key' => $info['key'], //这里是你在成功申请支付宝接口后获取到的Key
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'cacert' => dirname(__FILE__) . '\\cacert.pem',
            'transport' => 'http',
             //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
            //'seller_email' => 'uplus0592@sina.com',
            //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
            'notify_url' => $info['callback_url'],
            //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
            'return_url' =>'http://' . $_SERVER['HTTP_HOST'] . '/m.php/recharge/alipayReturn.html',
            'successpage' => 'http://' . $_SERVER['HTTP_HOST'] . '/recharge/rechargeResult/',//支付成功跳转到的页面
            'errorpage' => 'http://' . $_SERVER['HTTP_HOST'] . '/recharge/rechargeResult/',//支付失败跳转到的页面
            'service' => 'create_direct_pay_by_user',// 产品类型，无需修改
            'anti_phishing_key' => '',//防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
            'exter_invoke_ip' => get_client_ip(),// 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
        );
        
    }

}

