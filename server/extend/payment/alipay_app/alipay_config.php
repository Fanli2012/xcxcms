<?php
/* *
 * 配置文件
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner']		= '2088122797661182';

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key']	= 'MIICWwIBAAKBgQCnJ3R+bx6OcTWflk9akuUnxVIRMjfqbbPKGsl+gL2vCn/bVQ+1KuPVdE0vidtXwhTGQfvLtdAMma/TSrRTOx8ZqJG4knCsbcIYO/R+4wyf4qcQKFBsqCWh01B0JTuLt6XQJe59t/f0KVZoZMBAY5xUnW7jBFKJJW+dI2Nc7AT0twIDAQABAoGAadOc+X84hU4/eO4dB2cBFhDjSGfW5uDVdxtGfoTbkrq/AAn2i+i/niCnzXUmHYWOyYZ0+B//vDqBCvC9AgToRrxi4crupmQh6ZOOon3uVjHevHVz0p/C00Ob5ZoNL96gfrhbZrMCjPAmufEK7RzF+RB0I+pe0BCndrOv1rZLgyECQQDUCfX4UKFf/TteyUJZL/6wzz2OJTKmXmQZ1Vrtq6+VQe3GRl/RQ/zR02dVeMLzozrbMCPtwWEB/iYCtGpgRtmLAkEAyc836sAOfeLJ07chtV3Zu+s7JKsQ0crgAGovg+cp+Xgpl+Nacx8TSXigOAtbh5jQkbfFr89CjHtdo5ecTXy/BQJAaIDwRY4XuuNn23N9y88ny6SYRfJ3YB+tXj4VLoYrZ3iy48HTf6PuesuiZjG6g7GzVEwJqShh00WbHkIlG4ZPuQJADddvn2NC2zGF5EaIQldIitIMgWxWP/1pNb4SujpNr7WlLKzEVGcKPJzQaGenBHrfu07eeTt+9gG0H3dTmiD3PQJAd6hP2pPEAdlCla/rUsbXatvjuSoZo79bndUsG28LXgAd8XNfdrFbTD8YbHncT2Vsfge9ASYJm45BmtqJB5ZSOg==';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

//异步通知接口
$alipay_config['service']= 'mobile.securitypay.pay';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'/cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_rsa.function.php");
require_once("lib/alipay_core.function.php");

?>