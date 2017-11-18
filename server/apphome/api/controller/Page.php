<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Page extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function sgpageList()
	{
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.keyword', '') !== ''){$data['title'] = ['like','%'.input('param.keyword').'%'];}
        
        $page = db('page');
        if(isset($data)){$page->where($data);}
        $res = $page->field('body',true)->limit("$offset,$limit")->select();
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));}
        
        foreach($res as $k=>$v)
        {
            $res[$k]['pubdate'] = date('Y-m-d',$v['pubdate']);
            if(!empty($v['litpic'])){$res[$k]['litpic'] = http_host().$v['litpic'];}
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    public function sgpageDetail()
	{
        //参数
        if(input('param.id', '') !== ''){$data['id'] = input('param.id');}
        if(input('param.filename', '') !== ''){$data['filename'] = input('param.filename');}
        
        if(!isset($data)){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        $res = db('page')->where($data)->find();
		if(!$res){exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));}
        
        $res['pubdate'] = date('Y-m-d',$res['pubdate']);
        if(!empty($res['litpic'])){$res['litpic'] = http_host().$res['litpic'];}
        
        db('page')->where($data)->setInc('click', 1);
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}