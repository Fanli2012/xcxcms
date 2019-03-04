<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_rsa.function.php");
require_once("lib/alipay_core.function.php");
    //计算得出通知验证结果
    $alipayNotify = new AlipayNotify($alipay_config);
	if($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
	{
		if($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) {//使用支付宝公钥验签
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			//商户订单号
			$out_trade_no = $_POST['out_trade_no'];

			//支付宝交易号
			$trade_no = $_POST['trade_no'];

			//交易状态
			$trade_status = $_POST['trade_status'];

    		if($_POST['trade_status'] == 'TRADE_FINISHED') {
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
			//注意：
			//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
			//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
    		}
    		else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
			//注意：
			//付款完成后，支付宝系统发送该交易状态通知
			//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
    		}
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			echo "success";		//请不要修改或删除
		}
		else //验证签名失败
		{
			echo "sign fail";
		}
	}
	else //验证是否来自支付宝的通知失败
	{
		echo "response fail";
	}
?>