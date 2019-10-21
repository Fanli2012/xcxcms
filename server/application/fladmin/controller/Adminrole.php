<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\AdminRoleLogic;

class AdminRole extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new AdminRoleLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['name'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        $list = $this->getLogic()->getPaginate($where, ['listorder' => 'asc']);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
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
        $where['id'] = input('id'); //角色ID

        $res = $this->getLogic()->del($where);
        if ($res['code'] == ReturnData::SUCCESS) {
            //删除权限
            model('Access')->del(['role_id' => $where['id']]);

            $this->success("删除成功");
        }

        $this->error($res['msg']);
    }

    //角色权限设置视图
    public function permissions()
    {
        if (!checkIsNumber(input('id', null))) {
            $this->error('您访问的页面不存在或已被删除');
        }
        $role_id = $where['role_id'] = input('id');

        $menu = array();
        $access = model('Access')->getAll(['role_id' => $role_id]);
        if ($access) {
            foreach ($access as $k => $v) {
                $menu[] = $v['menu_id'];
            }
        }
        //echo '<pre>';print_r($this->get_category());exit;
        $menus = $this->category_tree($this->get_category(), 0);
        if ($menus) {
            foreach ($menus as $k => $v) {
                $menus[$k]['is_access'] = 0;

                if (!empty($menu) && in_array($v['id'], $menu)) {
                    $menus[$k]['is_access'] = 1;
                }
            }
        }

        $this->assign('menus', $menus);
        $this->assign('role_id', $role_id);

        return $this->fetch();
    }

    //角色权限设置
    public function dopermissions()
    {
        $menus = array();
        if ($_POST['menuid'] && $_POST['role_id']) {
            foreach ($_POST['menuid'] as $row) {
                $menus[] = array(
                    'role_id' => $_POST['role_id'],
                    'menu_id' => $row
                );
            }
        }

        if (!$menus) {
            $this->error('操作失败');
        }

        //先删除权限
        model('Access')->del(['role_id' => $_POST['role_id']]);
        //添加权限
        $res = model('Access')->add($menus, 2);
        if ($res) {
            $this->success('操作成功', url('index'), '', 1);
        }

        $this->error('操作失败');
    }

    /**
     * 将列表生成树形结构
     * @param int $parent_id 父级ID
     * @param int $deep 层级
     * @return array
     */
    public function get_category($parent_id = 0, $deep = 0)
    {
        $arr = array();

        $cats = model('Menu')->getAll(['parent_id' => $parent_id], 'listorder asc');
        if ($cats) {
            foreach ($cats as $row)//循环数组
            {
                $row['deep'] = $deep;
                //如果子级不为空
                if ($child = $this->get_category($row["id"], $deep + 1)) {
                    $row['child'] = $child;
                }
                $arr[] = $row;
            }
        }

        return $arr;
    }

    /**
     * 树形结构转成列表
     * @param array $list 数据
     * @param int $parent_id 父级ID
     * @return array
     */
    public function category_tree($list, $parent_id = 0)
    {
        global $temp;
        if (!empty($list)) {
            foreach ($list as $v) {
                $temp[] = array("id" => $v['id'], "deep" => $v['deep'], "name" => $v['name'], "parent_id" => $v['parent_id']);
                //echo $v['id'];
                if (isset($v['child'])) {
                    $this->category_tree($v['child'], $v['parent_id']);
                }
            }
        }

        return $temp;
    }
}