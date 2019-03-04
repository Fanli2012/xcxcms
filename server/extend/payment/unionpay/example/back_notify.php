<?php

require_once('../quickpay_service.php');

try {
    $response = new quickpay_service($_POST, quickpay_conf::RESPONSE);
    if ($response->get('respCode') != quickpay_service::RESP_SUCCESS) {
        $err = sprintf("Error: %d => %s", $response->get('respCode'), $response->get('respMsg'));
        throw new Exception($err);
    }

    $arr_ret = $response->get_args();

    //更新数据库，将交易状态设置为已付款
    //注意保存qid，以便调用后台接口进行退货/消费撤销

    //以下仅用于测试
    file_put_contents('notify.txt', var_export($arr_ret, true));

}
catch(Exception $exp) {
    //后台通知出错
    file_put_contents('notify.txt', var_export($exp, true));
}

?>
