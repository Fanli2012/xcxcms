<?php

namespace app\common\model;

use think\Db;

class Goods extends Base
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
        return db('goods');
    }

    //商品未删除
    const GOODS_UNDELETE = 0;
    //商品状态：0正常，1下架，2申请上架
    const GOODS_STATUS_NORMAL = 0;
    const GOODS_STATUS_UNDERCARRIAGE = 1;
    const GOODS_STATUS_APPLY_SHELVES = 2;
    //状态描述
    public static $goods_status_desc = array(
        self::GOODS_STATUS_NORMAL => '正常',
        self::GOODS_STATUS_UNDERCARRIAGE => '下架',
        self::GOODS_STATUS_APPLY_SHELVES => '申请上架'
    );

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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
        $where['delete_time'] = self::GOODS_UNDELETE;
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
     * 获取商品详情url
     * @param int $param ['id'] 商品ID
     * @return string
     */
    public function getGoodsDetailUrl($param = [])
    {
        if (isset($param['id'])) {
            return $url = '/goods/' . $param['id'];
        }
        return $url = '/goods/';
    }

    /**
     * 获取器——分类名称
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getTypeNameTextAttr($value, $data)
    {
        return model('GoodsType')->getValue(array('id' => $data['type_id']), 'name');
    }

    /**
     * 获取器——审核状态文字
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getStatusTextAttr($value, $data)
    {
        return self::$goods_status_desc[$data['status']];
    }

    /**
     * 获取器——商品图片列表
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getGoodsImgListAttr($value, $data)
    {
        $res = model('GoodsImg')->getAll(['goods_id' => $data['id']]);
        if (!$res) {
            return array();
        }

        foreach ($res as $k => $v) {
            if ($v['url']) {
                $res[$k]['url'] = (substr($v['url'], 0, strlen('http')) === 'http') ? $v['url'] : sysconfig('CMS_SITE_CDN_ADDRESS') . $v['url'];
            }
        }

        return $res;
    }

    /**
     * 获取器——商品价格
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getPriceAttr($value, $data)
    {
        return $this->getGoodsFinalPrice($data);
    }

    /**
     * 获取器——是否促销
     * @param int $value
     * @param array $data
     * @return string
     */
    public function getIsPromoteAttr($value, $data)
    {
        return $this->bargain_price($data['price'], $data['promote_start_date'], $data['promote_end_date']);
    }

    /**
     * 取得商品最终使用价格
     *
     * @param   string $goods_id 商品编号
     * @param   string $goods_num 购买数量
     *
     * @return  商品最终购买价格
     */
    public function getGoodsFinalPrice($goods)
    {
        $final_price = '0'; //商品最终购买价格
        $promote_price = '0'; //商品促销价格
        $user_price = '0'; //商品会员价格，预留

        //取得商品促销价格列表
        $final_price = $goods['price'];

        // 计算商品的促销价格
        if ($goods['promote_price'] > 0) {
            $promote_price = $this->bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
        }

        if ($promote_price > 0) {
            $final_price = $promote_price;
        }

        //返回商品最终购买价格
        return floatval($final_price);
    }

    /**
     * 判断某个商品是否正在特价促销期
     *
     * @access  public
     * @param   float $price 促销价格
     * @param   string $start 促销开始日期
     * @param   string $end 促销结束日期
     * @return  float   如果还在促销期则返回促销价，否则返回0
     */
    public function bargain_price($price, $start, $end)
    {
        if ($price <= 0) {
            return 0;
        }

        $time = time();
        if ($time >= $start && $time <= $end) {
            return $price;
        }

        return 0;
    }

    /**
     * 增加或减少商品库存
     *
     * @access  public
     * @param int $goods_id 商品ID
     * @param int $type 1增加库存
     * @return  bool
     */
    public function changeGoodsStock($where)
    {
        if (isset($where['type']) && $where['type'] == 1) {
            //增加库存
            return $this->setIncrement(array('id' => $where['goods_id']), 'goods_number', $where['goods_number']);
        }

        //减少库存
        return $this->setDecrement(array('id' => $where['goods_id']), 'goods_number', $where['goods_number']);
    }

}