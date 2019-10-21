<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\ShopLogic;

class Shop extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new ShopLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        if(input('keyword', null) != null){$where['company_name'] = ['like','%'.input('keyword').'%'];}
        if(input('proxy_id', null) != null){$where['proxy_id'] = input('proxy_id');}
        if(input('industry_id', null) != null){$where['industry_id'] = input('industry_id');}
        if(input('province_id', null) != null){$where['province_id'] = input('province_id');}
        if(input('city_id', null) != null){$where['city_id'] = input('city_id');}
        if(input('district_id', null) != null){$where['district_id'] = input('district_id');}
        
        $res = $this->getLogic()->getList($where,'id desc','*',$offset,$limit);
		
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                unset($res['list'][$k]['password']);
                unset($res['list'][$k]['pay_password']);
                if(!empty($v['head_img'])){$res['list'][$k]['head_img'] = http_host().$v['head_img'];}
                if(!empty($v['cover_img'])){$res['list'][$k]['cover_img'] = http_host().$v['cover_img'];}
                if(!empty($v['business_license_img'])){$res['list'][$k]['business_license_img'] = http_host().$v['business_license_img'];}
            }
        }
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id',null))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        $where['id'] = $id;
        
		$res = $this->getLogic()->getOne($where, '*');
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        
        $res['introduction'] = mb_substr($res['introduction'], 0, 36, 'utf-8');
        
        unset($res['password']);
        unset($res['pay_password']);
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $res = $this->getLogic()->add($_POST);
            
            Util::echo_json($res);
        }
    }
    
    //修改
    public function edit()
    {
        if(!checkIsNumber(input('id',null))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        if(Helper::isPostRequest())
        {
            unset($_POST['id']);
            $where['id'] = $id;
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            Util::echo_json($res);
        }
    }
    
    //删除
    public function del()
    {
        if(!checkIsNumber(input('id',null))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        if(Helper::isPostRequest())
        {
            unset($_POST['id']);
            $where['id'] = $id;
            
            $res = $this->getLogic()->del($where);
            
            Util::echo_json($res);
        }
    }
}