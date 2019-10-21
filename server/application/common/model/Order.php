<?php

namespace app\common\model;

use think\Db;

class Order extends Base
{
    // 模型会自动对应数据表，模型类的命名规则是除去表前缀的数据表名称，采用驼峰法命名，并且首字母大写，例如：模型名UserType，约定对应数据表think_user_type(假设数据库的前缀定义是 think_)
    // 设置当前模型对应的完整数据表名称
    //protected $table = 'fl_article';

    // 默认主键为自动识别，如果需要指定，可以设置属性
    protected $pk = 'id';

    // 设置当前模型的数据库连接
    /* protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => '127.0.0.1',
        // 数据库名
        'database'    => 'thinkphp',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => '123456',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => 'fl_',
        // 数据库调试模式
        'debug'       => false,
    ]; */

    public function getDb()
    {
        return db('order');
    }

    //订单未删除
    const ORDER_UNDELETE = 0;
    //订单状态:0生成订单,1已取消(客户触发),2无效(管理员触发),3完成订单
    const ORDER_STATUS_GENERATE = 0;
    const ORDER_STATUS_CANCEL = 1;
    const ORDER_STATUS_INVALID = 2;
    const ORDER_STATUS_COMPLETE = 3;
    //订单状态描述
    public static $order_status_desc = array(
        self::ORDER_STATUS_GENERATE => '生成订单',
        self::ORDER_STATUS_CANCEL => '已取消',
        self::ORDER_STATUS_INVALID => '无效',
        self::ORDER_STATUS_COMPLETE => '交易成功'
    );

    //订单支付状态:0未付款,1已付款
    const ORDER_PAY_STATUS_UNPAY = 0;
    const ORDER_PAY_STATUS_PAY = 1;

    const ORDER_REFUND_STATUS_NORETURN = 0; //无退货

    //订单配送情况:0未发货,1已发货,2已收货
    const ORDER_SHIPPING_STATUS_NOSHIP = 0;
    const ORDER_SHIPPING_STATUS_SHIP = 1;
    const ORDER_SHIPPING_STATUS_RECEIVE = 2;

    const ORDER_UN_COMMENT = 0;//未评价
    const ORDER_IS_COMMENT = 1;//是否评论，1已评价

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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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
        $where['delete_time'] = self::ORDER_UNDELETE;
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

