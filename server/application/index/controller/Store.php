<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ShopLogic;

class Store extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new ShopLogic();
    }

    //首页
    public function index()
    {
        $where = [];
        $title = '';

        $key = input('key', null);
        if ($key != null) {
            $arr_key = $this->getArrByString($key);
            if (!$arr_key) {
                Helper::http404();
            }

            //店铺所属分类id
            if (isset($arr_key['f']) && !empty($arr_key['f'])) {
                $where['category_id'] = $arr_key['f'];
                $this->assign('category_id', $where['category_id']);

                $category_name = model('Category')->getValue(['id' => $where['category_id']], 'name');
                $this->assign('category_name', $category_name);

                $title = $title . $category_name;
            }
        } else {
            $title = '列表';
        }

        $where['delete_time'] = 0;
        $where['status'] = 1;
        $where['head_img'] = ['<>', ''];
        $posts = $this->getLogic()->getPaginate($where, 'id desc', ['content']);

        $page = $posts->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $list = $posts->toArray();
        $this->assign('list', $list);
        if (!$list['data']) {
            Helper::http404();
        }

        //最新
        $where2['delete_time'] = 0;
        $where2['status'] = 1;
        $where2['head_img'] = ['<>', ''];
        $zuixin_list = logic('Shop')->getAll($where2, 'id desc', ['content'], 5);
        $this->assign('zuixin_list', $zuixin_list);

        //推荐
        $where3['delete_time'] = 0;
        $where3['status'] = 1;
        $where3['head_img'] = ['<>', ''];
        $tuijian_list = logic('Shop')->getAll($where3, 'click desc', ['content'], 5);
        $this->assign('tuijian_list', $tuijian_list);

        //seo标题设置
        $this->assign('title', $title);

        return $this->fetch();
    }

    //字符串转成数组
    public function getArrByString($key)
    {
        $res = array();

        if (!$key) {
            return [];
        }

        preg_match_all('/[a-z]+/u', $key, $letter);
        preg_match_all('/[0-9]+/u', $key, $number);
        if (count($letter[0]) != count($number[0])) {
            return [];
        }

        foreach ($letter[0] as $k => $v) {
            $res[$v] = $number[0][$k];
        }

        return $res;
    }

    //店铺详情页
    public function detail()
    {
        if (!checkIsNumber(input('id', null))) {
            Helper::http404();
        }
        $shop_id = input('id');

        //店铺最新文章
        $pagesize = 11;
        $offset = 0;
        if (isset($_REQUEST['page'])) {
            $offset = ($_REQUEST['page'] - 1) * $pagesize;
        }
        $where['shop_id'] = $shop_id;
        $where['delete_time'] = 0;
        $where['status'] = 0;
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
                        $html .= '<a class="limg" href="/p/' . $v['id'] . '"><img alt="' . $v['title'] . '" src="' . $v['litpic'] . '"></a>';
                    }
                    $html .= '<strong class="tit"><a href="/p/' . $v['id'] . '" target="_blank">' . $v['title'] . '</a></strong><p>' . mb_strcut($v['description'], 0, 150, 'UTF-8') . '..</p>';
                    $html .= '<div class="cl"></div></div>';
                }
            }

            exit(json_encode($html));
        }

        $where_shop['id'] = $shop_id;
        //$where3['delete_time'] = 0;
        //$where3['status'] = 1;
        $post = $this->getLogic()->getOne($where_shop);
        if (!$post) {
            Helper::http404();
        }

        $post['content'] = logic('Article')->replaceKeyword($post['content']);
        $this->assign('post', $post);
        //var_dump($post);exit;

        //推荐文章
        $where_rand['shop_id'] = $shop_id;
        $where_rand['delete_time'] = 0;
        $where_rand['status'] = 0;
        $where_rand['add_time'] = ['<', time()];
        //$where_rand['add_time'] = ['>',(time()-30*3600*24)];
        $relate_tuijian_list = logic('Article')->getAll($where_rand, 'click desc', ['content'], 5);
        $this->assign('relate_tuijian_list', $relate_tuijian_list);

        //店铺推荐
        $where_zuixin['delete_time'] = 0;
        //$where_zuixin['category_id'] = $post['category_id'];
        $where_zuixin['status'] = 1;
        $where_zuixin['head_img'] = ['<>', ''];
        $where_zuixin['tuijian'] = 1;
        $relate_zuixin_list = logic('Shop')->getAll($where_zuixin, 'click desc', ['content'], 5);
        $this->assign('relate_zuixin_list', $relate_zuixin_list);

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
}