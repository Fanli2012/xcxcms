<?php
namespace app\common\logic;
use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\Shop;

class ShopLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getModel()
    {
        return new Shop();
    }
    
    public function getValidate()
    {
        return Loader::validate('Shop');
    }
    
    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);
        
        if($res['list'])
        {
            foreach($res['list'] as $k=>$v)
            {
                //$res['list'][$k] = $this->getDataView($v);
                //$res['list'][$k]['province_text'] = model('Region')->getRegionName($v['province_id']);
                //$res['list'][$k]['city_text'] = model('Region')->getRegionName($v['city_id']);
                //$res['list'][$k]['district_text'] = model('Region')->getRegionName($v['district_id']);
            }
        }
        
        return $res;
    }
    
    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);
        
        $res = $res->each(function($item, $key){
            //$item = $this->getDataView($item);
            //$item['province_text'] = model('Region')->getRegionName($item['province_id']);
            //$item['city_text'] = model('Region')->getRegionName($item['city_id']);
            //$item['district_text'] = model('Region')->getRegionName($item['district_id']);
            
            return $item;
        });
        
        return $res;
    }
    
    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);
        
        if($res)
        {
            foreach($res as $k=>$v)
            {
                //$res[$k] = $this->getDataView($v);
                //$res[$k]['province_text'] = model('Region')->getRegionName($v['province_id']);
                //$res[$k]['city_text'] = model('Region')->getRegionName($v['city_id']);
                //$res[$k]['district_text'] = model('Region')->getRegionName($v['district_id']);
            }
        }
        
        return $res;
    }
    
    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if(!$res){return false;}
        
        //$res = $this->getDataView($res);
        
        //$res['province_text'] = model('Region')->getRegionName($res['province_id']);
        //$res['city_text'] = model('Region')->getRegionName($res['city_id']);
        //$res['district_text'] = model('Region')->getRegionName($res['district_id']);
        
        if(!empty($res['head_img'])){$res['head_img'] = http_host().$res['head_img'];}/* else{$res['head_img'] = http_host().'/images/avatar-loading.png';} */
        if(!empty($res['cover_img'])){$res['cover_img'] = http_host().$res['cover_img'];}else{$res['cover_img'] = http_host().'/images/xcx-banner.jpg';}
        if(!empty($res['business_license_img'])){$res['business_license_img'] = http_host().$res['business_license_img'];}
        $res['wxacode'] = ''; //小程序码图片
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/wxacode/'.$res['id'].'.jpg')){$res['wxacode'] = http_host().'/uploads/wxacode/'.$res['id'].'.jpg';}
        
        $this->getModel()->getDb()->where($where)->setInc('click', 1);
        
        return $res;
    }
    
    //添加
    public function add($data = array(), $type=0)
    {
        if(empty($data)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('add')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        //判断手机号
        if(isset($data['mobile']) && !empty($data['mobile']))
        {
            $where_mobile['mobile'] = $data['mobile'];
            if($this->getModel()->getOne($where_mobile)){
                return ReturnData::create(ReturnData::FAIL,null,'该手机号已被占用');
            }
        }
        
        //判断openid
        if(isset($data['openid']) && !empty($data['openid']))
        {
            $where_openid['openid'] = $data['openid'];
            if($this->getModel()->getOne($where_openid)){
                return ReturnData::create(ReturnData::FAIL,null,'该openid已被占用');
            }
        }
        
        //判断用户名
        if(isset($data['user_name']) && !empty($data['user_name']))
        {
            $where_user_name['user_name'] = $data['user_name'];
            if($this->getModel()->getOne($where_user_name)){
                return ReturnData::create(ReturnData::FAIL,null,'该用户名已被占用');
            }
        }
        
        //判断邮箱
        if(isset($data['email']) && !empty($data['email']))
        {
            $where_email['email'] = $data['email'];
            if($this->getModel()->getOne($where_email)){
                return ReturnData::create(ReturnData::FAIL,null,'该邮箱已被占用');
            }
        }
        
        $data['update_time'] = $data['add_time'] = time();
        if(isset($data['password']) && !empty($data['password'])){$data['password'] = md5($data['password']);}else{$data['password'] = md5('Fanli123456');}
        
        $res = $this->getModel()->add($data,$type);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //修改
    public function edit($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $data['update_time'] = time();
        if(isset($data['password']) && !empty($data['password'])){$data['password'] = md5($data['password']);}else{unset($data['password']);}
        
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
    
    //修改密码
    public function changePassword($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('change_password')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $record = $this->getModel()->getOne($where);
        if(!$record){return ReturnData::create(ReturnData::RECORD_NOT_EXIST);}
        
        if($data['re_password'] != $data['password']){return ReturnData::create(ReturnData::FAIL,null, '确认密码错误');}
        if(md5($data['password']) == $record['password']){return ReturnData::create(ReturnData::FAIL,null, '新旧密码不能一致');}
        if(md5($data['old_password']) != $record['password']){return ReturnData::create(ReturnData::FAIL,null, '旧密码错误');}
        
        $res = $this->getModel()->edit(['password'=>md5($data['password'])],$where);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //修改
    public function setting($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $check = $this->getValidate()->scene('setting')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $data['update_time'] = time();
        $res = $this->getModel()->edit($data,$where);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //注册
    public function reg($data)
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $check = $this->getValidate()->scene('mobile_reg')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        //判断手机号
        if(isset($data['mobile']) && !empty($data['mobile']))
        {
            $where_mobile['mobile'] = $data['mobile'];
            if($this->getModel()->getOne($where_mobile)){
                return ReturnData::create(ReturnData::FAIL,null,'该手机号已被占用');
            }
        }
        
        $data['user_name'] = $data['mobile'];
        $data['add_time'] = $data['update_time'] = time();
        $data['password'] = md5($data['password']);
        $res = $this->getModel()->add($data);
        if($res)
        {
            return ReturnData::create(ReturnData::SUCCESS,$res);
        }
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //重置密码
    public function resetpwd($data)
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $check = $this->getValidate()->scene('resetpwd')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $where_mobile['mobile'] = $data['mobile'];
        $record = $this->getModel()->getOne($where_mobile);
        if(!$record){return ReturnData::create(ReturnData::FAIL, null, '手机号码不存在');}
        
        $data['update_time'] = time();
        $data['password'] = md5($data['password']);
        $res = $this->getModel()->edit($data,['id'=>$record['id']]);
        if($res)
        {
            return ReturnData::create(ReturnData::SUCCESS,$res);
        }
        
        return ReturnData::create(ReturnData::FAIL);
    }
}