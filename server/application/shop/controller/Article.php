<?php
namespace app\shop\controller;
use think\Db;
use app\common\lib\ReturnData;
use app\common\logic\ArticleLogic;

class Article extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
	
    public function getLogic()
    {
        return new ArticleLogic();
    }
    
    public function index()
    {
		$where = array();
        if(!empty($_REQUEST["keyword"]))
        {
            $where['title'] = array('like','%'.$_REQUEST['keyword'].'%');
        }
        if(!empty($_REQUEST["type_id"]) && $_REQUEST["type_id"]>0)
        {
            $where['type_id'] = $_REQUEST["type_id"];
        }
        $where['delete_time'] = 0; //未删除
        $where['shop_id'] = $this->login_info['id'];
        $list = $this->getLogic()->getPaginate($where,['tuijian'=>'desc','update_time'=>'desc'],['content'],15);
		
		$this->assign('page',$list->render());
        $this->assign('list',$list);
		//echo '<pre>';var_dump($list);exit;
		return $this->fetch();
    }
    
    public function add()
    {
        if($this->login_info['status']==0){$this->error('请先完善资料', url('shop/Shop/setting'));}
        
        $where['shop_id'] = $this->login_info['id'];
        $where['delete_time'] = 0;
        $count = model('ArticleType')->getCount($where);
        if($count>0){}else{$this->error('请先添加分类', url('shop/ArticleType/add'));}
        
        $article_type_list = model('ArticleType')->getAll($where,['listorder'=>'asc'],['content'],15);
        $this->assign('article_type_list',$article_type_list);
        
        return $this->fetch();
    }
    
    public function doadd()
    {
        $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
        if(empty($_POST["description"])){if(!empty($_POST["content"])){$_POST['description']=cut_str($_POST["content"]);}} //description
        $content="";if(!empty($_POST["content"])){$content = $_POST["content"];}
        
        $_POST['add_time'] = $_POST['update_time'] = time(); // 更新时间
		$_POST['shop_id'] = $this->login_info['id']; // 发布者ID
        $_POST['click'] = rand(200,500);
        
		//关键词
        if(!empty($_POST["keywords"]))
        {
            $_POST['keywords']=str_replace("，",",",$_POST["keywords"]);
        }
        else
        {
            if(!empty($_POST["title"]))
            {
                $title=$_POST["title"];
                $title=str_replace("，","",$title);
                $title=str_replace(",","",$title);
                $_POST['keywords']=get_participle($title); // 标题分词
            }
        }
        
        if(isset($_POST["dellink"]) && $_POST["dellink"]==1 && !empty($content)){$content=logic('Article')->replacelinks($content,array(sysconfig('CMS_BASEHOST')));} //删除非站内链接
        $_POST['content']=$content;
        
        // 提取第一个图片为缩略图
        if(isset($_POST["autolitpic"]) && $_POST["autolitpic"] && empty($litpic))
        {
            $litpic = logic('Article')->getBodyFirstPic($content);
            if($litpic)
            {
                $_POST['litpic'] = $litpic;
            }
        }
		
        $res = $this->getLogic()->add($_POST);
		if($res['code']==ReturnData::SUCCESS)
        {
            $this->success('添加成功', url('index'));
        }
		
        $this->error($res['msg']);
    }
    
    public function edit()
    {
        if($this->login_info['status']==0){$this->error('请先完善资料', url('shop/Shop/setting'));}
        
        if(!checkIsNumber(input('id',null))){$this->error('参数错误');}
        $where['id'] = input('id');
        $this->assign('id', $where['id']);
        
        $where['shop_id'] = $where2['shop_id'] = $this->login_info['id'];
		$this->assign('post',$this->getLogic()->getOne($where));
        
        $where2['delete_time'] = 0;
        $count = model('ArticleType')->getCount($where2);
        if($count>0){}else{$this->error('请先添加分类', url('shop/article_type/add'));}
        
        $article_type_list = model('ArticleType')->getAll($where2,['listorder'=>'asc'],['content'],15);
        $this->assign('article_type_list',$article_type_list);
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        $id=$where['id'] = $_POST['id'];
        unset($_POST['id']);
        
        $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
        if(empty($_POST["description"])){if(!empty($_POST["content"])){$_POST['description']=cut_str($_POST["content"]);}} //description
        $content="";if(!empty($_POST["content"])){$content = $_POST["content"];}
        $_POST['update_time'] = time();//更新时间
        $where['shop_id'] = $this->login_info['id']; // 发布者ID
        
		//关键词
        if(!empty($_POST["keywords"]))
        {
            $_POST['keywords']=str_replace("，",",",$_POST["keywords"]);
        }
        else
        {
            if(!empty($_POST["title"]))
            {
                $title=$_POST["title"];
                $title=str_replace("，","",$title);
                $title=str_replace(",","",$title);
                $_POST['keywords']=get_participle($title); // 标题分词
            }
        }
        
        if(isset($_POST["dellink"]) && $_POST["dellink"]==1 && !empty($content)){$content=logic('Article')->replacelinks($content,array(sysconfig('CMS_BASEHOST')));} //删除非站内链接
        $_POST['content']=$content;
        
        // 提取第一个图片为缩略图
        if(isset($_POST["autolitpic"]) && $_POST["autolitpic"] && empty($litpic))
        {
            $litpic = logic('Article')->getBodyFirstPic($content);
            if($litpic)
            {
                $_POST['litpic'] = $litpic;
            }
        }
        
        $res = $this->getLogic()->edit($_POST, $where);
        if($res['code']==ReturnData::SUCCESS)
        {
            $this->success('修改成功', url('index'), '', 1);
        }
		
        $this->error($res['msg']);
    }
    
    public function del()
    {
        if(!checkIsNumber(input('id',null))){$this->error('参数错误');}
        $where['id'] = input('id');
        $where['shop_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->del($where);
        
		if($res['code'] == ReturnData::SUCCESS)
        {
            $this->success("删除成功");
        }
		
        $this->error($res['msg']);
    }
}