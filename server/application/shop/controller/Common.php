<?php

namespace app\shop\controller;

use think\Request;
use think\Db;
use think\Log;
use think\Session;
use think\Controller;

class Common extends Controller
{
    protected $login_info;

    /**
     * 初始化
     * @param void
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->login_info = session('shop_info');
        $this->assign('login_info', $this->login_info);

        //请求日志
        Log::info('【请求地址】：' . request()->ip() . ' [' . date('Y-m-d H:i:s') . '] ' . request()->method() . ' ' . '/' . request()->module() . '/' . request()->controller() . '/' . request()->action());
        Log::info('【请求参数】：' . json_encode(request()->param(), JSON_UNESCAPED_SLASHES));
        Log::info('【请求头】：' . json_encode(request()->header(), JSON_UNESCAPED_SLASHES));
    }

    //设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除');
    }
}