<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Product extends Base
{
	public function _initialize()
	{
		parent::_initialize();
        
        //Token::TokenAuth(request()); //TOKEN验证
    }
    
    public function productList()
	{
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.typeid', '') !== ''){$data['typeid'] = input('param.typeid');}
        if(input('param.keyword', '') !== ''){$data['title'] = ['like','%'.input('param.keyword').'%'];}
        $data['status'] = 0;
        
        $res = db('product')->where($data)->field('body',true)->limit("$offset,$limit")->select();
		
        foreach($res as $k=>$v)
        {
            $res[$k]['pubdate'] = date('Y-m-d',$v['pubdate']);
            $res[$k]['addtime'] = date('Y-m-d',$v['addtime']);
            if(!empty($v['litpic'])){$res[$k]['litpic'] = http_host().$v['litpic'];}
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    public function productDetail()
	{
        //参数
        $data['id'] = input('param.id','');
        
        if($data['id'] == ''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        $res = db('product')->where($data)->find();
		
        $res['pubdate'] = date('Y-m-d',$res['pubdate']);
        $res['addtime'] = date('Y-m-d',$res['addtime']);
        if(!empty($res['litpic'])){$res['litpic'] = http_host().$res['litpic'];}
        
        db('product')->where($data)->setInc('click', 1);
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}