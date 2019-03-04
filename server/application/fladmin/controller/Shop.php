<?php
namespace app\fladmin\controller;
use think\Db;
use app\common\lib\ReturnData;
use app\common\logic\ShopLogic;
use app\common\lib\Helper;

class Shop extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
	
    public function getLogic()
    {
        return new ShopLogic();
    }
    
    public function index()
    {
		$where = array();
        if(!empty($_REQUEST["keyword"]))
        {
            $where['company_name'] = array('like','%'.$_REQUEST['keyword'].'%');
        }
        if(!empty($_REQUEST["typeid"]) && $_REQUEST["typeid"]!=0)
        {
            $where['typeid'] = $_REQUEST["typeid"];
        }
        if(!empty($_REQUEST["id"]))
        {
            $where['typeid'] = $_REQUEST["id"];
        }
        $where['delete_time'] = 0; //未删除
        if(!empty($_REQUEST["status"]))
        {
            $where['status'] = $_REQUEST["status"];
        }
        if(isset($_REQUEST["tuijian"]))
        {
            $where['tuijian'] = $_REQUEST["tuijian"];
        }
        if(isset($_REQUEST["category_id"]))
        {
            $where['category_id'] = $_REQUEST["category_id"];
        }
        
        $posts = $this->getLogic()->getPaginate($where,'id desc',['body'],15);
		
		$this->assign('page',$posts->render());
        $this->assign('posts',$posts);
		
		return $this->fetch();
    }
    
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['click'] = rand(200,500);
            $_POST['status'] = 1; //正常
            
            if($this->getLogic()->getOne(['user_name'=>$_POST['user_name']])){$this->error('用户名已存在');}
            
            $res = $this->getLogic()->add($_POST);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'));
            }
            
            $this->error($res['msg']);
        }
        
        return $this->fetch();
    }
    
    //采集文章
    public function caijiadd($caiji_data)
    {
        if($caiji_data['shop_id']>0){}else{$this->error('参数错误');}
        $where = [];
        $where['标题'] = ['like','%'.$caiji_data['title'].'%'];
        //$where['typeid'] = 23;
        $aaa = db('aaa')->where($where)->order('rand()')->limit(500)->select();
        if($aaa)
        {
            foreach($aaa as $k=>$v)
            {
                //要记得改的
                $data['shop_id']=$data['typeid']=$caiji_data['shop_id']; //分类id
                //$data['shop_id']=120; //店铺id
                
                $chinese = new Utf8Chinese();
                
                $data['title']=cut_str($v["标题"]);
                $data['title']=$chinese->gb2312_to_big5($data['title']);
                $data['description']=cut_str($v["内容"]);
                $data['description']=$chinese->gb2312_to_big5($data['description']);
                
				$max_add_time = db('article')->max('add_time');
				$time = 200 + $max_add_time + rand(100,300);
                $data['add_time'] = $data['updated_at'] = $time;//添加时间
				
                $title=$v["标题"];
				$title=str_replace("，","",$title);
				$title=str_replace(",","",$title);
				$data['keywords']=get_keywords($title);//标题分词
                $data['keywords']=$chinese->gb2312_to_big5($data['keywords']);
                
                $content=replacelinks($v["内容"],array(sysconfig('CMS_BASEHOST')));
                $data['body']=$chinese->gb2312_to_big5($content);
                $data['click'] = rand(200,500);
                
                $youarticle = logic('Article')->getOne(['title'=>$data['title']]);
                if($youarticle)
                {
                    
                }
                else
                {
                    db('article')->insert($data);
                    echo $data['title'].'---'.$v["id"].'成功---<br>';
                }
                
                db('aaa')->where(['id'=>$v["id"]])->delete();
            }
        }
    }
    
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);
            
            if($this->getLogic()->getOne(['user_name'=>$_POST['user_name'],'id'=>['<>',$where['id']]])){$this->error('用户名已存在');}
            
            $res = $this->getLogic()->edit($_POST,$where);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'));
            }
            
            $this->error($res['msg']);
        }
        
        if(!empty($_GET["id"])){$id = $_GET["id"];}else {$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('id', $id);
        
        $post = $this->getLogic()->getOne("id=$id");
        $this->assign('post', $post);
        
        return $this->fetch();
    }
    
    public function tuijian()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        unset($_GET['id']);
        $where['id'] = $id;
        
        $res = model('Shop')->edit(['tuijian'=>$_GET['tuijian']], $where);
        if($res)
        {
            $this->success('操作成功');
        }
        
        $this->error('操作失败');
    }
    
    //通过审核
    public function tongguo()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        unset($_GET['id']);
        $where['id'] = $id;
        
        $res = model('Shop')->edit(array('status'=>1),$where);
        if($res)
        {
            $this->success('操作成功');
        }
        
        $this->error('操作失败');
    }
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        unset($_GET['id']);
        $where['id'] = $id;
        
        $res = $this->getLogic()->del($where);
        if($res['code'] == ReturnData::SUCCESS)
        {
            $this->success($res['msg']);
        }
        
        $this->error($res['msg']);
    }
}