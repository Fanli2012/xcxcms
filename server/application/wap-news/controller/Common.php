<?php
namespace app\wap\controller;
use think\Request;
use think\Db;
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
        $route = request()->module().'/'.request()->controller().'/'.request()->action();
        
        $this->login_info = session('shop_info');
        $this->assign('login_info', $this->login_info);
        
        //判断是否拥有权限
		/* if($this->shop_info['role_id'] <> 1)
		{
			$uncheck = array('shop/index/index','shop/index/upconfig','shop/index/upcache','shop/index/welcome');
            
			if(!in_array($route, $uncheck))
			{
				
			}
        } */
    }
	
    //设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除');
    }
}