<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\RegionLogic;

class Region extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new RegionLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $orderby = input('orderby','id asc');
        $where['parent_id'] = input('id',86);
        
        if ($where['parent_id'] == null)
		{
			return ReturnData::create(ReturnData::PARAMS_ERROR);
		}
        
		$res = $this->getLogic()->getAll($where,$orderby);
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $where['id'] = input('id');
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $res = $this->getLogic()->add($_POST);
            
            exit(json_encode($res));
        }
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            unset($_POST['id']);
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            
            $res = $this->getLogic()->del($where);
            
            exit(json_encode($res));
        }
    }
}