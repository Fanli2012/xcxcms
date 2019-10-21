<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\JobLogic;

class Job extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new JobLogic();
    }

    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['title'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        $where['delete_time'] = 0; //未删除
        $list = $this->getLogic()->getPaginate($where, ['update_time' => 'desc'], ['content']);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';var_dump($list);exit;
        return $this->fetch();
    }

    public function add()
    {
        if (Helper::isPostRequest()) {
            $_POST['add_time'] = $_POST['update_time'] = time();//添加、更新时间
            $_POST['click'] = rand(200, 500);//点击

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
            $_POST['update_time'] = time();
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

        $res = $this->getLogic()->del($where);
        if ($res['code'] == ReturnData::SUCCESS) {
            $this->success("删除成功");
        }

        $this->error($res['msg']);
    }
}