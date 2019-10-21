<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\MenuLogic;

class Menu extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new MenuLogic();
    }

    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['name'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }

        $list = $this->getLogic()->getPaginate($where, ['id' => 'desc']);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';var_dump($list->total());exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            //添加超级管理员权限
            logic('Access')->add(['role_id' => 1, 'menu_id' => $res['data']]);
            $this->success($res['msg'], url('index'), '', 1);
        }

        if (!empty($_GET['parent_id'])) {
            $parent_id = $_GET['parent_id'];
        } else {
            $parent_id = 0;
        }
        $menu = model('Menu')->category_tree(model('Menu')->get_category());

        $this->assign('menu', $menu);
        $this->assign('parent_id', $parent_id);

        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
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
        $this->assign('post', $post);

        $this->assign('menu', model('Menu')->category_tree(model('Menu')->get_category()));
        $this->assign('id', $where['id']);

        return $this->fetch();
    }

    //删除
    public function del()
    {
        if (!checkIsNumber(input('id', null))) {
            $this->error('删除失败！请重新提交');
        }
        $where['id'] = input('id'); //角色ID

        $res = $this->getLogic()->del($where);
        if ($res['code'] == ReturnData::SUCCESS) {
            //删除权限
            model('Access')->del($where);

            $this->success("删除成功");
        }

        $this->error($res['msg']);
    }

}