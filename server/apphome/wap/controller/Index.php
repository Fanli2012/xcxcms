<?php
namespace app\wap\controller;

use think\Request;
use think\Session;
use think\Controller;

class Index extends Controller
{
    //首页
    public function index()
	{
        return $this->fetch();
    }
	
    //列表页
    public function category()
	{
        $cat=input('cat');
        $pagenow=input('page');
        
		if(empty($cat) || !preg_match('/[0-9]+/',$cat)){$this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;}
        
		if(cache("catid$cat")){$post=cache("catid$cat");}else{$post = db('arctype')->where("id=$cat")->find();if(empty($post)){$this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;}cache("catid$cat",$post,2592000);}
        $this->assign('post',$post);
        
		$subcat="";$sql="";
		$post2=db('arctype')->field('id')->where("reid=$cat")->select();
		if(!empty($post2)){foreach($post2 as $row){$subcat=$subcat."typeid=".$row["id"]." or ";}}
		$subcat=$subcat."typeid=".$cat;
		$sql=$subcat." or typeid2 in (".$cat.")";//echo $subcat2;exit;
		$this->assign('sql',$sql);
		
		$counts=db("article")->where($sql)->count('id');
		if($counts>sysconfig('CMS_MAXARC')){$counts=sysconfig('CMS_MAXARC');}
		$pagesize=sysconfig('CMS_PAGESIZE');$page=0;
		if($counts % $pagesize){//取总数据量除以每页数的余数
		$pages = intval($counts/$pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
		}else{$pages = $counts/$pagesize;}
		if(!empty($pagenow)){if($pagenow==1 || $pagenow>$pages){header("HTTP/1.0 404 Not Found");$this->error('您访问的页面不存在或已被删除！');exit;}$page = $pagenow-1;$nextpage=$pagenow+1;$previouspage=$pagenow-1;}else{$page = 0;$nextpage=2;$previouspage=0;}
		$this->assign('previouspage',$previouspage);
        $this->assign('nextpage',$nextpage);
        $this->assign('catid',$cat); //栏目id
		$this->assign('page',$page); //
		$this->assign('pagesize',$pagesize); //每页数量
		$this->assign('pages',$pages); //总页数
		$this->assign('counts',$counts); //总条数
		$start=$page*$pagesize;
		
		$this->assign('posts',arclist(array("sql"=>$sql,"limit"=>"$start,$pagesize"))); //获取列表
		$this->assign('pagenav',get_listnav(array("counts"=>$counts,"pagesize"=>$pagesize,"pagenow"=>$page+1,"catid"=>$cat))); //获取分页列表
        
        return $this->fetch($post['templist']);
	}
    
    //文章详情页
    public function detail()
	{
        $id=input('id');
        if(empty($id) || !preg_match('/[0-9]+/',$id)){$this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;}
		
		if(cache("detailid$id")){$post=cache("detailid$id");}else{$post = db('Article')->where("id=$id")->find();if(empty($post)){$this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;}$post['typename'] = db('arctype')->where("id=".$post['typeid'])->value('typename');cache("detailid$id",$post,2592000);}
		if($post)
        {
			$cat=$post['typeid'];
            $post['body']=ReplaceKeyword($post['body']);
            if(!empty($post['writer'])){$post['writertitle']=$post['title'].' '.$post['writer'];}
            
			$this->assign('post',$post);
            $this->assign('pre',get_article_prenext(array('aid'=>$post["id"],'typeid'=>$post["typeid"],'type'=>"pre")));
        }
        else
        {
            $this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;
        }
        
		if(cache("catid$cat")){$post=cache("catid$cat");}else{$post = db('arctype')->where("id=$cat")->find();cache("catid$cat",$post,2592000);}
        
        return $this->fetch($post['temparticle']);
    }
	
    //标签详情页
	public function tag()
	{
        $tag=input('tag');
        $pagenow=input('page');
        
		if(empty($tag) || !preg_match('/[0-9]+/',$tag)){$this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;}
        
		if(cache("tagid$tag")){$post=cache("tagid$tag");}else{$post = db('tagindex')->where("id=$tag")->find();cache("tagid$tag",$post,2592000);}
        $this->assign('post',$post);
		
		$counts=db("taglist")->where("tid=$tag")->count('aid');
		if($counts>sysconfig('CMS_MAXARC')){$counts=sysconfig('CMS_MAXARC');}
		$pagesize=sysconfig('CMS_PAGESIZE');$page=0;
		if($counts % $pagesize){//取总数据量除以每页数的余数
		$pages = intval($counts/$pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
		}else{$pages = $counts/$pagesize;}
		if(!empty($pagenow)){if($pagenow==1 || $pagenow>$pages){header("HTTP/1.0 404 Not Found");$this->error('您访问的页面不存在或已被删除！');exit;}$page = $pagenow-1;$nextpage=$pagenow+1;$previouspage=$pagenow-1;}else{$page = 0;$nextpage=2;$previouspage=0;}
		$this->assign('page',$page);
		$this->assign('pages',$pages);
		$this->assign('counts',$counts);
		$start=$page*$pagesize;
		
		$posts=db("taglist")->where("tid=$tag")->order('aid desc')->limit("$start,$pagesize")->select();
		foreach($posts as $row)
		{
			$aid[] = $row["aid"];
		}
		$aid = implode(',',$aid);
		
		$this->assign('posts',arclist(array("sql"=>"id in ($aid)","orderby"=>"id desc","limit"=>"$pagesize"))); //获取列表
		$this->assign('pagenav',get_listnav(array("counts"=>$counts,"pagesize"=>$pagesize,"pagenow"=>$page+1,"catid"=>$tag,"urltype"=>"tag"))); //获取分页列表
		
		return $this->fetch($post['template']);
    }
    
	//标签页
    public function tags()
	{
		return $this->fetch();
    }
    
    //搜索页
	public function search()
	{
		if(!empty($_GET["keyword"]))
		{
			$keyword = $_GET["keyword"]; //搜索的关键词
			if(strstr($keyword,"&")) exit;
			
			$map['title'] = array('LIKE',"%$keyword%");
			
            $this->assign('posts',db("article")->field('body',true)->where($map)->order('id desc')->limit(30)->select());
			$this->assign('keyword',$keyword);
		}
		else
		{
			$this->error('请输入正确的关键词', '/' , 3);exit;
		}
		
		return $this->fetch();
    }
    
    //单页面
    public function page()
	{
        $id=input('id');
        
        if(!empty($id) && preg_match('/[a-z0-9]+/',$id))
        {
            $map['filename']=$id;
            if(cache("pageid$id")){$post=cache("pageid$id");}else{$post = db('page')->where($map)->find();cache("pageid$id",$post,2592000);}
            
            if($post)
            {
                $this->assign('post',$post);
            }
            else
            {
                $this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;
            }
            
            return $this->fetch($post['template']);
        }
        else
        {
            $this->error('您访问的页面不存在或已被删除！', '/' , 3);exit;
        }
    }
    
    public function sitemap()
    {
		return $this->fetch();
    }
    
	public function test()
    {
		//echo (dirname('/images/uiui/1.jpg'));
		//echo '<pre>';
		$str='<p><img border="0" src="./images/1.jpg" alt=""/></p>';
		
		//echo getfirstpic($str);
		$imagepath='.'.getfirstpic($str);
		$image = new \Think\Image(); 
		$image->open($imagepath);
		// 按照原图的比例生成一个最大为240*180的缩略图并保存为thumb.jpg
		$image->thumb(CMS_IMGWIDTH, CMS_IMGHEIGHT)->save('./images/1thumb.jpg');
    }
}