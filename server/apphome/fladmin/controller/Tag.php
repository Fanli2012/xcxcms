<?php
namespace app\fladmin\controller;

class Tag extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
        $list = parent::pageList('tagindex');
		
		$this->assign('page',$list->render());
        $this->assign('posts',$list);
		
		return $this->fetch();
    }
    
    public function doadd()
    {
		$tagarc="";
		if(!empty($_POST["tagarc"])){$tagarc = str_replace("，",",",$_POST["tagarc"]);if(!preg_match("/^\d*$/",str_replace(",","",$tagarc))){$tagarc="";}} //Tag文章列表
        
        $_POST['pubdate'] = time();//更新时间
        $_POST['click'] = rand(200,500);//点击
        unset($_POST["tagarc"]);
        
		if($insertId = db('tagindex')->insert($_POST))
        {
            if($tagarc!="")
            {
                $arr=explode(",",$tagarc);
                
                foreach($arr as $row)
                {
                    $data2['tid'] = $insertId;
                    $data2['aid'] = $row;
                    db("taglist")->insert($data2);
                }
            }
            $this->success('添加成功！', CMS_ADMIN.'Tag' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'Tag/add' , 3);
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
		$this->assign('row',db('tagindex')->where("id=$id")->find());
        
        //获取该标签下的文章id
        $posts = db('taglist')->field('aid')->where("tid=$id")->select();
        $aidlist = "";
        if(!empty($posts))
        {
            foreach($posts as $row)
            {
                $aidlist=$aidlist.','.$row['aid'];
            }
        }
        $this->assign('aidlist',ltrim($aidlist, ","));
		
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        if(!empty($_POST["keywords"])){$_POST['keywords']=str_replace("，",",",$_POST["keywords"]);}else{$_POST['keywords']="";}//关键词
        $_POST['pubdate'] = time();//更新时间
        $tagarc="";
		if(!empty($_POST["tagarc"])){$tagarc = str_replace("，",",",$_POST["tagarc"]);if(!preg_match("/^\d*$/",str_replace(",","",$tagarc))){$tagarc="";}} //Tag文章列表
        unset($_POST["tagarc"]);
        
		if(db('tagindex')->where("id=$id")->update($_POST))
        {
            //获取该标签下的文章id
            $posts = db("taglist")->field('aid')->where("tid=$id")->select();
            $aidlist = "";
            if(!empty($posts))
            {
                foreach($posts as $row)
                {
                    $aidlist = $aidlist.','.$row['aid'];
                }
            }
            $aidlist = ltrim($aidlist, ",");
            
            if($tagarc!="" && $tagarc!=$aidlist)
            {
                db("taglist")->where("tid=$id")->delete();
                
                $arr=explode(",",$tagarc);
                    
                foreach($arr as $row)
                {
                    $data2['tid'] = $id;
                    $data2['aid'] = $row;
                    db("taglist")->insert($data2);
                }
            }
            elseif($tagarc=="")
            {
                db("taglist")->where("tid=$id")->delete();
            }
            
            $this->success('修改成功！', CMS_ADMIN.'Tag' , 1);
        }
		else
		{
			$this->error('修改失败！', CMS_ADMIN.'Tag/edit?id='.$_POST["id"] , 3);
		}
    }
    
	public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交',CMS_ADMIN.'Tag' , 3);} //if(preg_match('/[0-9]*/',$id)){}else{exit;}
		
		if(db("tagindex")->where("id in ($id)")->delete())
        {
            $this->success('删除成功', CMS_ADMIN.'Tag' , 1);
        }
		else
		{
			$this->error('删除失败！请重新提交', CMS_ADMIN.'Tag', 3);
		}
    }
}
