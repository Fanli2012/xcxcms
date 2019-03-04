<?php
namespace app\fladmin\controller;
use think\Db;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
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
        $where['status'] = 0; //审核过的文章
        if(!empty($_REQUEST["status"]))
        {
            $where['status'] = $_REQUEST["status"]; //未审核过的文章
        }
        
        $list = $this->getLogic()->getPaginate($where,['update_time'=>'desc'], ['content']);
		
		$this->assign('page',$list->render());
        $this->assign('list',$list);
		//echo '<pre>';print_r($list);exit;
        
        //分类列表
        $article_type_list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $this->assign('article_type_list',$article_type_list);
        
		return $this->fetch();
        
        //if(!empty($_GET["id"])){$id = $_GET["id"];}else {$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        /* if(!empty($id)){$map['typeid']=$id;}
        $Article = M("Article")->field('id')->where($map);
        $counts = $Article->count();
        
        $pagesize =CMS_PAGESIZE;$page =0;
        if($counts % $pagesize){ //取总数据量除以每页数的余数
        $pages = intval($counts/$pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
        }else{$pages = $counts/$pagesize;}
        if(!empty($_GET["page"])){$page = $_GET["page"]-1;$nextpage=$_GET["page"]+1;$previouspage=$_GET["page"]-1;}else{$page = 0;$nextpage=2;$previouspage=0;}
        if($counts>0){if($page>$pages-1){exit;}}
        $start = $page*$pagesize;
        $Article = M("Article")->field('id,typeid,title,pubdate,click,litpic,tuijian')->where($map)->order('id desc')->limit($start,$pagesize)->select();
        
        $this->counts = $counts;
		$this->pages = $pages;
        $this->page = $page;
        $this->nextpage = $nextpage;
        $this->previouspage = $previouspage;
        $this->id = $id;
        $this->posts = $Article; */
        
        //echo '<pre>';
        //print_r($Article);
        //return $this->fetch();
    }
    
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
            if(empty($_POST["description"])){if(!empty($_POST["content"])){$_POST['description']=cut_str($_POST["content"]);}} //description
            $content="";if(!empty($_POST["content"])){$content = $_POST["content"];}
            
            $update_time = time();
            if($_POST['update_time']){$update_time = strtotime($_POST['update_time']);} // 更新时间
            $_POST['add_time'] = $_POST['update_time'] = $update_time;
            $_POST['user_id'] = session('admin_info')['id']; // 发布者id
            
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
                //Tag添加
                if(isset($_POST['tags']) && $_POST["tags"]!='')
                {
                    $tags = $_POST['tags'];
                    $tags = explode(',',str_replace('，',',',$tags));
                    foreach($tags as $row)
                    {
                        $tag_id = model('Tag')->getValue(array('name'=>$row),'id');
                        if($tag_id)
                        {
                            $data2['tag_id'] = $tag_id;
                            $data2['article_id'] = $id;
                            logic('Taglist')->add($data2);
                        }
                    }
                }
                
                $this->success($res['msg'], url('index'), '', 1);
            }
            
            $this->error($res['msg']);
        }
        
        //文章添加到哪个栏目下
        $this->assign('type_id',input('type_id/d', 0));
		
        //栏目列表
        $article_type_list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $this->assign('article_type_list',$article_type_list);
        
        return $this->fetch();
    }
    
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            $id=$where['id'] = $_POST['id'];
            unset($_POST['id']);
            
            $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
            if(empty($_POST["description"])){if(!empty($_POST["content"])){$_POST['description']=cut_str($_POST["content"]);}} //description
            $content="";if(!empty($_POST["content"])){$content = $_POST["content"];}
            
            $update_time = time();
            if($_POST['update_time']){$update_time = $_POST['add_time'] = strtotime($_POST['update_time']);} // 更新时间
            $_POST['update_time'] = $update_time;
            
            $_POST['user_id'] = session('admin_info')['id']; // 修改者id
            
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
            
            $res = $this->getLogic()->edit($_POST,$where);
            if($res['code'] == ReturnData::SUCCESS)
            {
                //Tag添加
                if(isset($_POST['tags']) && $_POST["tags"]!='')
                {
                    $tags = $_POST['tags'];
                    $tags = explode(',',str_replace('，',',',$tags));
                    model('Taglist')->del(array('article_id'=>$id));
                    foreach($tags as $row)
                    {
                        $tag_id = model('Tag')->getValue(array('name'=>$row),'id');
                        if($tag_id)
                        {
                            $data2['tag_id'] = $tag_id;
                            $data2['article_id'] = $id;
                            logic('Taglist')->add($data2);
                        }
                    }
                }
                
                $this->success($res['msg'], url('index'), '', 1);
            }
            
            $this->error($res['msg']);
        }
        
        if(!checkIsNumber(input('id',null))){$this->error('参数错误');}
        $where['id'] = input('id');
        $this->assign('id', $where['id']);
        
        $post = $this->getLogic()->getOne($where);
        $this->assign('post', $post);
        
        //Tag标签
        $tags = '';
        $taglist = model('Taglist')->getAll(['article_id'=>$where['id']]);
        if($taglist)
        {
            foreach($taglist as $k=>$v)
            {
                $tmp[] = model('Tag')->getValue(['id'=>$v['tag_id']],'name');
            }
            $tags = implode(',',$tmp);
        }
        $this->assign('tags',$tags);
        
        //栏目列表
        $article_type_list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $this->assign('article_type_list',$article_type_list);
        
        return $this->fetch();
    }
    
    //删除
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        $res = model('Article')->del("id in ($id)");
		if($res)
        {
            $this->success("$id ,删除成功", url('index'), '', 1);
        }
        
		$this->error('删除失败');
    }
    
    //文章重复列表
    public function repetarc()
    {
		$this->assign('list',Db::query("select title,count(*) AS count from ".config('database.prefix')."article group by title HAVING count>1 order by count DESC"));
		
        return $this->fetch();
    }
	
    //文章推荐
	public function recommendarc()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);} //if(preg_match('/[0-9]*/',$id)){}else{exit;}
		
		$data['tuijian'] = 1;
        $res = model('Article')->edit($data, "id in ($id)");
		if($res)
        {
            $this->success("$id ,推荐成功");
        }
        
		$this->error("$id ,推荐失败！请重新提交");
    }
    
    //文章是否存在
    public function articleexists()
    {
        $map=[];
        if(!empty($_GET["title"]))
        {
            $map['title'] = $_GET["title"];
        }
        
        if(!empty($_GET["id"]))
        {
            $map['id'] = array('NEQ',$_GET["id"]);
        }
        
        echo model('Article')->getCount($map);
    }
}