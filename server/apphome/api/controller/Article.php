<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Article extends Base
{
	public function _initialize()
	{
		parent::_initialize();
        
        //Token::TokenAuth(request()); //TOKEN验证
    }
    
    public function articleList()
	{
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.typeid', '') !== ''){$data['typeid'] = input('param.typeid');}
        if(input('param.keyword', '') !== ''){$data['title'] = ['like','%'.input('param.keyword').'%'];}
        $data['ischeck'] = 0;
        
        $res = db('article')->where($data)->field('body',true)->limit("$offset,$limit")->select();
		
        foreach($res as $k=>$v)
        {
            $res[$k]['pubdate'] = date('Y-m-d',$v['pubdate']);
            $res[$k]['addtime'] = date('Y-m-d',$v['addtime']);
            if(!empty($v['litpic'])){$res[$k]['litpic'] = http_host().$v['litpic'];}
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    public function articleDetail()
	{
        //参数
        $data['id'] = input('param.id','');
        $data['ischeck'] = 0;
        
        if($data['id'] == ''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        $res = db('article')->where($data)->find();
		
        $res['pubdate'] = date('Y-m-d',$res['pubdate']);
        $res['addtime'] = date('Y-m-d',$res['addtime']);
        if(!empty($res['litpic'])){$res['litpic'] = http_host().$res['litpic'];}
        
        db('article')->where($data)->setInc('click', 1);
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}