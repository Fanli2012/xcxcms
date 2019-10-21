<?php

namespace app\shop\controller;

class Base extends Common
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

        $route = request()->module() . '/' . request()->controller() . '/' . request()->action();

        if (!session('shop_info')) {
            $this->error('您访问的页面不存在或已被删除', '/', '', 3);
        }

        //判断是否拥有权限
        /* if($this->shop_info['role_id'] <> 1)
        {
            $uncheck = array('shop/index/index','shop/index/upconfig','shop/index/upcache','shop/index/welcome');

            if(!in_array($route, $uncheck))
            {

            }
        } */
    }

    public function check()
    {
        $uncheckarray = array('Applyindex', 'Applydelete', 'Applyoutput', 'Applylistorder', 'Applyedit', 'Applyupdate', 'Applystatus', 'Applyinsert');
        if (in_array(MODULE_NAME . ACTION_NAME, $uncheckarray)) {

        } else {
            if (MODULE_NAME != 'Index' && ACTION_NAME != 'index') {
                $menu_id = M('Menu')->where(array('model' => MODULE_NAME, 'action' => ACTION_NAME))->getField('id');
                $check = M('Access')->where(array('role_id' => session('admin_info')['role_id'], 'menu_id' => $menu_id))->find();

                if (empty($check)) {
                    $this->error('您暂时无权限浏览,请联系管理员！');
                }
            }
        }
    }
}
