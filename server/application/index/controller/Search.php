<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Search extends Base
{
    public function _initialize()
    {
        parent::_initialize();
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
        $where['add_time'] = ['<', time()];
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
        $where2['add_time'] = ['<', time()];
        $zuixin_list = logic('Article')->getAll($where2, 'id desc', ['content'], 5);
        $this->assign('zuixin_list', $zuixin_list);

        //推荐
        $where3['delete_time'] = 0;
        $where3['status'] = 0;
        $where3['tuijian'] = 1;
        $where3['litpic'] = ['<>', ''];
        $where3['add_time'] = ['<', time()];
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
        $keyword = input('keyword', null);
        if (!$keyword) {
            Helper::http404();
        }

        $where['title'] = array('like', '%' . $keyword . '%');

        $where['delete_time'] = 0;
        $where['status'] = 0;
        $where['add_time'] = ['<', time()];
        $list = logic('Article')->getPaginate($where, 'update_time desc', ['content']);
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
        $where2['add_time'] = ['<', time()];
        $relate_zuixin_list = logic('Article')->getAll($where2, 'id desc', ['content'], 5);
        $this->assign('relate_zuixin_list', $relate_zuixin_list);

        //推荐
        $where3['delete_time'] = 0;
        $where3['status'] = 0;
        $where3['tuijian'] = 1;
        $where3['litpic'] = ['<>', ''];
        $where3['add_time'] = ['<', time()];
        $relate_tuijian_list = logic('Article')->getAll($where3, 'id desc', ['content'], 5);
        $this->assign('relate_tuijian_list', $relate_tuijian_list);
        //搜索词
        $this->assign('keyword', $keyword);
        return $this->fetch();
    }
}