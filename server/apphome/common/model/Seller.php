<?php
namespace app\common\model;

use think\Db;

//车行管理
class Seller extends Base
{
    public function getList($_where = [], $_order = '', $_field = '*', $_limit = 10)
    {
        return $this->where($_where)->field($_field)->order($_order)->paginate($_limit, false, ['query' => request()->param()]);
    }
    
    public function getOne($_where, $_field = '*')
    {
        return $this->where($_where)->field($_field)->find();
    }
    
    public function getAll($_where = [], $_order = '', $_field = '*', $_limit = '', $_offset = '0')
    {
        return $this->where($_where)->field($_field)->order($_order)->limit($_offset, $_limit)->select();
    }
    
    public function add($_data)
    {
        return $this->allowField(true)->isUpdate(false)->save($_data);
    }
    
    public function modify($_data, $_where = [])
    {
        return $this->allowField(true)->isUpdate(true)->save($_data, $_where);
    }
    
    public function remove($_where)
    {
        return $this->where($_where)->delete();
    }
    
    public function countList($_where = [])
    {
        return $this->where($_where)->count();
    }
}