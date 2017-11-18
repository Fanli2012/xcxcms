<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Guestbook extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function addGuestbook()
	{
        //参数
        if(input('post.title', '') !== ''){$data['title'] = input('post.title');}
        if(input('post.msg', '') !== ''){$data['msg'] = input('post.msg');}
        if(input('post.name', '') !== ''){$data['name'] = input('post.name');}
        $data['phone'] = input('post.phone');
        if(input('post.email', '') !== ''){$data['email'] = input('post.email');}
        $data['addtime'] = time();
        
        if($data['phone'] == ''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        if(!Helper::isValidMobile($data['phone'])){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        $res = db('guestbook')->insertGetId($data);
        if($res === false){exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}