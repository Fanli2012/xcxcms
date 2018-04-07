<?php
namespace app\fladmin\controller;
use app\common\lib\ReturnData;
use app\common\logic\GoodsLogic;
use app\common\logic\GoodsBrandLogic;

class Goods extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new GoodsLogic();
    }
    
    public function index()
    {
        $where = array();
        if(isset($_REQUEST["keyword"]))
        {
            $where['title'] = array('like','%'.$_REQUEST['keyword'].'%');
        }
        if(isset($_REQUEST["typeid"]) && $_REQUEST["typeid"]!=0)
        {
            $where['typeid'] = $_REQUEST["typeid"];
        }
        if(isset($_REQUEST["id"]))
        {
            $where['typeid'] = $_REQUEST["id"];
        }
        
        $prolist = $this->getLogic()->getPaginate($where, '', ['body']);
		$posts = array();
		foreach($prolist as $key=>$value)
        {
            $value['name'] = db('goods_type')->field('content',true)->where("id=".$value['typeid'])->value('name');
			$posts[] = $value;
        }
		
		$this->assign('page',$prolist->render());
        $this->assign('posts',$posts);
		
		return $this->fetch();
    }
    
    public function add()
    {
		if(!empty($_GET["catid"])){$this->assign('catid',$_GET["catid"]);}else{$this->assign('catid',0);}
		
        $goods_brand_logic = new GoodsBrandLogic();
        $this->assign('goodsbrand_list', $goods_brand_logic->getAll('', 'listorder asc', 'id,title'));
        
        return $this->fetch();
    }
    
    public function doadd()
    {
        $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
        if(empty($_POST["description"])){if(!empty($_POST["body"])){$_POST['description']=cut_str($_POST["body"]);}} //description
        $_POST['add_time'] = $_POST['pubdate'] = time(); //添加&更新时间
		$_POST['user_id'] = session('admin_user_info')['id']; // 发布者id
		
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
				$_POST['keywords']=get_keywords($title);//标题分词
			}
		}
		
        if(isset($_POST['promote_start_date'])){$_POST['promote_start_date'] = strtotime($_POST['promote_start_date']);}
        if(isset($_POST['promote_end_date'])){$_POST['promote_end_date'] = strtotime($_POST['promote_end_date']);}
        if(empty($_POST['promote_price'])){unset($_POST['promote_price']);}
        
        $res = $this->getLogic()->add($_POST);
		if($res['code']==ReturnData::SUCCESS)
        {
            $this->success($res['msg'], url('index'), '', 1);
        }
		else
		{
			$this->error($res['msg']);
		}
    }
    
    public function edit()
    {
        if(!empty($_GET["id"])){$id = $_GET["id"];}else {$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $post = $this->getLogic()->getOne(array('id'=>$id));
        if($post['promote_start_date'] != 0){$post['promote_start_date'] = date('Y-m-d H:i:s',$post['promote_start_date']);}
        if($post['promote_end_date'] != 0){$post['promote_end_date'] = date('Y-m-d H:i:s',$post['promote_end_date']);}
        
        $this->assign('id',$id);
		$this->assign('post', $post);
        
        $goods_brand_logic = new GoodsBrandLogic();
        $this->assign('goodsbrand_list', $goods_brand_logic->getAll('', 'listorder asc', 'id,title'));
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];}else {$id="";exit;}
        
        $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
        if(empty($_POST["description"])){if(!empty($_POST["body"])){$_POST['description']=cut_str($_POST["body"]);}}//description
        $_POST['pubdate'] = time();//更新时间
        $_POST['user_id'] = session('admin_user_info')['id']; // 修改者id
		
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
				$_POST['keywords']=get_keywords($title);//标题分词
			}
		}
		
        if(isset($_POST['promote_start_date'])){$_POST['promote_start_date'] = strtotime($_POST['promote_start_date']);}
        if(isset($_POST['promote_end_date'])){$_POST['promote_end_date'] = strtotime($_POST['promote_end_date']);}
        if(empty($_POST['promote_price'])){unset($_POST['promote_price']);}
        
        $res = $this->getLogic()->edit($_POST,array('id'=>$id));
		if ($res['code'] == ReturnData::SUCCESS)
        {
            $this->success($res['msg'], url('index'), '', 1);
        }
		else
		{
			$this->error($res['msg']);
		}
    }
    
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');}if(preg_match('/[0-9]*/',$id)){}else{exit;}
		
		if(db('goods')->where("id in ($id)")->delete())
        {
            $this->success("$id ,删除成功", url('index'), '', 1);
        }
		else
		{
			$this->error("$id ,删除失败！请重新提交");
		}
    }
    
	//商品推荐
	public function recommendarc()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');} //if(preg_match('/[0-9]*/',$id)){}else{exit;}
		
		$data['tuijian'] = 1;

        if(db('goods')->where("id in ($id)")->update($data))
        {
            $this->success("$id ,推荐成功", url('index'), '', 1);
        }
		else
		{
			$this->error("$id ,推荐失败！请重新提交");
		}
    }
    
	//商品是否存在
    public function goodsexists()
    {
        if(!empty($_GET["title"]))
        {
            $map['title'] = $_GET["title"];
        }
        else
        {
            $map['title']="";
        }
        
        if(!empty($_GET["id"]))
        {
            $map['id'] = array('NEQ',$_GET["id"]);
        }
        
        return db('goods')->where($map)->count();
    }
}