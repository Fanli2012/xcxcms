<?php
namespace app\fladmin\controller;

class Searchword extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
        $list = parent::pageList('searchword');
		
		$this->assign('page',$list->render());
        $this->assign('posts',$list);
		
		return $this->fetch();
    }
    
    public function doadd()
    {
        $_POST['pubdate'] = time();//更新时间
        $_POST['click'] = rand(200,500);//点击
        
		if(db('searchword')->insert($_POST))
        {
            $this->success('添加成功！', CMS_ADMIN.'searchword' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'searchword/add' , 3);
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
		$this->assign('row',db('searchword')->where("id=$id")->find());
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        if(!empty($_POST["keywords"])){$_POST['keywords']=str_replace("，",",",$_POST["keywords"]);}else{$_POST['keywords']="";}//关键词
        $_POST['pubdate'] = time();//更新时间
        
		if(db('searchword')->where("id=$id")->update($_POST))
        {
            $this->success('修改成功！', CMS_ADMIN.'searchword' , 1);
        }
		else
		{
			$this->error('修改失败！', CMS_ADMIN.'searchword/edit?id='.$_POST["id"] , 3);
		}
    }
    
	public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交',CMS_ADMIN.'searchword' , 3);} //if(preg_match('/[0-9]*/',$id)){}else{exit;}
		
		if(db("searchword")->where("id in ($id)")->delete())
        {
            $this->success('删除成功', CMS_ADMIN.'searchword' , 1);
        }
		else
		{
			$this->error('删除失败！请重新提交', CMS_ADMIN.'searchword', 3);
		}
    }
}
