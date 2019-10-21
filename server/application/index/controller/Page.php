<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\PageLogic;

class Page extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new PageLogic();
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
                Helper::http404();
            }

            //分类id
            if (isset($arr_key['f']) && $arr_key['f'] > 0) {
                $where['type_id'] = $arr_key['f'];

                $post = model('ArticleType')->getOne(['id' => $arr_key['f']]);
                $this->assign('post', $post);

                //面包屑导航
                $this->assign('bread', logic('Article')->get_article_type_path($where['type_id']));
            }
        }

        $where['delete_time'] = 0;
        $where['status'] = 0;
        $list = $this->getLogic()->getPaginate($where, 'id desc', ['content']);
        if (!$list) {
            Helper::http404();
        }

        $page = $list->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $this->assign('list', $list);

        //最新
        $where2['delete_time'] = 0;
        $where2['status'] = 0;
        $zuixin_list = logic('Article')->getAll($where2, 'id desc', ['content'], 5);
        $this->assign('zuixin_list', $zuixin_list);

        //推荐
        $where3['delete_time'] = 0;
        $where3['status'] = 0;
        $where3['tuijian'] = 1;
        $where3['litpic'] = ['<>', ''];
        $tuijian_list = logic('Article')->getAll($where3, 'id desc', ['content'], 5);
        $this->assign('tuijian_list', $tuijian_list);

        //seo标题设置
        $title = $title . '最新动态';
        $this->assign('title', $title);
        return $this->fetch();
    }

    //详情
    public function detail()
    {
        $id = input('id');

        $where['filename'] = $id;
        $post = cache("index_page_detail_$id");
        if (!$post) {
            $where['delete_time'] = 0;
            $post = $this->getLogic()->getOne($where);
            if (!$post) {
                Helper::http404();
            }
            cache("index_page_detail_$id", $post, 2592000);
        }

        $this->assign('post', $post);
        return $this->fetch();
    }
}