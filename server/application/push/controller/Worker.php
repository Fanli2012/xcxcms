<?php

namespace app\push\controller;

use think\worker\Server;
use think\Log;

class Worker extends Server
{
    // push.app是你的本地测试域名
    protected $socket = 'websocket://testnbnbk.xyabb.com:2346';

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        Log::info('服务端接收到客户端发送过来的消息：' . json_encode($data));
        $connection->send(date('Y-m-d H:i:s') . '：我收到你的信息了');
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {

    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {

    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {

    }
}