<?php

namespace app\fladmin\controller;

use think\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\BonusLogic;
use app\common\model\Bonus as BonusModel;

class Bonus extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new BonusLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword'])) {
            $where['name'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        //状态：0可用，1不可用
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
            //表单令牌验证
            $validate = new Validate([
                ['__token__', 'require|token', '非法提交|请不要重复提交表单'],
            ]);
            if (!$validate->check($_POST)) {
                $this->error($validate->getError());
            }

            if (isset($_POST['start_time']) && $_POST['start_time'] != '') {
                $_POST['start_time'] = strtotime($_POST['start_time']);
            }
            if (isset($_POST['end_time']) && $_POST['end_time'] != '') {
                $_POST['end_time'] = strtotime($_POST['end_time']);
            }
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $this->success($res['msg'], url('index'), '', 1);
        }

        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            if (isset($_POST['start_time']) && $_POST['start_time'] != '') {
                $_POST['start_time'] = strtotime($_POST['start_time']);
            }
            if (isset($_POST['end_time']) && $_POST['end_time'] != '') {
                $_POST['end_time'] = strtotime($_POST['end_time']);
            }
            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $this->success($res['msg'], url('index'), '', 1);
        }

        if (!checkIsNumber(input('id', null))) {
            $this->error('参数错误');
        }
        $where['id'] = input('id');
        $this->assign('id', $where['id']);

        $post = $this->getLogic()->getOne($where);
        //时间戳转日期格式
        if ($post['start_time'] == 0) {
            $post['start_time'] = '';
        } else {
            $post['start_time'] = date('Y-m-d H:i:s', $post['start_time']);
        }
        if ($post['end_time'] == 0) {
            $post['end_time'] = '';
        } else {
            $post['end_time'] = date('Y-m-d H:i:s', $post['end_time']);
        }

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
        if ($res['code'] != ReturnData::SUCCESS) {
            $this->error($res['msg']);
        }

        $this->success('删除成功');
    }
}