<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserLogic;
use app\common\model\User as UserModel;

class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new UserLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') {
            $where['nickname'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        //用户状态
        if (isset($_REQUEST['status'])) {
            $where['status'] = $_REQUEST['status'];
        }
        $list = $this->getLogic()->getPaginate($where, 'id desc');

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->register($_POST);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $this->success('操作成功', url('index'), '', 1);
        }

        $assign_data['user_rank'] = model('UserRank')->getAll(array(), 'rank asc');
        $this->assign($assign_data);
        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $res = $this->getLogic()->userInfoUpdate($_POST, $where);
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

        $res = $this->getLogic()->del($where);
        if ($res['code'] == ReturnData::SUCCESS) {
            $this->success('删除成功');
        }

        $this->error($res['msg']);
    }

}