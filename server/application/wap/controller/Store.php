<?php

namespace app\wap\controller;

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
                $this->error('您访问的页面不存在或已被删除', '/', 3);
            }

            //省
            if (isset($arr_key['p']) && !empty($arr_key['p'])) {
                $where['province_id'] = $arr_key['p'];
                $title = model('Region')->getRegionName($where['province_id']);
                $this->assign('province', $title);
                $province_id = $where['province_id'];
            }

            //市
            if (isset($arr_key['c']) && !empty($arr_key['c'])) {
                $where['city_id'] = $arr_key['c'];
                $region = model('Region')->getOne(['id' => $where['city_id']]);
                if ($region) {
                    $title = $region['name'];
                    $this->assign('city', $region['name']);
                    $this->assign('province', model('Region')->getRegionName($region['parent_id']));

                    $province_id = $region['parent_id'];
                }
            }

            //区
            if (isset($arr_key['d']) && !empty($arr_key['d'])) {
                $where['district_id'] = $arr_key['d'];
                $title = model('Region')->getRegionName($where['district_id']);
            }

            //企业类型，0个人，1公司
            if (isset($arr_key['t']) && !empty($arr_key['t'])) {
                $where['type'] = $arr_key['t'];
            }

            //店铺所属分类id
            if (isset($arr_key['f']) && !empty($arr_key['f'])) {
                $where['category_id'] = $arr_key['f'];
                $this->assign('category_id', $where['category_id']);

                $category_name = model('Category')->getDb()->where(['id' => $where['category_id']])->value('name');
                $this->assign('category_name', $category_name);

                $title = $title . $category_name;
            }
        }

        $where['delete_time'] = 0;
        $where['status'] = 1;
        $posts = $this->getLogic()->getPaginate($where, 'id desc', ['content']);
        if (!$posts) {
            $this->error('您访问的页面不存在或已被删除', '/', 3);
        }

        $page = $posts->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $this->assign('posts', $posts);

        //最新
        $where2['delete_time'] = 0;
        $where2['status'] = 1;
        $zuixin_list = logic('Shop')->getAll($where2, 'id desc', ['content'], 5);
        $this->assign('zuixin_list', $zuixin_list);

        //推荐
        $where3['delete_time'] = 0;
        $where3['status'] = 1;
        $where3['tuijian'] = 1;
        $where3['head_img'] = ['<>', ''];
        $tuijian_list = logic('Shop')->getAll($where3, 'id desc', ['content'], 5);
        $this->assign('tuijian_list', $tuijian_list);

        //seo标题设置
        $title = $title . '批发商';
        $this->assign('title', $title);

        //相关推荐
        if (isset($province_id)) {
            $region_list = logic('Region')->getAll(['parent_id' => $province_id]);
            if ($region_list) {
                foreach ($region_list as $k => $v) {
                    $where9['city_id'] = $v['id'];
                    if (isset($arr_key['f'])) {
                        $where9['category_id'] = $arr_key['f'];
                    }

                    $count = Db::table('fl_shop')->where($where9)->count();
                    if ($count < 5) {
                        unset($region_list[$k]);
                    }
                }
            }

            $this->assign('region_list', $region_list);
        }

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
            $this->error('您访问的页面不存在或已被删除', '/', 3);
        }
        $shop_id = input('id');

        $where3['id'] = $shop_id;
        //$where3['delete_time'] = 0;
        //$where3['status'] = 1;
        $post = $this->getLogic()->getOne($where3);
        if (!$post) {
            $this->error('您访问的页面不存在或已被删除', '/', 3);
        }

        $post['content'] = ReplaceKeyword($post['content']);
        $this->assign('post', $post);
        //var_dump($post);exit;
        //最新动态
        $where['shop_id'] = $shop_id;
        $where['delete_time'] = 0;
        $article_list = logic('article')->getAll($where, 'id desc', ['content'], 5);
        if (!$article_list) {
            $article_list = logic('article')->getAll(['delete_time' => 0], 'id desc', ['content'], 5);
            $this->assign('is_article_list', 1);
        }
        $this->assign('article_list', $article_list);

        //产品中心
        $where2['shop_id'] = $shop_id;
        $where2['delete_time'] = 0;
        $goods_list = logic('goods')->getAll($where2, 'id desc', ['body'], 5);
        if (!$goods_list) {
            $goods_list = logic('goods')->getAll(['delete_time' => 0], 'id desc', ['body'], 5);
            $this->assign('is_goods_list', 1);
        }
        $this->assign('goods_list', $goods_list);

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