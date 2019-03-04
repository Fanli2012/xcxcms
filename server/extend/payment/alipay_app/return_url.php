<?php
/* *
 * 功能：支付宝移动支付服务端同步验签页面
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要编写。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 本页面代码示例用于处理客户端使用http(s) post传输到此服务端的移动支付同步返回中的result待验签字段.
 * 注意：只要把同步返回中的result结果传输过来做验签.
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_rsa.function.php");
require_once("lib/alipay_core.function.php");

$alipayNotify = new AlipayNotify($alipay_config);

//注意：在客户端把返回参数请求过来的时候务必要把sign做一次urlencode,保证"+"号字符不会变成空格。
if($_POST['success']=="true")//判断success是否为true.
{
	//验证参数是否匹配
	if (str_replace('"','',$_POST['partner'])==$alipay_config['partner']&&str_replace('"','',$_POST['service'])==$alipay_config['service']) {

		//获取要校验的签名结果
		$sign=$_POST['sign'];

		//除去数组中的空值和签名参数,且把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$data=createLinkstring(paraFilter($_POST));

		//logResult('data:'.$data);//调试用，判断待验签参数是否和客户端一致。
		//logResult('sign:'.$sign);//调试用，判断sign值是否和客户端请求时的一致，
		$isSgin=false;

		//获得验签结果
		$isSgin=rsaVerify($data,$alipay_config['alipay_public_key'],$sign);
		if ($isSgin) {
			echo "return success";
			//此处可做商家业务逻辑，建议商家以异步通知为准。
		}
		else {
			echo "return fail";
		}
	}
}
?>