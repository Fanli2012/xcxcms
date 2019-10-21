<?php

namespace app\shop\controller;

use think\Db;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ShopLogic;

class Shop extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new ShopLogic();
    }

    public function index()
    {
        $this->assign('posts', db("page")->order('id desc')->select());
        return $this->fetch();
    }

    public function doadd()
    {
        $_POST['pubdate'] = time();//更新时间
        $_POST['click'] = rand(200, 500);//点击

        if (db("page")->insert($_POST)) {
            $this->success('添加成功', CMS_ADMIN . 'Page', 1);
        } else {
            $this->error('添加失败！请修改后重新添加', CMS_ADMIN . 'Page/add', 3);
        }
    }

    public function add()
    {
        return $this->fetch();
    }

    public function edit()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $id = "";
        }
        if (preg_match('/[0-9]*/', $id)) {
        } else {
            exit;
        }

        $this->assign('id', $id);
        $this->assign('row', db('page')->where("id=$id")->find());

        return $this->fetch();
    }

    public function doedit()
    {
        if (!empty($_POST["id"])) {
            $id = $_POST["id"];
            unset($_POST["id"]);
        } else {
            $id = "";
            exit;
        }
        $_POST['pubdate'] = time();//更新时间

        if (db('page')->where("id=$id")->update($_POST)) {
            $this->success('修改成功', CMS_ADMIN . 'Page', 1);
        } else {
            $this->error('修改失败！请修改后重新添加', CMS_ADMIN . 'Page/edit?id=' . $_POST["id"], 3);
        }
    }

    public function del()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $this->error('删除失败！请重新提交', CMS_ADMIN . 'Page', 3);
        } //if(preg_match('/[0-9]*/',$id)){}else{exit;}

        if (db('page')->where("id in ($id)")->delete()) {
            $this->success('删除成功', CMS_ADMIN . 'Page', 1);
        } else {
            $this->error('删除失败！请重新提交', CMS_ADMIN . 'Page', 3);
        }
    }

    public function setting()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];

            $res = $this->getLogic()->setting($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                if ($this->login_info['status'] == 0) {
                    $this->getLogic()->edit(['status' => 3], $where);
                } //完善资料之后变更状态为【待审】
                $this->success($res['msg']);
            }

            $this->error($res['msg']);
        }

        //获取类目
        $where2['parent_id'] = 0;
        $where2['delete_time'] = 0; //未删除
        $category_list = logic('Category')->getAllCategoryList($where2, ['id' => 'desc'], ['content']);
        $this->assign('category_list', $category_list);

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }

    public function changePassword()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];
            $res = $this->getLogic()->changePassword($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                session('shop_info', null);
                $this->success($res['msg'], url('shop/login/index'), '', 1);
            }

            $this->error($res['msg']);
        }

        return $this->fetch('shop/changePassword');
    }

    //设置头像
    public function setavatar()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];

            $postdata = array(
                'img' => $_POST['head_img']
            );
            $url = url('api/Image/base64ImageUpload');
            $res = curl_request($url, $postdata, 'POST');
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $res = $this->getLogic()->edit(['head_img' => $res['data']], $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg']);
            }

            $this->error($res['msg']);
        }

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }

    //设置封面
    public function setcover()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg']);
            }

            $this->error($res['msg']);
        }

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }

    //设置二维码
    public function setqrcode()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];

            $postdata = array(
                'img' => $_POST['qrcode']
            );
            $url = url('api/Image/base64ImageUpload');
            $res = curl_request($url, $postdata, 'POST');
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $res = $this->getLogic()->edit(['qrcode' => $res['data']], $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg']);
            }

            $this->error($res['msg']);
        }

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }
}