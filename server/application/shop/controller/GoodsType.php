<?php

namespace app\shop\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\GoodsTypeLogic;

class GoodsType extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new GoodsTypeLogic();
    }

    public function index()
    {

        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['name'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }

        $where['delete_time'] = 0; //未删除
        $where['shop_id'] = $this->login_info['id'];
        $list = $this->getLogic()->getPaginate($where, ['update_time' => 'desc'], ['content'], 15);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';var_dump($list->total());exit;
        return $this->fetch();
    }

    public function add()
    {
        if (Helper::isPostRequest()) {
            $_POST['shop_id'] = $this->login_info['id'];
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'));
            }

            $this->error($res['msg']);
        }

        return $this->fetch();
    }

    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['shop_id'] = $this->login_info['id'];
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'));
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