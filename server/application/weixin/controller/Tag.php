<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\TagLogic;

class Tag extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new TagLogic();
    }
    
    //列表
    public function index()
	{
        $where = [];
        $title = '';
        
        $key = input('key', null);
        
        //标签
        $where['status'] = 0;
        $list = logic('Tag')->getAll($where, 'id desc', ['content'], 100);
        $this->assign('list',$list);
        
        //推荐文章
        $relate_tuijian_list = cache("index_tag_index_relate_tuijian_list_$key");
        if(!$relate_tuijian_list)
        {
            $where_tuijian['delete_time'] = 0;
            $where_tuijian['status'] = 0;
            $where_tuijian['tuijian'] = 1;
            $relate_tuijian_list = logic('Article')->getAll($where_tuijian, 'update_time desc', ['content'], 5);
            cache("index_tag_index_relate_tuijian_list_$key",$relate_tuijian_list,2592000);
        }
        $this->assign('relate_tuijian_list',$relate_tuijian_list);
        
        //最新文章
        $relate_zuixin_list = cache("index_tag_index_relate_zuixin_list_$key");
        if(!$relate_zuixin_list)
        {
            $where_zuixin['delete_time'] = 0;
            $where_zuixin['status'] = 0;
            $where_zuixin['tuijian'] = 0;
            $relate_zuixin_list = logic('Article')->getAll($where_zuixin, 'update_time desc', ['content'], 5);
            cache("index_tag_index_relate_zuixin_list_$key",$relate_zuixin_list,2592000);
        }
        $this->assign('relate_zuixin_list',$relate_zuixin_list);
        
        //seo标题设置
        $title = $title.'标签';
        $this->assign('title',$title);
        return $this->fetch();
    }
	
    //详情页
    public function detail()
	{
        if(!checkIsNumber(input('id',null))){Helper::http404();}
        $id = input('id');
        
        $where['fl_taglist.tag_id'] = $id;
        
        $post = model('Tag')->getOne(['id'=>$id]);
        $this->assign('post',$post);
        
        $pagesize = 11;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        $where['status'] = 0;
        $where['delete_time'] = 0;
		$res = logic('Taglist')->getJoinList($where, 'fl_article.update_time desc', 'fl_article.*', $offset, $pagesize);
        if($res['list'])
        {
            foreach($res['list'] as $k => $v)
            {
                
            }
        }
        $this->assign('list',$res['list']);
        $totalpage = ceil($res['count']/$pagesize);
        $this->assign('totalpage',$totalpage);
        if(isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax']==1)
        {
    		$html = '';
            if($res['list'])
            {
                foreach($res['list'] as $k => $v)
                {
                    $html .= '<div class="list">';
                    if(!empty($v['litpic'])){$html .= '<a class="limg" href="/p/'.$v['id'].'"><img alt="'.$v['title'].'" src="'.$v['litpic'].'"></a>';}
                    $html .= '<strong class="tit"><a href="/p/'.$v['id'].'" target="_blank">'.$v['title'].'</a></strong><p>'.mb_strcut($v['description'],0,150,'UTF-8').'..</p>';
                    $html .= '<div class="info"><span class="fl"><em>'.date("m-d H:i",$v['update_time']).'</em></span><span class="fr"><em>'.$v['click'].'</em>人阅读</span></div>';
                    $html .= '<div class="cl"></div></div>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
        //最新文章
        $relate_zuixin_list = cache("index_tag_detail_relate_zuixin_list_$id");
        if(!$relate_zuixin_list)
        {
            $where_zuixin['delete_time'] = 0;
            $where_zuixin['status'] = 0;
            $relate_zuixin_list = logic('Article')->getAll($where_zuixin, 'update_time desc', ['content'], 5);
            cache("index_tag_detail_relate_zuixin_list_$id",$relate_zuixin_list,2592000);
        }
        $this->assign('relate_zuixin_list',$relate_zuixin_list);
        
        //随机文章
        $relate_rand_list = cache("index_tag_detail_relate_rand_list_$id");
        if(!$relate_rand_list)
        {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            $relate_rand_list = logic('Article')->getAll($where_rand, ['orderRaw','rand()'], ['content'], 5);
            cache("index_tag_detail_relate_rand_list_$id",$relate_rand_list,2592000);
        }
        $this->assign('relate_rand_list',$relate_rand_list);
        
        //标签
        $where_tag['status'] = 0;
        $tag_list = logic('Tag')->getAll($where_tag, 'id desc', ['content'], 10);
        $this->assign('tag_list',$tag_list);
        
        return $this->fetch();
    }
}