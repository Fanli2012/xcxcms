<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\EmailVerifyCodeLogic;

class EmailVerifyCode extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new EmailVerifyCodeLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        $orderby = input('orderby','id desc');
        
        $res = $this->getLogic()->getList($where,$orderby,'*',$offset,$limit);
		
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS,$res));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $where['id'] = input('id');
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS,$res));
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
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            unset($_POST['id']);
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            Util::echo_json($res);
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            
            $res = $this->getLogic()->del($where);
            
            Util::echo_json($res);
        }
    }
    
    /**
     * 获取邮箱验证码
     * @param $email 邮箱
     * @param $captcha 验证码
     * @return string 成功失败信息
     */
    public function get_email_verify_code()
    {
        $res = $this->getLogic()->getEmailCode($_REQUEST);
        if ($res['code'] == ReturnData::SUCCESS)
        {
            Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res['data'], '发送成功'));
        }
        
        Util::echo_json(ReturnData::create(ReturnData::FAIL, null, $res['msg']));
    }
    
    //邮箱验证码校验
    public function check()
	{
		$res = $this->getLogic()->check($_REQUEST);
        if ($res['code'] == ReturnData::SUCCESS)
        {
            Util::echo_json(ReturnData::create(ReturnData::SUCCESS));
        }
        
        Util::echo_json(ReturnData::create(ReturnData::FAIL, null, $res['msg']));
    }
    
}