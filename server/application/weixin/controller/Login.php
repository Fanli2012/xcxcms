<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserLogic;
use app\common\lib\wechat\WechatAuth;

class Login extends Common
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserLogic();
    }
    
    //登录
    public function index()
	{
        $weixin_user_info = session('weixin_user_info');
        if($weixin_user_info && isset($weixin_user_info['token']['expire_time']) && $weixin_user_info['token']['expire_time'] > time())
        {
            if(isset($_SERVER['HTTP_REFERER'])){header('Location: '.$_SERVER['HTTP_REFERER']);exit;}
            header('Location: '.url('user/index'));exit;
        }
        
        $return_url = '';
        if(isset($_REQUEST['return_url']) && !empty($_REQUEST['return_url'])){$return_url = $_REQUEST['return_url']; session('weixin_history_back_url', $return_url);}
        if($return_url == '' && session('weixin_history_back_url')){ $return_url = session('weixin_history_back_url'); }
		
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if($_POST['user_name'] == '')
            {
                $this->error('账号不能为空');
            }
            
            if($_POST['password'] == '')
            {
                $this->error('密码不能为空');
            }
            
            $postdata = array(
                'user_name' => $_POST['user_name'],
                'password' => $_POST['password'],
                'from' => 2
            );
            $url = sysconfig('CMS_API_URL').'/login/index';
            $res = Util::curl_request($url,$postdata,'POST');
            
            if($res['code'] != ReturnData::SUCCESS){$this->error('登录失败');}
            
            session('weixin_user_info', $res['data']);
            session('weixin_history_back_url', null);
			
            if($return_url != ''){header('Location: '.$return_url);exit;}
            header('Location: '.url('user/index'));exit;
        }
		
        return $this->fetch();
    }
	
    //注册
    public function register()
	{
        if(session('weixin_user_info'))
        {
            if(isset($_SERVER["HTTP_REFERER"])){header('Location: '.$_SERVER["HTTP_REFERER"]);exit;}
            header('Location: '.url('user/index'));exit;
        }
        
        $return_url = '';
        if(isset($_REQUEST['return_url']) && !empty($_REQUEST['return_url'])){session('weixin_history_back_url', $_REQUEST['return_url']);}
        if(isset($_REQUEST['invite_code']) && !empty($_REQUEST['invite_code'])){session('weixin_user_invite_code', $_REQUEST['invite_code']);} //推荐人id存在session，首页入口也存了一次
        
        return $this->fetch();
    }
	
    //微信网页授权登录
    public function wx_oauth()
	{
		$weixin_oauth = session('weixin_oauth');
        if (!isset($weixin_oauth['userinfo']))
        {
            $wechat_auth = new WechatAuth(sysconfig('CMS_WX_APPID'),sysconfig('CMS_WX_APPSECRET'));
            
            // 获取code码，用于和微信服务器申请token。 注：依据OAuth2.0要求，此处授权登录需要用户端操作
            if(!isset($_GET['code']))
            {
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                $callback_url = $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; //回调地址，当前页面
                //生成唯一随机串防CSRF攻击
                $state = md5(uniqid(rand(), true));
                session('weixin_oauth.state', $state); //存到SESSION
                $authorize_url = $wechat_auth->get_authorize_url($callback_url, $state);
                
                header("Location: $authorize_url");exit;
            }
            
            // 依据code码去获取openid和access_token，自己的后台服务器直接向微信服务器申请即可
            session('weixin_oauth.code', $_GET['code']);
            
            if($_GET['state'] != session('weixin_oauth.state'))
            {
                $this->error('您访问的页面不存在或已被删除');
            }
            
            //得到 access_token 与 openid
            session('weixin_oauth.token', $wechat_auth->get_access_token($_GET['code']));
            // 依据申请到的access_token和openid，申请Userinfo信息。
            session('weixin_oauth.userinfo', $wechat_auth->get_user_info(session('weixin_oauth.token')['access_token'], session('weixin_oauth.token')['openid']));
        }
        
        $post_data = array(
            'openid' => session('weixin_oauth.userinfo')['openid'],
            'unionid' => isset(session('weixin_oauth.userinfo')['unionid']) ? session('weixin_oauth.userinfo')['unionid'] : '',
            'nickname' => isset(session('weixin_oauth.userinfo')['nickname']) ? Helper::filterEmoji(session('weixin_oauth.userinfo')['nickname']) : '',
            'sex' => session('weixin_oauth.userinfo')['sex'],
            'head_img' => session('weixin_oauth.userinfo')['headimgurl'],
            'parent_id' => session('weixin_user_invite_code') ? session('weixin_user_invite_code') : 0,
            'parent_mobile' => '',
            'mobile' => ''
        );
        $url = sysconfig('CMS_API_URL').'/login/wx_login';
        $res = Util::curl_request($url, $post_data, 'POST');
        if($res['code'] != ReturnData::SUCCESS){$this->error('操作失败');}
        
        session('weixin_user_info', $res['data']);
        header('Location: '.url('user/index'));exit;
	}
    
	/**
	 * 退出登录
	 */
	public function logout()
	{
        //session_unset();
        //session_destroy(); // 退出登录，清除session
        session('weixin_user_info', null);
		$this->success('退出成功', url('index/index'));
	}
	
	/**
	 * 重新登录
	 */
	public function relogin()
	{
        //session_unset();
        //session_destroy(); // 退出登录，清除session
        session('weixin_user_info', null);
		header('Location: '.url('login/index'));exit;
	}
}