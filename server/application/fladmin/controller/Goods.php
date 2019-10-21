<?php

namespace app\fladmin\controller;

use think\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\GoodsLogic;
use app\common\logic\GoodsBrandLogic;

class Goods extends Base
{
    public $goods_type_list; //商品分类
    public $goods_brand_list; //商品品牌

    public function _initialize()
    {
        parent::_initialize();

        //商品分类
        $this->goods_type_list = model('GoodsType')->tree_to_list(model('GoodsType')->list_to_tree());
        //商品品牌
        $this->goods_brand_list = model('GoodsBrand')->getAll([], 'listorder asc', 'id,name');
    }

    public function getLogic()
    {
        return new GoodsLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (isset($_REQUEST["keyword"])) {
            $where['title'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        if (isset($_REQUEST["type_id"]) && $_REQUEST["type_id"] > 0) {
            $where['type_id'] = $_REQUEST["type_id"];
        }
        $list = $this->getLogic()->getPaginate($where, ['update_time' => 'desc'], ['content']);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;

        //分类列表
        $this->assign('goods_type_list', $this->goods_type_list);

        return $this->fetch();
    }

    public function add()
    {
        if (Helper::isPostRequest()) {
            //表单令牌验证
            $validate = new Validate([
                ['__token__', 'require|token', '非法提交|请不要重复提交表单'],
            ]);
            if (!$validate->check($_POST)) {
                $this->error($validate->getError());
            }

            $_POST['add_time'] = $_POST['update_time'] = time(); //添加&更新时间
            $_POST['user_id'] = session('admin_info')['id']; // 发布者id

            if (empty($_POST["description"])) {
                if (!empty($_POST["content"])) {
                    $_POST['description'] = cut_str($_POST["content"]);
                }
            } //description
            //关键词
            if (!empty($_POST["keywords"])) {
                $_POST['keywords'] = str_replace("，", ",", $_POST["keywords"]);
            } else {
                if (!empty($_POST["title"])) {
                    $title = $_POST["title"];
                    $title = str_replace("，", "", $title);
                    $title = str_replace(",", "", $title);
                    $_POST['keywords'] = get_participle($title);//标题分词
                }
            }
            if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
                $_POST['keywords'] = mb_strcut($_POST['keywords'], 0, 60, 'UTF-8');
            }
            //促销时间
            if (isset($_POST['promote_start_date']) && $_POST['promote_start_date'] != '') {
                $_POST['promote_start_date'] = strtotime($_POST['promote_start_date']);
            }
            if (isset($_POST['promote_end_date']) && $_POST['promote_end_date'] != '') {
                $_POST['promote_end_date'] = strtotime($_POST['promote_end_date']);
            }
            if (empty($_POST['promote_price'])) {
                unset($_POST['promote_price']);
            }
            //商品图片
            if (!empty($_POST['goods_img'])) {
                $goods_img = $_POST['goods_img'];
                $_POST['goods_img'] = $_POST['goods_img'][0];
            }

            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                //添加商品图片
                if (isset($goods_img)) {
                    foreach ($goods_img as $k => $v) {
                        logic('GoodsImg')->add(['url' => $v, 'goods_id' => $res['data'], 'add_time' => $_POST['add_time']]);
                    }
                }

                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        //商品添加到哪个栏目下
        $this->assign('type_id', input('type_id/d', 0));
        //分类列表
        $this->assign('goods_type_list', $this->goods_type_list);
        //品牌列表
        $this->assign('goods_brand_list', $this->goods_brand_list);
        return $this->fetch();
    }

    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $_POST['update_time'] = time();//更新时间
            $_POST['user_id'] = session('admin_info')['id']; // 修改者ID

            if (empty($_POST["description"])) {
                if (!empty($_POST["content"])) {
                    $_POST['description'] = cut_str($_POST["content"]);
                }
            } //description
            //关键词
            if (!empty($_POST["keywords"])) {
                $_POST['keywords'] = str_replace("，", ",", $_POST["keywords"]);
            } else {
                if (!empty($_POST["title"])) {
                    $title = $_POST["title"];
                    $title = str_replace("，", "", $title);
                    $title = str_replace(",", "", $title);
                    $_POST['keywords'] = get_participle($title); //标题分词
                }
            }
            if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
                $_POST['keywords'] = mb_strcut($_POST['keywords'], 0, 60, 'UTF-8');
            }
            //促销时间
            if (isset($_POST['promote_start_date']) && $_POST['promote_start_date'] != '') {
                $_POST['promote_start_date'] = strtotime($_POST['promote_start_date']);
            }
            if (isset($_POST['promote_end_date']) && $_POST['promote_end_date'] != '') {
                $_POST['promote_end_date'] = strtotime($_POST['promote_end_date']);
            }
            if (empty($_POST['promote_price'])) {
                unset($_POST['promote_price']);
            }
            //商品图片
            if (!empty($_POST['goods_img'])) {
                $goods_img = $_POST['goods_img'];
                $_POST['goods_img'] = $_POST['goods_img'][0];
            }

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                if (isset($goods_img)) {
                    model('GoodsImg')->del(array('goods_id' => $where['id']));
                    foreach ($goods_img as $k => $v) {
                        logic('GoodsImg')->add(['url' => $v, 'goods_id' => $where['id'], 'add_time' => $_POST['update_time']]);
                    }
                }

                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        if (!checkIsNumber(input('id', null))) {
            $this->error('参数错误');
        }
        $where['id'] = input('id');
        $this->assign('id', $where['id']);

        $post = $this->getLogic()->getOne($where);

        //时间戳转日期格式
        if ($post['promote_start_date'] == 0) {
            $post['promote_start_date'] = '';
        } else {
            $post['promote_start_date'] = date('Y-m-d H:i:s', $post['promote_start_date']);
        }
        if ($post['promote_end_date'] == 0) {
            $post['promote_end_date'] = '';
        } else {
            $post['promote_end_date'] = date('Y-m-d H:i:s', $post['promote_end_date']);
        }

        $this->assign('post', $post);
        //分类列表
        $this->assign('goods_type_list', $this->goods_type_list);
        //品牌列表
        $this->assign('goods_brand_list', $this->goods_brand_list);
        //商品图片列表
        $this->assign('goods_img_list', model('GoodsImg')->getAll(array('goods_id' => $where['id']), 'listorder asc'));

        return $this->fetch();
    }

    //删除
    public function del()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $this->error('参数错误', url('index'), '', 3);
        }

        $res = model('Goods')->del("id in ($id)");
        if ($res) {
            $this->success("$id ,删除成功", url('index'), '', 1);
        }

        $this->error('删除失败');
    }

    //商品推荐
    public function recommendarc()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $this->error('参数错误', url('index'), '', 3);
        } //if(preg_match('/[0-9]*/',$id)){}else{exit;}

        $data['tuijian'] = 1;
        $res = model('Goods')->edit($data, "id in ($id)");
        if ($res) {
            $this->success("$id ,推荐成功");
        }

        $this->error("$id ,推荐失败！请重新提交");
    }

    //商品是否存在
    public function goodsexists()
    {
        $map = [];
        if (!empty($_GET["title"])) {
            $map['title'] = $_GET["title"];
        }

        if (!empty($_GET["id"])) {
            $map['id'] = array('NEQ', $_GET["id"]);
        }

        echo model('Goods')->getCount($map);
    }
}