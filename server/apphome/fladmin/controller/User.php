<?php
namespace app\fladmin\controller;

class User extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
        $posts = parent::pageList('user');
        
        $this->assign('page',$posts->render());
		$this->assign('posts',$posts);
		return $this->fetch();
    }
	
	public function add()
    {
		$this->assign('rolelist',db('user_role')->order('listorder desc')->select());
        
        return $this->fetch();
    }
    
    public function doadd()
    {
		$_POST['pwd'] = md5($_POST['pwd']);
		if(db('user')->insert($_POST))
        {
			$this->success('添加成功！', CMS_ADMIN.'User' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'User/add' , 3);
		}
    }
    
    public function edit()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$id="";}
        if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('id',$id);
        $this->assign('post',db('user')->where('id='.$id)->find());
        $this->assign('rolelist',db('user_role')->order('listorder desc')->select());
        
        return $this->fetch();
    }
	
	public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else {$id="";exit;}
        
		unset($_POST["_token"]);
		$_POST['pwd'] = md5($_POST['pwd']);
		if(db('user')->where('id='.$id)->update($_POST))
        {
            $this->success('修改成功！', CMS_ADMIN.'User' , 1);
        }
		else
		{
			$this->error('修改失败！', CMS_ADMIN.'User' , 3);
		}
    }
	
	public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');}
		
		if(db("user")->where("id in ($id)")->delete())
        {
            $this->success('删除成功');
        }
		else
		{
			$this->error('删除失败！请重新提交');
		}
    }
}
