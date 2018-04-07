<?php
namespace app\fladmin\controller;

class Menu extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
        $posts = parent::pageList('menu');
		
        $this->assign('page',$posts->render());
		$this->assign('posts',$posts);
		return $this->fetch();
    }
	
	public function add()
    {
		if(!empty($_GET["pid"])){$pid = $_GET["pid"];}else{$pid=0;}
        
		$this->assign('menu',model('menu')->category_tree(model('menu')->get_category('menu',0)));
        $this->assign('pid',$pid);
        return $this->fetch();
    }
    
    public function doadd()
    {
		$menuid = db('menu')->strict(false)->insertGetId($_POST);
		if($menuid)
        {
			if(!db('access')->where(['role_id' => 1, 'menu_id' => $menuid])->find()){db('access')->strict(false)->insertGetId(['role_id' => 1, 'menu_id' => $menuid]);}
			
			$this->success('添加成功！', url('index'), 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加');
		}
    }
    
    public function edit()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$id="";}
        if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('post',db('menu')->where('id='.$id)->find());
		$this->assign('menu',model('menu')->category_tree(model('menu')->get_category('menu',0)));
        $this->assign('id',$id);
        
        return $this->fetch();
    }
	
	public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else {$id="";exit;}
        
		if(db('menu')->where('id='.$id)->update($_POST))
        {
            $this->success('修改成功！', url('index'), 1);
        }
		else
		{
			$this->error('修改失败！');
		}
    }
	
	public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');}
		
		if(db("menu")->where("id in ($id)")->delete())
        {
			db('access')->where('role_id=1')->where("menu_id in ($id)")->delete();
			
            $this->success('删除成功');
        }
		else
		{
			$this->error('删除失败！请重新提交');
		}
    }
}