<?php

namespace app\shop\controller;

use think\Db;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\SlideLogic;

class Slide extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new SlideLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['title'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        $where['shop_id'] = $this->login_info['id'];
        $posts = $this->getLogic()->getPaginate($where, ['id' => 'desc']);

        $this->assign('page', $posts->render());
        $list = $posts->toArray();
        $this->assign('list', $list);

        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        $where['shop_id'] = $this->login_info['id'];
        $count = model('Slide')->getCount($where);
        if ($count >= 5) {
            $this->error('最多5张轮播图', url('index'));
        }

        if (Helper::isPostRequest()) {
            $_POST['shop_id'] = $this->login_info['id'];
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

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

        return $this->fetch();
    }

    //删除
    public function del()
    {
        if (!checkIsNumber(input('id', null))) {
            $this->error('删除失败！请重新提交');
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