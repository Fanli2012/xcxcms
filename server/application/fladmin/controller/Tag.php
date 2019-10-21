<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\TagLogic;

class Tag extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new TagLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['name'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        $list = $this->getLogic()->getPaginate($where, ['id' => 'desc']);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $_POST['add_time'] = $_POST['update_time'] = time(); //添加时间、更新时间
            $_POST['click'] = rand(200, 500); //点击量

            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                //添加Tag下的文章
                $tagarc = "";
                if (!empty($_POST["tagarc"])) {
                    $tagarc = str_replace("，", ",", $_POST["tagarc"]);
                    if (!preg_match("/^\d*$/", str_replace(",", "", $tagarc))) {
                        $tagarc = "";
                    }
                } //Tag文章列表
                if ($tagarc != "") {
                    $arr = explode(",", $tagarc);
                    foreach ($arr as $row) {
                        $data2['tag_id'] = $res['data'];
                        $data2['article_id'] = $row;
                        logic('Taglist')->add($data2);
                    }
                }

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
            $where['id'] = $where2['tag_id'] = $_POST['id'];
            unset($_POST['id']);

            $_POST['update_time'] = time(); //更新时间

            $tagarc = "";
            if (!empty($_POST["tagarc"])) {
                $tagarc = str_replace("，", ",", $_POST["tagarc"]);
                if (!preg_match("/^\d*$/", str_replace(",", "", $tagarc))) {
                    $tagarc = "";
                }
            } //Tag文章列表
            unset($_POST["tagarc"]);

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                //获取该标签下的文章id
                $posts = model('Taglist')->getAll($where2);
                $article_id_list = "";
                if (!empty($posts)) {
                    foreach ($posts as $row) {
                        $article_id_list = $article_id_list . ',' . $row['article_id'];
                    }
                }
                $article_id_list = ltrim($article_id_list, ",");

                if ($tagarc != "" && $tagarc != $article_id_list) {
                    model('Taglist')->del($where2);

                    $arr = explode(",", $tagarc);
                    foreach ($arr as $row) {
                        $data2['tag_id'] = $where2['tag_id'];
                        $data2['article_id'] = $row;
                        logic('Taglist')->add($data2);
                    }
                } elseif ($tagarc == "") {
                    model('Taglist')->del($where2);
                }

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

        //获取该标签下的文章id
        $posts = model('Taglist')->getAll("tag_id=" . $where['id']);
        $article_id_list = "";
        if (!empty($posts)) {
            foreach ($posts as $row) {
                $article_id_list = $article_id_list . ',' . $row['article_id'];
            }
        }
        $this->assign('article_id_list', ltrim($article_id_list, ","));

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