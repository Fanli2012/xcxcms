<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserMoneyLogic;
use app\common\model\UserMoney as UserMoneyModel;

class UserMoney extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new UserMoneyLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (isset($_REQUEST['keyword'])) {
            $where['desc'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        if (isset($_REQUEST['user_id'])) {
            $where['user_id'] = $_REQUEST["user_id"];
        }
        $list = $this->getLogic()->getPaginate($where, 'id desc');

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //后台人工充值
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

            if (!is_numeric($_POST['money']) || $_POST['money'] == 0) {
                $this->error('充值金额格式不正确');
            }

            $user_money_data['type'] = 1; //0增加,1减少
            if (!$_POST['desc']) {
                $this->error('请填写说明/备注');
            }
            $user_money_data['desc'] = $_POST['desc'];
            if ($_POST['money'] > 0) {
                $user_money_data['type'] = 0;
            }

            //增加用户余额及余额记录
            $user_money_data['user_id'] = $_POST['user_id'];
            $user_money_data['money'] = abs($_POST['money']);
            $user_money = logic('UserMoney')->add($user_money_data);
            if ($user_money['code'] != ReturnData::SUCCESS) {
                $this->error('充值失败');
            }

            $this->success('操作成功', url('index'), '', 1);
        }

        $user = model('User')->getOne(array('id' => $_REQUEST['user_id']), 'user_name,mobile,money,id');
        if (!$user) {
            $this->error('参数错误');
        }
        $this->assign('user', $user);

        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

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

    public function change_status()
    {
        if (!empty($_POST['id'])) {
            $id = $_POST['id'];
            unset($_POST['id']);
        } else {
            $id = '';
            exit;
        }

        if (!isset($_POST['type'])) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $user_withdraw = model('UserMoney')->getOne(array('id' => $id, 'status' => 0));
        if (!$user_withdraw) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //0拒绝，1成功
        $edit_user_withdraw = array();
        if ($_POST['type'] == 0) {
            $edit_user_withdraw['status'] = 4;
            //增加用户余额及余额记录
            $user_money_data['user_id'] = $user_withdraw['user_id'];
            $user_money_data['type'] = 0;
            $user_money_data['money'] = $user_withdraw['money'];
            $user_money_data['desc'] = '提现失败-返还余额';
            $user_money = logic('UserMoney')->add($user_money_data);
        } elseif ($_POST['type'] == 1) {
            $edit_user_withdraw['status'] = 2;
        }

        if (!$edit_user_withdraw) {
            return ReturnData::create(ReturnData::FAIL);
        }

        $res = model('UserMoney')->edit($edit_user_withdraw, array('id' => $id));
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS);
    }
}