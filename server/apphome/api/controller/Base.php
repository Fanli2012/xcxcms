<?php
namespace app\api\controller;

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
        
        //权限验证
        /* if(session('admin_user_info')['role_id'] <> 1)
        {
            $this->check();
        } */
    }
}
