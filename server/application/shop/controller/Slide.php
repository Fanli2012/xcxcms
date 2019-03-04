<?php
namespace app\shop\controller;

class Slide extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
		$list = parent::pageList('slide','','is_show asc,rank desc');
		
		$this->assign('page',$list->render());
        $this->assign('posts',$list);
		
		return $this->fetch();
    }
    
    public function doadd()
    {
		if(isset($_POST['editorValue'])){unset($_POST['editorValue']);}
		if(db('slide')->insert($_POST))
        {
            $this->success('添加成功', CMS_ADMIN.'Slide' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'Slide/add' , 3);
		}
    }
    
    public function add()
    {
        return $this->fetch();
    }
    
    public function edit()
    {
        if(!empty($_GET["id"])){$id = $_GET["id"];}else{$id="";}
        if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('id',$id);
		$this->assign('row',db('slide')->where("id=$id")->find());
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        if(isset($_POST['editorValue'])){unset($_POST['editorValue']);}
		
		if(db('slide')->where("id=$id")->update($_POST))
        {
            $this->success('修改成功', CMS_ADMIN.'Slide' , 1);
        }
		else
		{
			$this->error('修改失败', CMS_ADMIN.'Slide/edit?id='.$id , 3);
		}
    }
    
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交',CMS_ADMIN.'Slide' , 3);}
		
		if(db('slide')->where("id in ($id)")->delete())
        {
            $this->success('删除成功', CMS_ADMIN.'Slide' , 1);
        }
		else
		{
			$this->error('删除失败！请重新提交', CMS_ADMIN.'Slide', 3);
		}
    }
}