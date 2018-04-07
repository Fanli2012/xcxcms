<?php
namespace app\common\model;

use app\common\lib\Helper;
use app\common\lib\Sms;
use think\Db;

class VerifyCode extends Base
{
    protected $pk = 'id';
    
    public function getDb()
    {
        return db('verify_code');
    }
    
    const STATUS_UNUSE = 0;
    const STATUS_USE = 1;                                                       //验证码已被使用
    
    const TYPE_GENERAL = 0;                                                     //通用
    const TYPE_REGISTER = 1;                                                    //用户注册业务验证码
    const TYPE_CHANGE_PASSWORD = 2;                                             //密码修改业务验证码
    const TYPE_MOBILEE_BIND = 3;                                                //手机绑定业务验证码
	const TYPE_VERIFYCODE_LOGIN = 4;                                            //验证码登录
	const TYPE_CHANGE_MOBILE = 5;                                               //修改手机号码
	
    //验证码校验
    public function isVerify($mobile, $code, $type)
    {
        return $this->getOne(array('code'=>$code,'mobile'=>$mobile,'type'=>$type,'status'=>self::STATUS_UNUSE,'expired_at'=>array('>',date('Y-m-d H:i:s'))));
    }
    
    //生成验证码
    public function getVerifyCode($mobile,$type,$text='')
    {
        //验证手机号
        if (!Helper::isValidMobile($mobile))
        {
            return ReturnData::create(ReturnData::MOBILE_FORMAT_FAIL);
        }
        
        switch ($type)
        {
            case self::TYPE_GENERAL;//通用
                break;
            case self::TYPE_REGISTER: //用户注册业务验证码
                break;
            case self::TYPE_CHANGE_PASSWORD: //密码修改业务验证码
                break;
            case self::TYPE_MOBILEE_BIND: //手机绑定业务验证码
                break;
            case self::TYPE_VERIFYCODE_LOGIN: //验证码登录
                break;
            case self::TYPE_CHANGE_MOBILE: //修改手机号码
                break;
            default:
                return ReturnData::create(ReturnData::INVALID_VERIFYCODE);
        }
        
        $data['type'] = $type;
        $data['mobile'] = $mobile;
        $data['code'] = rand(1000, 9999);
        $data['status'] = self::STATUS_UNUSE;
        //10分钟有效
        $data['expired_at'] = date('Y-m-d H:i:s',(time()+60*20));
        
        //短信发送验证码
        if (strpos($data['mobile'], '+') !== false)
        {
            $text = "【hoo】Your DC verification Code is: ".$data['code'];
        }
        else
        {
            $text = "【后】您的验证码是".$data['code']."，有效期20分钟。";
        }
        
        Sms::sendByYp($text,$data['mobile']);
		
		$this->add($data);
		
        return ReturnData::create(ReturnData::SUCCESS,array('code' => $data['code']));
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
    
    //类型，0通用，注册，1:手机绑定业务验证码，2:密码修改业务验证码
    public function getTypeAttr($data)
    {
        $arr = array(0 => '通用', 1 => '手机绑定业务验证码', 2 => '密码修改业务验证码');
        return $arr[$data['type']];
    }
    
    //状态
    public function getStatusAttr($data)
    {
        $arr = array(0 => '未使用', 1 => '已使用');
        return $arr[$data['status']];
    }
}