<?php
// +----------------------------------------------------------------------
// | 递规树
// +----------------------------------------------------------------------
namespace app\common\lib;

class Tree
{
    public $_tree_id = '';
    public $_tree_pid = 0;

    //初始化配制
    public function init($_tree_id = '', $_tree_pid = 0)
    {
        $this->_tree_id = $_tree_id;
        $this->_tree_pid = $_tree_pid;
        return $this;
    }

    //组合一维数组
    public function unlimitedForLevel($cate, $html = '--', $pid = 0, $level = 0)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->_tree_pid] == $pid) {
                $v['level'] = $level + 1;
                $v['html'] = str_repeat($html, $level);
                $arr[] = $v;
                $arr = array_merge($arr, $this->unlimitedForLevel($cate, $html, $v[$this->_tree_id], $level + 1));
            }
        }
        return $arr;
    }

    //组合多维数组
    public function unlimitedForLayer($cate, $name = 'child', $pid = 0)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->_tree_pid] == $pid) {
                $v[$name] = $this->unlimitedForLayer($cate, $name, $v[$this->_tree_id]);
                $arr[] = $v;
            }
        }
        return $arr;
    }

    //传递一个子级分类ID返回所有父级分类
    public function getParents($cate, $id)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->_tree_id] == $id) {
                $pid = $v[$this->_tree_pid];
                $arr[] = $v;
                $arr = array_merge($this->getParents($cate, $v[$this->_tree_pid]), $arr);
            }
        }
        return $arr;
    }

    //传递一个父级分类ID返回所有子级分类ID
    public function getChildsId($cate, $pid)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->_tree_pid] == $pid) {
                $arr[] = $v[$this->_tree_id];
                $arr = array_merge($arr, $this->getChildsId($cate, $v[$this->_tree_id]));
            }
        }
        return $arr;
    }

    //传递一个父级分类ID返回所有子级分类
    public function getChilds($cate, $pid)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->_tree_pid] == $pid) {
                $arr[] = $v;
                $arr = array_merge($arr, $this->getChildsId($cate, $v[$this->_tree_id]));
            }
        }
        return $arr;
    }

    //传递一个父级分类ID返回下级分类
    public function getChildsSub($cate, $pid)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->_tree_pid] == $pid) {
                $arr[] = $v;
            }
        }
        return $arr;
    }
}

?>