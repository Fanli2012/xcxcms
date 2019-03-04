<?php

//前台支付接口示例

require_once('../quickpay_service.php');

//下面这行用于测试，以生成随机且唯一的订单号
mt_srand(quickpay_service::make_seed());

$param['transType']             = quickpay_conf::CONSUME;  //交易类型，CONSUME or PRE_AUTH

$param['orderAmount']           = 11000;        //交易金额
$param['orderNumber']           = date('YmdHis') . strval(mt_rand(100, 999)); //订单号，必须唯一
$param['orderTime']             = date('YmdHis');   //交易时间, YYYYmmhhddHHMMSS
$param['orderCurrency']         = quickpay_conf::CURRENCY_CNY;  //交易币种，CURRENCY_CNY=>人民币

$param['customerIp']            = $_SERVER['REMOTE_ADDR'];  //用户IP
$param['frontEndUrl']           = "http://www.example.com/sdk/utf8/front_notify.php";    //前台回调URL
$param['backEndUrl']            = "http://www.example.com/sdk/utf8/back_notify.php";    //后台回调URL

/* 可填空字段
   $param['commodityUrl']          = "http://www.example.com/product?name=商品";  //商品URL
   $param['commodityName']         = '商品名称';   //商品名称
   $param['commodityUnitPrice']    = 11000;        //商品单价
   $param['commodityQuantity']     = 1;            //商品数量
//*/

//其余可填空的参数可以不填写

$pay_service = new quickpay_service($param, quickpay_conf::FRONT_PAY);
$html = $pay_service->create_html();

header("Content-Type: text/html; charset=" . quickpay_conf::$pay_params['charset']);
echo $html; //自动post表单

?>
