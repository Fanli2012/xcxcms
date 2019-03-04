<?php
namespace app\api\controller;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class UserBase extends Common
{
    protected $token_info = array();
    
	/**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
        parent::_initialize();
        
        //哪些方法不需要TOKEN验证
        $uncheck = array('index/index','article/index','article/detail','arctype/index','arctype/detail','page/index','page/detail','friendlink/index','payment/index','slide/index','sysconfig/index','region/index','region/detail','goods/index','goods/detail','goodstype/index','goodstype/detail','shop/index','shop/detail','guestbook/add','wechat/miniprogramwxlogin');
        if(!in_array(strtolower(request()->controller().'/'.request()->action()), $uncheck))
        {
            //登录验证
            $this->checkToken();
        }
    }
    
	/**
     * Token验证
     * @param access_token 登录凭据
     * @return array
     */
    public function checkToken()
    {
        $access_token = input('access_token', null);
        if (!$access_token)
        {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, '权限不足')));
        }
        
        $this->loginMember = cache('login_user_token_'.$access_token);
        if(!$this->loginMember)
        {
            $res = logic('UserToken')->getOne(['token' => $access_token, 'expired_time'=>['>',time()]]);
            if (!$res)
            {
                //$this->error('鉴权失败');
                exit(json_encode(ReturnData::create(ReturnData::TOKEN_ERROR)));
            }
            
            $user = logic('User')->getOne(['id' => $res['uid']]);
            if (!$user)
            {
                exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在')));
            }
            
            $this->loginMember = $user;
            cache('login_user_token_'.$access_token, $this->loginMember, 3600);//缓存60分钟
        }
    }
    
    /**
     * token验证
     * @param access_token
     * @return array
     */
    /* public function check_token()
    {
        $access_token = input('access_token');
        $this->token_info = cache('access_token_'.$access_token);
        
        if(!$this->token_info)
        {
            $this->token_info = db('token')->where(array('token'=>$access_token,'expired_at'=>array('>',date('Y-m-d H:i:s'))))->find();
            
            if(!$this->token_info)
            {
                exit(json_encode(ReturnData::create(ReturnData::TOKEN_ERROR)));
            }
            
            cache('access_tokenn_'.$access_token, $this->token_info, 3600); //文件缓存60分钟
        }
    } */
    
    /**
     * 生成token
     *
     * @param $type
     * @param $uid
     * @param $data
     *
     * @return string
     */
    public function get_token($type, $uid, $data = array())
    {
        //支持多账号登录
        if ($token = db('token')->where(array('type' => $type, 'uid' => $uid))->order('id desc')->find())
		{
            if($data == $token['data'] && strtotime($token['expired_at'])>time())
			{
                return array('access_token'=>$token['token'],'expired_at'=>$token['expired_at']);
            }
        }
		
        //生成新token
        $token = md5($type . '-' . $uid . '-' . microtime() . rand(0, 9999));
        $expired_at = date("Y-m-d H:i:s",(time()+3600*24*30)); //token 30天过期
        
        db('token')->insert(array(
            'token'      => $token,
            'type'       => $type,
            'uid'        => $uid,
            'data'       => $data ? json_encode($data) : '',
            'expired_at' => $expired_at
        ));
		
        return array('access_token'=>$token,'expired_at'=>$expired_at,'uid'=>$uid,'type'=>$type);
    }
}