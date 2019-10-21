<?php

namespace app\shop\controller;

use app\common\lib\ReturnData;
use app\common\logic\GoodsLogic;
use app\common\logic\GoodsBrandLogic;
use think\Db;

class Goods extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new GoodsLogic();
    }

    public function index()
    {
        $where = array();
        if (isset($_REQUEST["keyword"])) {
            $where['title'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        if (isset($_REQUEST["type_id"]) && $_REQUEST["type_id"] != 0) {
            $where['type_id'] = $_REQUEST["type_id"];
        }
        $where['delete_time'] = 0; //未删除
        $where['shop_id'] = $this->login_info['id'];
        $list = $this->getLogic()->getPaginate($where, 'id desc', ['content'], 15);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';var_dump($list->total());exit;
        return $this->fetch();
    }

    public function add()
    {
        if ($this->login_info['status'] == 0) {
            $this->error('请先完善资料', url('shop/Shop/setting'));
        }

        $where['shop_id'] = $this->login_info['id'];

        $count = model('GoodsType')->getCount($where);
        if ($count > 0) {
        } else {
            $this->error('请先添加分类', url('shop/GoodsType/add'));
        }

        $type_list = model('GoodsType')->getAll($where, ['listorder' => 'asc'], ['content'], 15);
        $this->assign('type_list', $type_list);

        //获取类目
        /* $where2['parent_id'] = 0;
        $where2['delete_time'] = 0; //未删除
        $category_list = logic('Category')->getAllCategoryList($where2,['id'=>'desc'],['content']);
        $this->assign('category_list',$category_list); */

        return $this->fetch();
    }

    public function doadd()
    {
        $litpic = "";
        if (!empty($_POST["litpic"])) {
            $litpic = $_POST["litpic"];
        } else {
            $_POST['litpic'] = "";
        } //缩略图
        if (empty($_POST["description"])) {
            if (!empty($_POST["content"])) {
                $_POST['description'] = cut_str($_POST["content"]);
            }
        } //description
        $_POST['shop_id'] = $this->login_info['id']; // 发布者id
        $_POST['click'] = rand(200, 500);
        $_POST['add_time'] = $_POST['update_time'] = time(); // 更新时间

        //关键词
        if (!empty($_POST["keywords"])) {
            $_POST['keywords'] = str_replace("，", ",", $_POST["keywords"]);
        } else {
            if (!empty($_POST["title"])) {
                $title = $_POST["title"];
                $title = str_replace("，", "", $title);
                $title = str_replace(",", "", $title);
                $_POST['keywords'] = get_participle($title); // 标题分词
            }
        }

        if (isset($_POST['promote_start_date'])) {
            $_POST['promote_start_date'] = strtotime($_POST['promote_start_date']);
        }
        if (isset($_POST['promote_end_date'])) {
            $_POST['promote_end_date'] = strtotime($_POST['promote_end_date']);
        }
        if (isset($_POST['promote_price']) && empty($_POST['promote_price'])) {
            unset($_POST['promote_price']);
        }
        if (!empty($_POST['goods_img'])) {
            $goods_img = $_POST['goods_img'];
            $_POST['goods_img'] = $_POST['goods_img'][0];
        }

        // 启动事务
        Db::startTrans();

        $res = $this->getLogic()->add($_POST);
        if ($res['code'] == ReturnData::SUCCESS) {
            if (isset($goods_img)) {
                $tmp = [];
                foreach ($goods_img as $k => $v) {
                    $tmp[] = ['url' => $v, 'goods_id' => $res['data'], 'add_time' => time()];
                }

                db('goods_img')->insertAll($tmp);
            }

            // 提交事务
            Db::commit();
            $this->success($res['msg'], url('index'));
        }

        // 回滚事务
        Db::rollback();
        $this->error($res['msg']);
    }

    public function edit()
    {
        if ($this->login_info['status'] == 0) {
            $this->error('请先完善资料', url('shop/Shop/setting'));
        }

        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $id = "";
        }
        if (preg_match('/[0-9]*/', $id)) {
        } else {
            exit;
        }

        $where['id'] = $id;
        $where['shop_id'] = $where2['shop_id'] = $this->login_info['id'];
        $post = $this->getLogic()->getOne($where);

        $this->assign('id', $id);
        $this->assign('post', $post);
        $this->assign('goods_img_list', db('goods_img')->where(array('goods_id' => $id))->order('listorder asc')->select());

        $type_list = model('GoodsType')->getAll($where2, ['listorder' => 'asc'], ['content'], 15);
        $this->assign('type_list', $type_list);

        //获取类目
        /* $where3['parent_id'] = 0;
        $where3['delete_time'] = 0; //未删除
        $category_list = logic('Category')->getAllCategoryList($where3,['id'=>'desc'],['content']);
        $this->assign('category_list',$category_list); */

        return $this->fetch();
    }

    public function doedit()
    {
        if (!empty($_POST["id"])) {
            $id = $_POST["id"];
        } else {
            $id = "";
            exit;
        }

        $litpic = "";
        if (!empty($_POST["litpic"])) {
            $litpic = $_POST["litpic"];
        } else {
            $_POST['litpic'] = "";
        } //缩略图
        if (empty($_POST["description"])) {
            if (!empty($_POST["content"])) {
                $_POST['description'] = cut_str($_POST["content"]);
            }
        }//description
        $_POST['update_time'] = time(); // 更新时间

        //关键词
        if (!empty($_POST["keywords"])) {
            $_POST['keywords'] = str_replace("，", ",", $_POST["keywords"]);
        } else {
            if (!empty($_POST["title"])) {
                $title = $_POST["title"];
                $title = str_replace("，", "", $title);
                $title = str_replace(",", "", $title);
                $_POST['keywords'] = get_participle($title); // 标题分词
            }
        }

        if (isset($_POST['promote_start_date'])) {
            $_POST['promote_start_date'] = strtotime($_POST['promote_start_date']);
        }
        if (isset($_POST['promote_end_date'])) {
            $_POST['promote_end_date'] = strtotime($_POST['promote_end_date']);
        }
        if (isset($_POST['promote_price']) && empty($_POST['promote_price'])) {
            unset($_POST['promote_price']);
        }
        if (!empty($_POST['goods_img'])) {
            $goods_img = $_POST['goods_img'];
            $_POST['goods_img'] = $_POST['goods_img'][0];
        }

        // 启动事务
        Db::startTrans();

        $res = $this->getLogic()->edit($_POST, array('id' => $id, 'shop_id' => $this->login_info['id']));
        if ($res['code'] == ReturnData::SUCCESS) {
            if (isset($goods_img)) {
                $tmp = [];
                foreach ($goods_img as $k => $v) {
                    $tmp[] = ['url' => $v, 'goods_id' => $id, 'add_time' => time()];
                }

                db('goods_img')->where(array('goods_id' => $id))->delete();
                db('goods_img')->insertAll($tmp);
            }

            // 提交事务
            Db::commit();
            $this->success($res['msg'], url('index'));
        }

        // 回滚事务
        Db::rollback();
        $this->error($res['msg']);
    }

    public function del()
    {
        if (!checkIsNumber(input('id', null))) {
            $this->error('参数错误');
        }
        $where['id'] = input('id');
        $where['shop_id'] = $this->login_info['id'];

        $res = $this->getLogic()->del($where);
        if ($res['code'] == ReturnData::SUCCESS) {
            $this->success("删除成功");
        }

        $this->error($res['msg']);
    }
}