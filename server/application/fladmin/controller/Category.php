<?php

namespace app\fladmin\controller;

use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\CategoryLogic;

class Category extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new CategoryLogic();
    }

    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['name'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }

        $where['parent_id'] = 0;
        $where['delete_time'] = 0; //未删除
        $list = $this->getLogic()->getAllCategoryList($where, ['update_time' => 'desc'], ['content']);

        $this->assign('list', $list);

        return $this->fetch();
    }

    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'));
            }

            $this->error($res['msg']);
        }

        $parent_id = 0;
        if (input('parent_id', null) != null) {
            $parent_id = input('parent_id', 0);
            if (preg_match('/[0-9]*/', $parent_id)) {
            } else {
                exit;
            }
            if ($parent_id != 0) {
                $this->assign('postone', $this->getLogic()->getOne(['id' => $parent_id]));
            }
        }

        $this->assign('parent_id', $parent_id);
        return $this->fetch();
    }

    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];

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
        if (!empty($_REQUEST["id"])) {
            $id = $_REQUEST["id"];
        } else {
            $this->error('删除失败！请重新提交', CMS_ADMIN . 'Category', 3);
        } //if(preg_match('/[0-9]*/',$id)){}else{exit;}

        if (db('category')->where("parent_id=$id")->find()) {
            $this->error('删除失败！请先删除子栏目', CMS_ADMIN . 'Category', 3);
        } else {
            if (db('category')->where("id=$id")->delete()) {
                if (db("article")->where("typeid=$id")->count() > 0) //判断该分类下是否有文章，如果有把该分类下的文章也一起删除
                {
                    if (db("article")->where("typeid=$id")->delete()) {
                        $this->success('删除成功', CMS_ADMIN . 'Category', 1);
                    } else {
                        $this->error('栏目下的文章删除失败！', CMS_ADMIN . 'Category', 3);
                    }
                } else {
                    $this->success('删除成功', CMS_ADMIN . 'Category', 1);
                }
            } else {
                $this->error('删除失败！请重新提交', CMS_ADMIN . 'Category', 3);
            }
        }
    }
}