    /**
     * 获取器——订单状态文字:1待付款，2待发货,3待收货,4待评价(确认收货，交易成功),5退款/售后,6已取消,7无效,8退款成功
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getStatusTextAttr($value, $data)
    {
        $res = '';
        if ($data['order_status'] == 0 && $data['pay_status'] == 0) {
            $res = '待付款';
        } elseif ($data['order_status'] == 0 && $data['shipping_status'] == 0 && $data['pay_status'] == 1) {
            $res = '待发货';
        } elseif ($data['order_status'] == 0 && $data['refund_status'] == 0 && $data['shipping_status'] == 1 && $data['pay_status'] == 1) {
            $res = '待收货';
        } elseif ($data['order_status'] == 3 && $data['refund_status'] == 0) {
            $res = '交易成功';
        } elseif ($data['order_status'] == 3 && $data['refund_status'] == 1) {
            $res = '售后中';
        } elseif ($data['order_status'] == 1) {
            $res = '已取消';
        } elseif ($data['order_status'] == 2) {
            $res = '无效';
        } elseif ($data['order_status'] == 3 && $data['refund_status'] == 2) {
            $res = '退款成功';
        }

        return $res;
    }

    //获取订单状态文字:1待付款，2待发货,3待收货,4待评价(确认收货，交易成功),5退款/售后,6已取消,7无效,8退款成功
    public function getOrderStatusNum($data)
    {
        $res = '';
        if ($data['order_status'] == 0 && $data['pay_status'] == 0) {
            $res = 1;
        } elseif ($data['order_status'] == 0 && $data['shipping_status'] == 0 && $data['pay_status'] == 1) {
            $res = 2;
        } elseif ($data['order_status'] == 0 && $data['refund_status'] == 0 && $data['shipping_status'] == 1 && $data['pay_status'] == 1) {
            $res = 3;
        } elseif ($data['order_status'] == 3 && $data['refund_status'] == 0) {
            $res = 4;
        } elseif ($data['order_status'] == 3 && $data['refund_status'] == 1) {
            $res = 5;
        } elseif ($data['order_status'] == 1) {
            $res = 6;
        } elseif ($data['order_status'] == 2) {
            $res = 7;
        } elseif ($data['order_status'] == 3 && $data['refund_status'] == 2) {
            $res = 8;
        }

        return $res;
    }

    /**
     * 获取器——国家名称
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getCountryNameAttr($value, $data)
    {
        if (isset($data['country_id']) && $data['country_id'] > 0) {
            return model('Region')->getValue(array('id' => $data['country_id']), 'name');
        }

        return '';
    }

    /**
     * 获取器——省份名称
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getProvinceNameAttr($value, $data)
    {
        if (isset($data['province_id']) && $data['province_id'] > 0) {
            return model('Region')->getValue(array('id' => $data['province_id']), 'name');
        }

        return '';
    }

    /**
     * 获取器——城市名称
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getCityNameAttr($value, $data)
    {
        if (isset($data['city_id']) && $data['city_id'] > 0) {
            return model('Region')->getValue(array('id' => $data['city_id']), 'name');
        }

        return '';
    }

    /**
     * 获取器——县区名称
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getDistrictNameAttr($value, $data)
    {
        if (isset($data['district_id']) && $data['district_id'] > 0) {
            return model('Region')->getValue(array('id' => $data['district_id']), 'name');
        }

        return '';
    }

    /**
     * 获取器——发票类型文字：0不索要发票，1个人，2企业
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getInvoiceTextAttr($value, $data)
    {
        $arr = array(0 => '无发票', 1 => '个人', 2 => '企业');
        return $arr[$data['invoice']];
    }

    /**
     * 获取器——订单来源:1pc，2weixin，3app，4wap，5miniprogram
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getPlaceTypeTextAttr($value, $data)
    {
        $arr = array(0 => '未知', 1 => 'pc', 2 => 'weixin', 3 => 'app', 4 => 'wap', 5 => 'miniprogram');
        return $arr[$data['place_type']];
    }

    /**
     * 获取器——订单商品列表
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getGoodsListAttr($value, $data)
    {
        //订单商品列表
        $order_goods = model('OrderGoods')->getAll(array('order_id' => $data['id']));
        if (!$order_goods) {
            return array();
        }
        foreach ($order_goods as $k => $v) {
            $order_goods[$k]['refund_status_text'] = model('OrderGoods')->getRefundStatusAttr(null, $v);
        }

        return $order_goods;
    }

    /**
     * 获取器——下单人用户信息
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getUserAttr($value, $data)
    {
        $user = model('User')->getOne(array('id' => $data['user_id']), User::USER_COMMON_FIELD);
        return $user;
    }

    /**
     * 根据订单ID返库存
     * @param int $order_id
     * @return bool
     */
    public function returnStock($order_id)
    {
        $order_goods = model('OrderGoods')->getAll(array('order_id' => $order_id));
        if (!$order_goods) {
            return false;
        }

        foreach ($order_goods as $k => $v) {
            //订单商品直接返库存
            model('Goods')->changeGoodsStock(array('goods_id' => $v['goods_id'], 'goods_number' => $v['goods_number'], 'type' => 1));
        }

        return true;
    }

    /**
     * 订单超时，设为无效
     * @param int $order_id
     * @return bool
     */
    public function orderSetInvalid($order_id)
    {
        $order = $this->edit(array('order_status' => 2, 'note' => '订单超时'), array('id' => $order_id, 'order_status' => 0, 'pay_status' => 0));
        if (!$order) {
            return false;
        }

        //返库存
        $this->returnStock($order_id);

        return true;
    }

}