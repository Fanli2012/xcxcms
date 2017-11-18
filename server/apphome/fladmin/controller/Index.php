<?php
namespace app\fladmin\controller;

class Index extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
	public function index()
	{
        $this->assign('menus',model('menu')->getPermissionsMenu($this->admin_user_info['role_id']));
        
        return $this->fetch();
    }
    
    public function welcome()
	{
        return $this->fetch();
    }
	
    public function upconfig()
	{
        updateconfig();
        $this->success('缓存更新成功！');
    }
    
    public function upcache()
	{
        dir_delete(APP_PATH.'../runtime/');
        $this->success('缓存更新成功！');
    }
}
