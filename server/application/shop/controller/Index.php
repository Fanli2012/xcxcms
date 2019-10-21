<?php

namespace app\shop\controller;

class Index extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->assign('post', logic('Shop')->getOne(['id' => $this->login_info['id']]));

        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }

    public function upconfig()
    {
        //updateconfig();
        $this->success('缓存更新成功');
    }

    public function upcache()
    {
        dir_delete(APP_PATH . '../runtime/');
        $this->success('缓存更新成功');
    }
}