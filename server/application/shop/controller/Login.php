<?php
namespace app\shop\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;

class Login extends Controller
{
    /**
     * 登录页面
     */
	public function index()
	{
		if(session('shop_info'))
		{
			header("Location: ".url('shop/Index/index'));
			exit;
		}
		
        return $this->fetch();
    }
    
    /**
     * 登录处理页面
     */
    public function dologin()
    {
        //验证码验证
        if(!captcha_check(input('captcha',null)))
        {
            $this->error('验证码错误');
        }
        
        if(input('user_name',null)!=null){$user_name = input('user_name');}else{$this->error('请输入账号');}//用户名
        if(input('password',null)!=null){$password = md5(input('password'));}else{$this->error('请输入密码');}//密码
		//echo '<pre>';print_r($_POST);exit;
		//$sql = "(user_name = '".$user_name."' and password = '".$password."') or (email = '".$user_name."' and password = '".$password."')";
        $shop = db("shop")->where(function($query) use ($user_name,$password){$query->where('user_name',$user_name)->where('password',$password);})->whereOr(function($query) use ($user_name,$password){$query->where('email',$user_name)->where('password',$password);})->whereOr(function($query) use ($user_name,$password){$query->where('mobile',$user_name)->where('password',$password);})->find();
        
        if($shop)
        {
			session('shop_info', $shop);
			$this->success('登录成功', url('shop/Index/index'), '', 1);
        }
        
        $this->error('登录失败！请重新登录！！', url('shop/Login/index'), '', 3);
    }
    
    /**
     * 注册
     */
	public function reg()
	{
        if(Helper::isPostRequest())
        {
            $_POST['smstype'] = 1; //注册
            $res = logic('Shop')->reg($_POST);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('shop/Login/index'), '', 1);
            }
            
            $this->error($res['msg']);
        }
        
        return $this->fetch();
    }
    
    /**
     * 注册获取短信验证码
     * @param $mobile 手机号
     * @param $captcha 验证码
     * @return string 成功失败信息
     */
    public function getRegSmscode()
    {
        //验证码验证
        if(!captcha_check(input('captcha',null)))
        {
            exit(json_encode(ReturnData::create(ReturnData::FAIL, null, '图形验证码错误')));
        }
        
        $mobile = input('mobile', null);
        $check = validate('VerifyCode');
        if(!$check->scene('get_smscode_by_smsbao')->check($_REQUEST)){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, $check->getError())));}
        
        $res = model('VerifyCode')->getVerifyCodeBySmsbao($mobile,input('type', 1));
        if ($res['code'] == ReturnData::SUCCESS)
        {
            exit(json_encode(ReturnData::create(ReturnData::SUCCESS, array('code'=>$res['data']['code']))));
        }
        
        exit(json_encode(ReturnData::create(ReturnData::FAIL, null, $res['msg'])));
    }
    
    /**
     * 忘记密码
     */
	public function resetpwd()
	{
        if(Helper::isPostRequest())
        {
            $_POST['smstype'] = 3; //密码修改
            $res = logic('Shop')->resetpwd($_POST);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('shop/Login/index'), '', 1);
            }
            
            $this->error($res['msg']);
        }
        
        return $this->fetch();
    }
    
    //退出登录
    public function loginout()
    {
        session('shop_info', null);
		$this->success('退出成功', '/');
    }
    
    //密码恢复
    /* public function recoverpwd()
    {
        $data["user_name"] = "admin888";
        $data["password"] = "21232f297a57a5a743894a0e4a801fc3";
        
        if(db('shop')->where("id=1")->update($data))
        {
            $this->success('密码恢复成功', CMS_ADMIN.'Login' , 1);
        }
		else
		{
			$this->error('密码恢复失败', CMS_ADMIN.'Login' , 3);
		}
    } */
	
	/**
     * 判断用户名是否存在
     */
    public function userexists()
    {
		$map['user_name']="";
        if(isset($_POST["user_name"]) && !empty($_POST["user_name"]))
        {
            $map['user_name'] = $_POST["user_name"];
        }
		else
		{
			return 0;
		}
        
        return db("shop")->where($map)->count();
    }
}