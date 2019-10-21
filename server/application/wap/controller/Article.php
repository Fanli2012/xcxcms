<?php

namespace app\wap\controller;

use think\Db;
use think\Request;
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

    //列表
    public function index()
    {
        $where = [];
        $title = '';

        $key = input('key', null);
        if ($key != null) {
            $arr_key = logic('Article')->getArrByString($key);
            if (!$arr_key) {
                $this->error('您访问的页面不存在或已被删除', '/', '', 3);
            }

            //分类id
            if (isset($arr_key['f']) && $arr_key['f'] > 0) {
                $type_id = $where['type_id'] = $arr_key['f'];

                $post = model('ArticleType')->getOne(['id' => $arr_key['f']]);
                $this->assign('post', $post);

                //面包屑导航
                $this->assign('bread', logic('Article')->get_article_type_path($where['type_id']));
            }
        }

        /* $where['delete_time'] = 0;
        $where['status'] = 0;
        $list = $this->getLogic()->getPaginate($where, 'id desc', ['content']);
        if(!$list){$this->error('您访问的页面不存在或已被删除', '/' , '', 3);}
        
        $page = $list->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $this->assign('list', $list); */

        $pagesize = 11;
        $offset = 0;
        if (isset($_REQUEST['page'])) {
            $offset = ($_REQUEST['page'] - 1) * $pagesize;
        }
        $where['status'] = 0;
        $where['delete_time'] = 0;
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
                    $html .= '<a href="' . model('Article')->getArticleDetailUrl(array('id' => $v['id'])) . '" class="weui-media-box weui-media-box_appmsg">';
                    if (!empty($v['litpic'])) {
                        $html .= '<div class="weui-media-box__hd"><img class="weui-media-box__thumb" src="' . $v['litpic'] . '" alt="' . $v['title'] . '"></div>';
                    }
                    $html .= '<div class="weui-media-box__bd">';
                    $html .= '<h4 class="weui-media-box__title">' . $v['title'] . '</h4>';
                    $html .= '<p class="weui-media-box__desc">' . mb_strcut($v['description'], 0, 120, 'UTF-8') . '..</p>';
                    $html .= '</div></a>';
                }
            }

            exit(json_encode($html));
        }

        //推荐文章
        $relate_tuijian_list = cache("index_article_detail_relate_tuijian_list_$key");
        if (!$relate_tuijian_list) {
            $where_tuijian['delete_time'] = 0;
            $where_tuijian['status'] = 0;
            $where_tuijian['tuijian'] = 1;
            $where_tuijian['litpic'] = ['<>', ''];
            if (isset($type_id)) {
                $where_tuijian['type_id'] = $type_id;
            }
            $relate_tuijian_list = logic('Article')->getAll($where_tuijian, 'update_time desc', ['content'], 5);
            cache("index_article_detail_relate_tuijian_list_$key", $relate_tuijian_list, 2592000);
        }
        $this->assign('relate_tuijian_list', $relate_tuijian_list);

        //随机文章
        $relate_rand_list = cache("index_article_detail_relate_rand_list_$key");
        if (!$relate_rand_list) {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            if (isset($type_id)) {
                $where_rand['type_id'] = $type_id;
            }
            $relate_rand_list = logic('Article')->getAll($where_rand, 'rand()', ['content'], 5);
            cache("index_article_detail_relate_rand_list_$key", $relate_rand_list, 2592000);
        }
        $this->assign('relate_rand_list', $relate_rand_list);

        //seo标题设置
        $title = $title . '最新动态';
        $this->assign('title', $title);
        return $this->fetch();
    }

    //详情
    public function detail()
    {
        if (!checkIsNumber(input('id', null))) {
            $this->error('您访问的页面不存在或已被删除', '/', '', 3);
        }
        $id = input('id');

        $post = cache("index_article_detail_$id");
        if (!$post) {
            $where['id'] = $id;
            $post = $this->getLogic()->getOne($where);
            if (!$post) {
                $this->error('您访问的页面不存在或已被删除', '/', '', 3);
            }
            $post['content'] = $this->getLogic()->replaceKeyword($post['content']);
            cache("index_article_detail_$id", $post, 2592000);

        }
        $this->assign('post', $post);
        //echo '<pre>';print_r($post);exit;
        //最新文章
        $relate_zuixin_list = cache("index_article_detail_relate_zuixin_list_$id");
        if (!$relate_zuixin_list) {
            $where_zuixin['delete_time'] = 0;
            $where_zuixin['status'] = 0;
            $where_zuixin['type_id'] = $post['type_id'];
            $where_zuixin['id'] = ['<', $id];
            $relate_zuixin_list = logic('Article')->getAll($where_zuixin, 'update_time desc', ['content'], 5);
            if (!$relate_zuixin_list) {
                unset($where_zuixin['id']);
                $relate_zuixin_list = logic('Article')->getAll($where_zuixin, 'update_time desc', ['content'], 5);
            }
            cache("index_article_detail_relate_zuixin_list_$id", $relate_zuixin_list, 2592000);
        }
        $this->assign('relate_zuixin_list', $relate_zuixin_list);

        //随机文章
        $relate_rand_list = cache("index_article_detail_relate_rand_list_$id");
        if (!$relate_rand_list) {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            $where_rand['type_id'] = $post['type_id'];
            $relate_rand_list = logic('Article')->getAll($where_rand, 'rand()', ['content'], 5);
            cache("index_article_detail_relate_rand_list_$id", $relate_rand_list, 2592000);
        }
        $this->assign('relate_rand_list', $relate_rand_list);

        //面包屑导航
        $this->assign('bread', logic('Article')->get_article_type_path($post['type_id']));

        //上一篇、下一篇
        $this->assign($this->getPreviousNextArticle(['article_id' => $id]));

        return $this->fetch();
    }

    /**
     * 获取文章上一篇，下一篇
     * @param int $param ['article_id'] 当前文章id
     * @return array
     */
    public function getPreviousNextArticle(array $param)
    {
        $res['previous_article'] = [];
        $res['next_article'] = [];

        $where['id'] = $param['article_id'];
        $post = model('Article')->getOne($where, ['content']);
        if (!$post) {
            return $res;
        }
        $res['previous_article'] = model('Article')->getOne(['id' => ['<', $param['article_id']], 'type_id' => $post['type_id']], ['content']);
        $res['next_article'] = model('Article')->getOne(['id' => ['>', $param['article_id']], 'type_id' => $post['type_id']], ['content']);
        return $res;
    }
}