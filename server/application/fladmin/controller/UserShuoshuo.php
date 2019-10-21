<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserShuoshuoLogic;
use app\common\model\UserShuoshuo as UserShuoshuoModel;

class UserShuoshuo extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new UserShuoshuoLogic();
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
        //$list_data = $list->toArray();
        $this->assign('list', $list);
        //echo '<pre>';print_r($list_data);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            if (empty($_POST['shuoshuo_img']) && empty($_POST['desc'])) {
                $this->error('请填写内容');
            }

            $res = $this->getLogic()->add($_POST);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            //添加图片
            if (isset($_POST['shuoshuo_img']) && $_POST['shuoshuo_img']) {
                foreach ($_POST['shuoshuo_img'] as $k => $v) {
                    $aa = logic('UserShuoshuoImg')->add(array('url' => $v, 'user_shuoshuo_id' => $res['data']));
                }
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

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            model('UserShuoshuoImg')->del(array('user_shuoshuo_id' => $where['id']));
            //添加图片
            if (isset($_POST['shuoshuo_img']) && $_POST['shuoshuo_img']) {
                foreach ($_POST['shuoshuo_img'] as $k => $v) {
                    logic('UserShuoshuoImg')->add(['url' => $v, 'user_shuoshuo_id' => $where['id']]);
                }
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
        if ($res['code'] == ReturnData::SUCCESS) {
            $this->success("删除成功");
        }

        $this->error($res['msg']);
    }
}