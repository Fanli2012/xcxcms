<?php

namespace app\index\controller;

use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ShopLogic;

class Index extends Base
{
    //首页
    public function index()
    {
        Log::error('错误信息');
        Log::info('首页');
        $pagesize = 5;
        $offset = 0;
        if (isset($_REQUEST['page'])) {
            $offset = ($_REQUEST['page'] - 1) * $pagesize;
        }
        $where['status'] = 0;
        $where['delete_time'] = 0;
        $where['add_time'] = ['<', time()];
        $res = logic('Article')->getList($where, 'id desc', ['content'], $offset, $pagesize);
        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {

            }
        }
        $this->assign('list', $res['list']);
        $totalpage = ceil($res['count'] / $pagesize);
        $this->assign('totalpage', $totalpage);
        if (isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax'] == 1) {
            $html = '';
            if ($res['list']) {
                foreach ($res['list'] as $k => $v) {
                    $html .= '<div class="list">';
                    if (!empty($v['litpic'])) {
                        $html .= '<a class="limg" href="' . model('Article')->getArticleDetailUrl(array('id' => $v['id'])) . '"><img alt="' . $v['title'] . '" src="' . $v['litpic'] . '"></a>';
                    }
                    $html .= '<strong class="tit"><a href="' . model('Article')->getArticleDetailUrl(array('id' => $v['id'])) . '" target="_blank">' . $v['title'] . '</a></strong><p>' . mb_strcut($v['description'], 0, 150, 'UTF-8') . '..</p>';
                    $html .= '<div class="info"><span class="fl"><em>' . date("m-d H:i", $v['update_time']) . '</em></span><span class="fr"><em>' . $v['click'] . '</em>人阅读</span></div>';
                    $html .= '<div class="cl"></div></div>';
                }
            }

            exit(json_encode($html));
        }

        //推荐文章
        $relate_tuijian_list = cache("index_index_index_relate_tuijian_list");
        if (!$relate_tuijian_list) {
            $where2['delete_time'] = 0;
            $where2['status'] = 0;
            $where2['tuijian'] = 1;
            $where2['add_time'] = ['<', time()];
            //$where2['add_time'] = ['>',(time()-30*3600*24)];
            $relate_tuijian_list = logic('Article')->getAll($where2, 'update_time desc', ['content'], 5);
            cache("index_index_index_relate_tuijian_list", $relate_tuijian_list, 3600); //1小时
        }
        $this->assign('relate_tuijian_list', $relate_tuijian_list);

        //随机文章
        $relate_rand_list = cache("index_index_index_relate_rand_list");
        if (!$relate_rand_list) {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            $where_rand['add_time'] = ['<', time()];
            $relate_rand_list = logic('Article')->getAll($where_rand, ['orderRaw', 'rand()'], ['content'], 5);
            cache("index_index_index_relate_rand_list", $relate_rand_list, 3600); //1小时
        }
        $this->assign('relate_rand_list', $relate_rand_list);

        //标签
        $relate_tag_list = cache("index_index_index_relate_tag_list");
        if (!$relate_tag_list) {
            $where_tag['status'] = 0;
            $relate_tag_list = logic('Tag')->getAll($where_tag, 'id desc', ['content'], 5);
            cache("index_index_index_relate_tag_list", $relate_tag_list, 3600); //1小时
        }
        $this->assign('relate_tag_list', $relate_tag_list);

        //友情链接
        $friendlink_list = cache("index_index_index_friendlink_list");
        if (!$friendlink_list) {
            $friendlink_list = logic('Friendlink')->getAll('', 'id desc', '*', 5);
            cache("index_index_index_friendlink_list", $friendlink_list, 604800); //7天
        }
        $this->assign('friendlink_list', $friendlink_list);

        //轮播图
        $slide_list = cache("index_index_index_slide_list");
        if (!$slide_list) {
            $where_slide['status'] = 0;
            $where_slide['group_id'] = 0;
            $slide_list = logic('Slide')->getAll($where_slide, 'listorder asc', '*', 5);
            cache("index_index_index_slide_list", $slide_list, 86400); //1天
        }
        $this->assign('slide_list', $slide_list);

