<?php

//后台接口示例

require_once('../quickpay_service.php');

//下面这行用于测试，以生成随机且唯一的订单号
mt_srand(quickpay_service::make_seed());

//交易类型 退货=REFUND 或 消费撤销=CONSUME_VOID, 如果原始交易是PRE_AUTH，那么后台接口也支持对应的
//  PRE_AUTH_VOID(预授权撤销), PRE_AUTH_COMPLETE(预授权完成), PRE_AUTH_VOID_COMPLETE(预授权完成撤销)
$param['transType']             = quickpay_conf::REFUND;  

$param['origQid']               = '201110281442120195882'; //原交易返回的qid, 从数据库中获取

$param['orderAmount']           = 11000;        //交易金额
$param['orderNumber']           = date('YmdHis') . strval(mt_rand(100, 999)); //订单号，必须唯一(不能与原交易相同)
$param['orderTime']             = date('YmdHis');   //交易时间, YYYYmmhhddHHMMSS
$param['orderCurrency']         = quickpay_conf::CURRENCY_CNY;  //交易币种，

$param['customerIp']            = $_SERVER['REMOTE_ADDR'];  //用户IP
$param['frontEndUrl']           = "";    //前台回调URL, 后台交易可为空
$param['backEndUrl']            = "http://www.example.com/sdk/utf8/back_notify.php";    //后台回调URL

//其余可填空的参数可以不填写

//提交
$pay_service = new quickpay_service($param, quickpay_conf::BACK_PAY);
$ret = $pay_service->post();

//同步返回（表示服务器已收到后台接口请求）, 处理成功与否以后台通知为准；或使用主动查询
$response = new quickpay_service($ret, quickpay_conf::RESPONSE);
if ($response->get('respCode') != quickpay_service::RESP_SUCCESS) { //错误处理
    $err = sprintf("Error: %d => %s", $response->get('respCode'), $response->get('respMsg'));
    throw new Exception($err);
}

//后续处理
$arr_ret = $response->get_args();

echo "后台交易返回：\n" . var_export($arr_ret, true); //此行仅用于测试输出

?>
