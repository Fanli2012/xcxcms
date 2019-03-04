<?php
namespace app\wap\controller;

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
        /* if(session('admin_info')['role_id'] <> 1)
        {
            $this->check();
        } */
    }
	
    public function check()
    {
        $uncheckarray = array('Applyindex','Applydelete','Applyoutput','Applylistorder','Applyedit','Applyupdate','Applystatus','Applyinsert');
        if(in_array(MODULE_NAME.ACTION_NAME,$uncheckarray))
        {

        }
        else
        {
        	if(MODULE_NAME!='Index'&&ACTION_NAME!='index')
            {
        		$menu_id = M('Menu')->where(array('model'=>MODULE_NAME,'action'=>ACTION_NAME))->getField('id');
        		$check = M('Access')->where(array('role_id'=>session('admin_info')['role_id'],'menu_id'=>$menu_id))->find();
        			
        		if(empty($check))
                {
        			$this->error('您暂时无权限浏览,请联系管理员！');
        		}
        	}
        }
    }
}
