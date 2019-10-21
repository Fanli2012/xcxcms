<?php

namespace app\fladmin\controller;

use app\common\lib\Helper;

class Base extends Common
{
    /**
     * 初始化
     * @param void
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

        // 当前账号不是超级管理员，判断是否拥有权限
        if (isset($this->admin_info['role_id']) && $this->admin_info['role_id'] != 1) {
            $this->verifyPermission();
        }
    }

    // 权限验证
    public function verifyPermission()
    {
        $current_controller = Helper::uncamelize(request()->controller()); //驼峰命名转下划线
        $route = request()->module() . '/' . $current_controller . '/' . request()->action();
        // 不需要权限验证的列表
        $uncheck = array(
            'fladmin/index/index',
            'fladmin/index/upconfig',
            'fladmin/index/upcache',
            'fladmin/index/welcome'
        );

        if (!in_array(strtolower($route), $uncheck)) {
            $menu_id = model('Menu')->getValue(array('module' => request()->module(), 'controller' => $current_controller, 'action' => request()->action()), 'id');
            // 是否存在该菜单
            if (!$menu_id) {
                $this->error('你没有权限访问，请联系管理员', url('fladmin/index/index'), '', 3);
            }
            // 判断当前账号是否拥有权限
            $check = db('access')->where(array('role_id' => $this->admin_info['role_id'], 'menu_id' => $menu_id))->find();
            if (!$check) {
                $this->error('你没有权限访问，请联系管理员', url('fladmin/index/index'), '', 3);
            }
        }
    }

}