<?php

namespace app\fladmin\controller;

use think\Controller;

class Common extends Controller
{
    protected $admin_info;

    /**
     * 初始化
     * @param void
     * @return void
     */
    public function _initialize()
    {
        // 未登录
        if (!session('admin_info')) {
            $this->error('您访问的页面不存在或已被删除', '/', '', 3);
        }

        $this->admin_info = session('admin_info');
        $this->assign('admin_info', $this->admin_info);
    }

    // 设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除');
    }
}