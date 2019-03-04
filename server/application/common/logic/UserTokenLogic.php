<?php
namespace app\common\logic;
use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\UserToken;

class UserTokenLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getModel()
    {
        return new UserToken();
    }
    
    public function getValidate()
    {
        return Loader::validate('UserToken');
    }
    
    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);
        
        if($res['list'])
        {
            foreach($res['list'] as $k=>$v)
            {
                $res['list'][$k] = $this->getDataView($v);
            }
        }
        
        return $res;
    }
    
    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);
        
        $res = $res->each(function($item, $key){
            $item = $this->getDataView($item);
            return $item;
        });
        
        return $res;
    }
    
    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);
        
        /* if($res)
        {
            foreach($res as $k=>$v)
            {
                $res[$k] = $this->getDataView($v);
            }
        } */
        
        return $res;
    }
    
    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if(!$res){return false;}
        
        $res = $this->getDataView($res);
        
        return $res;
    }
    
    //添加
    public function add($data = array(), $type=0)
    {
        if(empty($data)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('add')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $data['add_time'] = time();
        $res = $this->getModel()->add($data,$type);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //修改
    public function edit($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $res = $this->getModel()->edit($data,$where);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //删除
    public function del($where)
    {
        if(empty($where)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('del')->check($where);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $res = $this->getModel()->del($where);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = array())
    {
        return getDataAttr($this->getModel(),$data);
    }
    
    /**
     * 生成token
     * 
     * @param $uid
     * 
     * @return array
     */
    public function getToken($uid)
    {
        //支持多账号登录
        if ($token = $this->getModel()->getOne(['uid' => $uid, 'expired_time' => ['>',time()]]))
		{
            return array('access_token'=>$token['token'],'expired_time'=>$token['expired_time'],'uid'=>$token['uid']);
        }
		
        //生成新token
        $token = md5($uid . '-' . microtime() . rand(1000, 9999));
        $expired_time = time()+3600*24*30; //token 30天过期
        
        $add_data = array(
            'token'        => $token,
            'uid'          => $uid,
            'expired_time' => $expired_time,
            'add_time'     => time()
        );
        
		$this->getModel()->add($add_data);
        return array('access_token'=>$token, 'expired_time'=>$expired_time, 'uid'=>$uid);
    }
}