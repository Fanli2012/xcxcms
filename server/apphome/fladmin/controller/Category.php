<?php
namespace app\fladmin\controller;

class Category extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
        return $this->fetch();
    }
    
    public function add()
    {
        if(!empty($_GET["reid"]))
        {
            $id = $_GET["reid"];
            if(preg_match('/[0-9]*/',$id)){}else{exit;}
            if($id!=0)
            {
				$this->assign('postone',db("arctype")->field('content',true)->where("id=$id")->find());
            }
			
            $this->assign('id',$id);
        }
        else
        {
            $this->assign('id',0);
        }
        
        return $this->fetch();
    }
    
    public function doadd()
    {
        if(!empty($_POST["prid"])){if($_POST["prid"]=="top"){$_POST['reid']=0;}else{$_POST['reid'] = $_POST["prid"];}}//父级栏目id
        $_POST['addtime'] = time();//添加时间
		unset($_POST["prid"]);
		
		if(db('arctype')->insert($_POST))
        {
            $this->success('添加成功！', CMS_ADMIN.'Category' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'Category' , 3);
		}
    }
    
    public function edit()
    {
        $id = $_GET["id"];if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
		$this->assign('id',$id);
        $post = db('arctype')->where("id=$id")->find();
        $reid = $post['reid'];
        if($reid!=0){$this->assign('postone',db('arctype')->where("id=$reid")->find());}
        
        $this->assign('post',$post);
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else {$id="";exit;}
        $_POST['addtime'] = time();//添加时间
        
		if(db('arctype')->where("id=$id")->update($_POST))
        {
            $this->success('修改成功！', CMS_ADMIN.'Category' , 1);
        }
		else
		{
			$this->error('修改失败！请修改后重新添加', CMS_ADMIN.'Category/edit?id='.$id , 3);
		}
    }
    
    public function del()
    {
		if(!empty($_REQUEST["id"])){$id = $_REQUEST["id"];}else{$this->error('删除失败！请重新提交',CMS_ADMIN.'Category' , 3);} //if(preg_match('/[0-9]*/',$id)){}else{exit;}
		
		if(db('arctype')->where("reid=$id")->find())
		{
			$this->error('删除失败！请先删除子栏目', CMS_ADMIN.'Category', 3);
		}
		else
		{
			if(db('arctype')->where("id=$id")->delete())
			{
				if(db("article")->where("typeid=$id")->count()>0) //判断该分类下是否有文章，如果有把该分类下的文章也一起删除
				{
					if(db("article")->where("typeid=$id")->delete())
					{
						$this->success('删除成功', CMS_ADMIN.'Category' , 1);
					}
					else
					{
						$this->error('栏目下的文章删除失败！', CMS_ADMIN.'Category', 3);
					}
				}
				else
				{
					$this->success('删除成功', CMS_ADMIN.'Category' , 1);
				}
			}
			else
			{
				$this->error('删除失败！请重新提交', CMS_ADMIN.'Category', 3);
			}
		}
    }
}