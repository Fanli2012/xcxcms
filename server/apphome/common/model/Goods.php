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
        $res['count'] = $this->getDb()->where($where)->count();
        $res['list'] = array();
        
        if($res['count'] > 0)
        {
            $res['list'] = $this->getDb()->where($where);
            
            if(is_array($field))
            {
                $res['list'] = $res['list']->field($field[0],true);
            }
            else
            {
                $res['list'] = $res['list']->field($field);
            }
            
            $res['list'] = $res['list']->order($order)->limit($offset.','.$limit)->select();
        }
        
        return $res;
    }
    
    /**
     * 分页，用于前端html输出
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 每页几条
     * @param int $page 当前第几页
     * @return array
     */
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 15)
    {
        $res = $this->getDb()->where($where);
        
        if(is_array($field))
        {
            $res = $res->field($field[0],true);
        }
        else
        {
            $res = $res->field($field);
        }
        
        return $res->order($order)->paginate($limit, false, array('query' => request()->param()));
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
        $res = $this->getDb()->where($where);
            
        if(is_array($field))
        {
            $res = $res->field($field[0],true);
        }
        else
        {
            $res = $res->field($field);
        }
        
        $res = $res->order($order)->limit($limit)->select();
        
        return $res;
    }
    
    /**
     * 获取一条
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getOne($where, $field = '*')
    {
        $res = $this->getDb()->where($where);
        
        if(is_array($field))
        {
            $res = $res->field($field[0],true);
        }
        else
        {
            $res = $res->field($field);
        }
        
        $res = $res->find();
        
        return $res;
    }
    
    /**
     * 添加
     * @param array $data 数据
     * @return int
     */
    public function add($data,$type=0)
    {
        // 过滤数组中的非数据表字段数据
        // return $this->allowField(true)->isUpdate(false)->save($data);
        
        if($type==0)
        {
            // 新增单条数据并返回主键值
            return $this->getDb()->strict(false)->insertGetId($data);
        }
        elseif($type==1)
        {
            // 添加单条数据
            return $this->getDb()->strict(false)->insert($data);
        }
        elseif($type==2)
        {
            /**
             * 添加多条数据
             * $data = [
             *     ['foo' => 'bar', 'bar' => 'foo'],
             *     ['foo' => 'bar1', 'bar' => 'foo1'],
             *     ['foo' => 'bar2', 'bar' => 'foo2']
             * ];
             */
            
            return $this->getDb()->strict(false)->insertAll($data);
        }
    }
    
    /**
     * 修改
     * @param array $data 数据
     * @param array $where 条件
     * @return bool
     */
    public function edit($data, $where = array())
    {
        return $this->allowField(true)->isUpdate(true)->save($data, $where);
    }
    
    /**
     * 删除
     * @param array $where 条件
     * @return bool
     */
    public function del($where)
    {
        return $this->where($where)->delete();
    }
    
    //商品状态 0正常 1已删除 2下架 3申请上架
    public function getStatusAttr($data)
    {
        $arr = array(0 => '正常', 1 => '已删除', 2 => '下架', 3 => '申请上架');
        return $arr[$data['status']];
    }
    
    //是否栏目名称
    public function getTypenameAttr($data)
    {
        return db('goods_type')->where(array('id'=>$data['typeid']))->value('name');
    }
}