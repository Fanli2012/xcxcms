<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Slide extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function slideList()
	{
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.group_id', '') !== ''){$data['group_id'] = input('param.group_id');}
        $data['is_show'] = 0;
        
        $page = db('slide');
        if(isset($data)){$page->where($data);}
        $res = $page->order('rank asc')->limit("$offset,$limit")->select();
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));}
        
        foreach($res as $k=>$v)
        {
            if(!empty($v['pic'])){$res[$k]['pic'] = http_host().$v['pic'];}
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}