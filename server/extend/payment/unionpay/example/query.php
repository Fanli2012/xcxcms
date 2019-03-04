<?php

//查询接口示例

require_once('../quickpay_service.php');

//* 测试数据
$transType   = quickpay_conf::CONSUME;
$orderNumber = "20111108150703852";
$orderTime   = "20111108150703";
// */

//需要填入的部分
$param['transType']     = $transType;   //交易类型
$param['orderNumber']   = $orderNumber; //订单号
$param['orderTime']     = $orderTime;   //订单时间

//提交查询
$query  = new quickpay_service($param, quickpay_conf::QUERY);
$ret    = $query->post();

//返回查询结果
$response = new quickpay_service($ret, quickpay_conf::RESPONSE);

//后续处理
$arr_ret = $response->get_args();
echo "查询请求返回：<pre>\n" .  var_export($arr_ret, true) . "</pre>";

$respCode = $response->get('respCode');
$queryResult = $response->get('queryResult');

if ($queryResult == quickpay_service::QUERY_FAIL) 
{
    echo "交易失败[respCode:{$respCode}]!\n";
    //更新数据库, 设置为交易失败
}
else if ($queryResult == quickpay_service::QUERY_INVALID) {
    //出错
    echo "不存在此交易!\n";
}
else if ($respCode == quickpay_service::RESP_SUCCESS
        && $queryResult == quickpay_service::QUERY_SUCCESS)
{
    echo "交易成功!\n";
    //更新数据库, 设置为交易成功
}
else if ($respCode == quickpay_service::RESP_SUCCESS
        && $queryResult == quickpay_service::QUERY_WAIT)
{
    echo "交易处理中，下次再查!\n";
}
else 
{
    //其他异常错误
    $err = sprintf("Error[respCode:%d]", $response->get('respCode'));
    throw new Exception($err);
}


?>
