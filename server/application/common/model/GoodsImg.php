<?php

namespace app\common\model;

use think\Db;

class GoodsImg extends Base
{
    // 模型会自动对应数据表，模型类的命名规则是除去表前缀的数据表名称，采用驼峰法命名，并且首字母大写，例如：模型名UserType，约定对应数据表think_user_type(假设数据库的前缀定义是 think_)
    // 设置当前模型对应的完整数据表名称
    //protected $table = 'fl_page';

    // 默认主键为自动识别，如果需要指定，可以设置属性
    protected $pk = 'id';

    public function getDb()
    {
        return db('goods_img');
    }

    /**
     * 列表
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $offset 偏移量
     * @param int $limit 取多少条
     * @return array
     */
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 15)
    {
        $res['count'] = self::where($where)->count();
        $res['list'] = array();

        if ($res['count'] > 0) {
            $res['list'] = self::where($where);

            if (is_array($field)) {
                $res['list'] = $res['list']->field($field[0], true);
            } else {
                $res['list'] = $res['list']->field($field);
            }

            if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
                $res['list'] = $res['list']->orderRaw($order[1]);
            } else {
                $res['list'] = $res['list']->order($order);
            }

            $res['list'] = $res['list']->limit($offset . ',' . $limit)->select();
        }

        return $res;
    }

    /**
     * 分页，用于前端html输出
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 每页几条
     * @param int|bool $simple 是否简洁模式或者总记录数
     * @param int $page 当前第几页
     * @return array
     */
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 15, $simple = false)
    {
        $res = self::where($where);

        if (is_array($field)) {
            $res = $res->field($field[0], true);
        } else {
            $res = $res->field($field);
        }

        if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
            $res = $res->orderRaw($order[1]);
        } else {
            $res = $res->order($order);
        }

        return $res->paginate($limit, $simple, array('query' => request()->param()));
    }

    /**
     * 查询全部
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 取多少条
     * @return array
     */
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = self::where($where);

        if (is_array($field)) {
            $res = $res->field($field[0], true);
        } else {
            $res = $res->field($field);
        }

        if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
            $res = $res->orderRaw($order[1]);
        } else {
            $res = $res->order($order);
        }

        $res = $res->limit($limit)->select();

        return $res;
    }

    /**
     * 获取一条
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getOne($where, $field = '*', $order = '')
    {
        $res = self::where($where);

        if (is_array($field)) {
            $res = $res->field($field[0], true);
        } else {
            $res = $res->field($field);
        }

        if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
            $res = $res->orderRaw($order[1]);
        } else {
            $res = $res->order($order);
        }

        $res = $res->find();

        return $res;
    }

    /**
     * 添加
     * @param array $data 数据
     * @return int
     */
    public function add($data, $type = 0)
    {
        // 过滤数组中的非数据表字段数据
        // return $this->allowField(true)->isUpdate(false)->save($data);

        if ($type == 1) {
            // 添加单条数据
            //return $this->allowField(true)->data($data, true)->save();
            return self::strict(false)->insert($data);
        } elseif ($type == 2) {
            /**
             * 添加多条数据
             * $data = [
             *     ['foo' => 'bar', 'bar' => 'foo'],
             *     ['foo' => 'bar1', 'bar' => 'foo1'],
             *     ['foo' => 'bar2', 'bar' => 'foo2']
             * ];
             */

            //return $this->allowField(true)->saveAll($data);
            return self::strict(false)->insertAll($data);
        }

        // 新增单条数据并返回主键值
        return self::strict(false)->insertGetId($data);
    }

    /**
     * 修改
     * @param array $data 数据
     * @param array $where 条件
     * @return bool
     */
    public function edit($data, $where = array())
    {
        //return $this->allowField(true)->save($data, $where);
        return self::strict(false)->where($where)->update($data);
    }

    /**
     * 删除
     * @param array $where 条件
     * @return bool
     */
    public function del($where)
    {
        return self::where($where)->delete();
    }

    /**
     * 统计数量
     * @param array $where 条件
     * @param string $field 字段
     * @return int
     */
    public function getCount($where, $field = '*')
    {
        return self::where($where)->count($field);
    }

    /**
     * 获取最大值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getMax($where, $field)
    {
        return self::where($where)->max($field);
    }

    /**
     * 获取最小值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getMin($where, $field)
    {
        return self::where($where)->min($field);
    }

    /**
     * 获取平均值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getAvg($where, $field)
    {
        return self::where($where)->avg($field);
    }

    /**
     * 统计总和
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getSum($where, $field)
    {
        return self::where($where)->sum($field);
    }

    /**
     * 查询某一字段的值
     * @param array $where 条件
     * @param string $field 字段
     * @return null
     */
    public function getValue($where, $field)
    {
        return self::where($where)->value($field);
    }

    /**
     * 查询某一列的值
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getColumn($where, $field)
    {
        return self::where($where)->column($field);
    }

    /**
     * 某一列的值自增
     * @param array $where 条件
     * @param string $field 字段
     * @param int $step 默认+1
     * @return array
     */
    public function setIncrement($where, $field, $step = 1)
    {
        return self::where($where)->setInc($field, $step);
    }

    /**
     * 某一列的值自减
     * @param array $where 条件
     * @param string $field 字段
     * @param int $step 默认-1
     * @return array
     */
    public function setDecrement($where, $field, $step = 1)
    {
        return self::where($where)->setDec($field, $step);
    }

    /**
     * 打印sql
     */
    public function toSql()
    {
        return self::getLastSql();
    }

}