        return $this->fetch();
    }

    //列表页
    public function category()
    {
        $cat = input('cat');
        $pagenow = input('page');

        if (empty($cat) || !preg_match('/[0-9]+/', $cat)) {
            Helper::http404();
        }

        if (cache("catid$cat")) {
            $post = cache("catid$cat");
        } else {
            $post = db('arctype')->where("id=$cat")->find();
            if (empty($post)) {
                Helper::http404();
            }
            cache("catid$cat", $post, 2592000);
        }
        $this->assign('post', $post);

        $subcat = "";
        $sql = "";
        $post2 = db('arctype')->field('id')->where("parent_id=$cat")->select();
        if (!empty($post2)) {
            foreach ($post2 as $row) {
                $subcat = $subcat . "typeid=" . $row["id"] . " or ";
            }
        }
        $subcat = $subcat . "typeid=" . $cat;
        $sql = $subcat . " or typeid2 in (" . $cat . ")";//echo $subcat2;exit;
        $this->assign('sql', $sql);

        $counts = db("article")->where($sql)->count('id');
        if ($counts > sysconfig('CMS_LIST_MAX_TOTAL')) {
            $counts = sysconfig('CMS_LIST_MAX_TOTAL');
        }
        $pagesize = sysconfig('CMS_PAGESIZE');
        $page = 0;
        if ($counts % $pagesize) {//取总数据量除以每页数的余数
            $pages = intval($counts / $pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
        } else {
            $pages = $counts / $pagesize;
        }
        if (!empty($pagenow)) {
            if ($pagenow == 1 || $pagenow > $pages) {
                Helper::http404();
            }
            $page = $pagenow - 1;
            $nextpage = $pagenow + 1;
            $previouspage = $pagenow - 1;
        } else {
            $page = 0;
            $nextpage = 2;
            $previouspage = 0;
        }
        $this->assign('page', $page);
        $this->assign('pages', $pages);
        $this->assign('counts', $counts);
        $start = $page * $pagesize;

        $this->assign('posts', arclist(array("sql" => $sql, "limit" => "$start,$pagesize"))); //获取列表
        $this->assign('pagenav', get_listnav(array("counts" => $counts, "pagesize" => $pagesize, "pagenow" => $page + 1, "catid" => $cat))); //获取分页列表

        return $this->fetch($post['templist']);
    }

    //文章详情页
    public function detail()
    {
        $id = input('id');
        if (empty($id) || !preg_match('/[0-9]+/', $id)) {
            Helper::http404();
        }
        $article = db('article');

        if (cache("detailid$id")) {
            $post = cache("detailid$id");
        } else {
            $post = db('article')->where("id=$id")->find();
            if (empty($post)) {
                Helper::http404();
            }
            $post['name'] = db('arctype')->where("id=" . $post['typeid'])->value('name');
            cache("detailid$id", $post, 2592000);
        }
        if ($post) {
            $cat = $post['typeid'];
            $post['body'] = ReplaceKeyword($post['body']);
            if (!empty($post['writer'])) {
                $post['writertitle'] = $post['title'] . ' ' . $post['writer'];
            }

            $this->assign('post', $post);
            $pre = get_article_prenext(array('aid' => $post["id"], 'typeid' => $post["typeid"], 'type' => "pre"));
            $this->assign('pre', $pre);
        } else {
            Helper::http404();
        }

        //获取最新列表
        $where = '';
        if ($pre) {
            $where['typeid'] = $post['typeid'];
            $where['id'] = array('lt', $pre['id']);
        }
        $latest_posts = $article->where($where)->field('body', true)->order('id desc')->limit(5)->select();
        if (!$latest_posts) {
            $latest_posts = $article->field('body', true)->order('id desc')->limit(5)->select();
        }
        $this->assign('latest_posts', $latest_posts);

        if (cache("catid$cat")) {
            $post = cache("catid$cat");
        } else {
            $post = db('arctype')->where("id=$cat")->find();
            cache("catid$cat", $post, 2592000);
        }

        return $this->fetch($post['temparticle']);
    }

    //标签详情页，共有3种显示方式，1正常列表，2列表显示文章，3显示描述
    public function tag()
    {
        $tag = input('tag');
        $pagenow = input('page');

        if (empty($tag) || !preg_match('/[0-9]+/', $tag)) {
            Helper::http404();
        }

        $post = db('tagindex')->where("id=$tag")->find();
        $this->assign('post', $post);

        $counts = db("taglist")->where("tid=$tag")->count('aid');
        if ($counts > sysconfig('CMS_LIST_MAX_TOTAL')) {
            $counts = sysconfig('CMS_BASEHOST');
        }
        $pagesize = sysconfig('CMS_PAGESIZE');
        $page = 0;
        if ($counts % $pagesize) {//取总数据量除以每页数的余数
            $pages = intval($counts / $pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
        } else {
            $pages = $counts / $pagesize;
        }
        if (!empty($pagenow)) {
            if ($pagenow == 1 || $pagenow > $pages) {
                Helper::http404();
            }
            $page = $pagenow - 1;
            $nextpage = $pagenow + 1;
            $previouspage = $pagenow - 1;
        } else {
            $page = 0;
            $nextpage = 2;
            $previouspage = 0;
        }
        $this->assign('page', $page);
        $this->assign('pages', $pages);
        $this->assign('counts', $counts);
        $start = $page * $pagesize;

        $posts = db("taglist")->where("tid=$tag")->order('aid desc')->limit("$start,$pagesize")->select();
        foreach ($posts as $row) {
            $aid[] = $row["aid"];
        }
        $aid = isset($aid) ? implode(',', $aid) : "";

        if ($aid != "") {
            if ($post['template'] == 'tag2') {
                $this->assign('posts', arclist(array("sql" => "id in ($aid)", "orderby" => "id desc", "limit" => "$pagesize", "field" => "title,body"))); //获取列表
            } else {
                $this->assign('posts', arclist(array("sql" => "id in ($aid)", "orderby" => "id desc", "limit" => "$pagesize"))); //获取列表
            }
        } else {
            $this->assign('posts', ""); //获取列表
        }

        $this->assign('pagenav', get_listnav(array("counts" => $counts, "pagesize" => $pagesize, "pagenow" => $page + 1, "catid" => $tag, "urltype" => "tag"))); //获取分页列表

        return $this->fetch($post['template']);
    }

    //标签页
    public function tags()
    {
        return $this->fetch();
    }

    //推荐页
    public function tuijian()
    {
        $pagenow = input('page');
        $where['tuijian'] = 1;

        $counts = db("article")->where($where)->count();
        if ($counts > sysconfig('CMS_LIST_MAX_TOTAL')) {
            $counts = sysconfig('CMS_BASEHOST');
        }
        $pagesize = sysconfig('CMS_PAGESIZE');
        $page = 0;
        if ($counts % $pagesize) {//取总数据量除以每页数的余数
            $pages = intval($counts / $pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
        } else {
            $pages = $counts / $pagesize;
        }
        if (!empty($pagenow)) {
            if ($pagenow == 1 || $pagenow > $pages) {
                Helper::http404();
            }
            $page = $pagenow - 1;
            $nextpage = $pagenow + 1;
            $previouspage = $pagenow - 1;
        } else {
            $page = 0;
            $nextpage = 2;
            $previouspage = 0;
        }
        $this->assign('page', $page);
        $this->assign('pages', $pages);
        $this->assign('counts', $counts);
        $start = $page * $pagesize;

        $posts = db('article')->where($where)->field('body', true)->order('id desc')->limit("$start,$pagesize")->select();
        $this->assign('posts', $posts); //获取列表
        $pagenav = '';
        if ($nextpage <= $pages && $nextpage > 0) {
            $pagenav = get_pagination_url(http_host() . '/tuijian', $_SERVER['QUERY_STRING'], $nextpage);
        } //获取上一页下一页网址
        $this->assign('pagenav', $pagenav);

        return $this->fetch();
    }

    //XML地图
    public function sitemap()
    {
        //最新文章
        $where['delete_time'] = 0;
        $where['status'] = 0;
        $where['add_time'] = ['<', time()];
        $list = logic('Article')->getAll($where, 'update_time desc', ['content'], 100);
        $this->assign('list', $list);

        return $this->fetch();
    }

    //404页面
    public function notfound()
    {
        return $this->fetch();
    }

    public function test()
    {
        //echo '<pre>';print_r(request());exit;
        //echo (dirname('/images/uiui/1.jpg'));
        //echo '<pre>';
        //$str='<p><img border="0" src="./images/1.jpg" alt=""/></p>';

        //echo getfirstpic($str);
        //$imagepath='.'.getfirstpic($str);
        //$image = new \Think\Image();
        //$image->open($imagepath);
        // 按照原图的比例生成一个最大为240*180的缩略图并保存为thumb.jpg
        //$image->thumb(CMS_IMGWIDTH, CMS_IMGHEIGHT)->save('./images/1thumb.jpg');

        return $this->fetch();
    }

    //图片列表
    public function piclist()
    {
        return $this->fetch();
    }

    //图片列表
    public function piclist2()
    {
        return $this->fetch();
    }

    //图文列表
    public function piclist3()
    {
        return $this->fetch();
    }

    public function hello_world_test()
    {
        return 'Hello world!';
    }
}