<?php
/* *
 * 功能：支付宝移动支付服务端签名页面
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要编写。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 本页面代码示例用于处理客户端使用http(s) post传输到此服务端的移动支付请求参数待签名字符串。
 * 本页面代码示例采用客户端创建订单待签名的请求字符串传输到服务端的这里进行签名操作并返回。
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_rsa.function.php");
require_once("lib/alipay_core.function.php");

//确认PID和接口名称是否匹配。
date_default_timezone_set("PRC");
if (str_replace('"','',$_POST['partner'])==$alipay_config['partner']&&str_replace('"','',$_POST['service'])==$alipay_config['service']) {

	//将post接收到的数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串。
	$data=createLinkstring($_POST);

	//打印待签名字符串。工程目录下的log文件夹中的log.txt。
	logResult($data);

	//将待签名字符串使用私钥签名,且做urlencode. 注意：请求到支付宝只需要做一次urlencode.
	$rsa_sign=urlencode(rsaSign($data, $alipay_config['private_key']));

	//把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
	$data = $data.'&sign='.'"'.$rsa_sign.'"'.'&sign_type='.'"'.$alipay_config['sign_type'].'"';

	//返回给客户端,建议在客户端使用私钥对应的公钥做一次验签，保证不是他人传输。
	echo $data;
}
else{
	echo "不匹配或为空！";
	logResult(createLinkstring($_POST));
}
?>