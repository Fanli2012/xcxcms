<?php
namespace app\miniprogram\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;

class Common extends Controller
{
    protected $miniprogram_user_info; //登录信息
    
	public function _initialize()
	{
        $route = strtolower(request()->module().'/'.request()->controller().'/'.request()->action());
        
        //不需要登录的方法
        $uncheck = array('miniprogram/index/index','miniprogram/login/index','miniprogram/login/componentverifyticket');
        if(!in_array($route, $uncheck))
        {
            //判断是否登录
            if(!Session::has('miniprogram_user_info'))
            {
                $this->error('请先登录', url('miniprogram/login/index'), '', 3);
            }
            
            $this->miniprogram_user_info = Session::get('miniprogram_user_info');
        }
    }
	
    //设置空操作
    public function _empty()
    {
        $this->error('您访问的页面不存在或已被删除！');
    }
}