<?php
namespace app\fladmin\controller;

class Sysconfig extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
		$this->assign('posts',db("sysconfig")->order('id desc')->select());
        return $this->fetch();
    }
    
	//添加参数，视图
    public function add()
    {
        return $this->fetch();
    }
    
	//修改参数，视图
    public function edit()
    {
        if(!empty($_REQUEST["id"])){$id = $_REQUEST["id"];}else{$id="";}
        if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
		$this->assign('id',$id);
		$this->assign('row',db('sysconfig')->where("id=$id")->find());
		
        return $this->fetch();
    }
    
    public function doadd()
    {
        //参数名称
        if(!empty($_POST["varname"]))
        {
			if(!preg_match("/^CMS_[a-z]+$/i", $_POST["varname"]))
			{
				$this->error('添加失败！参数名称不正确', CMS_ADMIN.'Sysconfig/add');exit;
			}
        }
        else
        {
            $this->error('添加失败！参数名称不能为空', CMS_ADMIN.'Sysconfig/add');exit;
        }
		
		if($_POST['varname']!="" && db('sysconfig')->insert($_POST))
        {
            updateconfig();
            $this->success('添加成功！', CMS_ADMIN.'Sysconfig' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'Sysconfig/add');
		}
    }
    
    public function doedit()
    {
        if(isset($_POST["id"]) && !empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        
        //参数名称
        if(!empty($_POST["varname"]))
        {
            if(!preg_match("/^CMS_[a-z]+$/i", $_POST["varname"]))
			{
				$this->error('更新失败！参数名称不正确', CMS_ADMIN.'Sysconfig/edit?id='.$id);exit;
			}
        }
        else
        {
            $this->error('更新失败！参数名称不能为空', CMS_ADMIN.'Sysconfig/edit?id='.$id);exit;
        }
		
		if(db('sysconfig')->where("id=$id")->update($_POST))
        {
            updateconfig();
            $this->success('更新成功！', CMS_ADMIN.'Sysconfig');
        }
		else
		{
			$this->error('更新失败！请修改后重新提交', CMS_ADMIN.'Sysconfig/edit?id='.$id);
		}
    }
    
    public function del()
    {
		if(!empty($_REQUEST["id"])){$id = $_REQUEST["id"];}else{$this->error('删除失败！请重新提交',CMS_ADMIN.'Sysconfig');}
		
		if(db("sysconfig")->where("id in ($id)")->delete())
        {
            $this->success('删除成功', CMS_ADMIN.'Sysconfig');
        }
		else
		{
			$this->error('删除失败！请重新提交', CMS_ADMIN.'Sysconfig');
		}
    }
}