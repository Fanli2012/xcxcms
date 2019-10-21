<?php
namespace app\api\controller;
use app\common\lib\ReturnData;

class Base extends Common
{
	protected $login_info = array();
    
	/**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
        parent::_initialize();
		
		//Token验证
		$this->checkToken();
    }
	
    /**
     * Token验证
     * @param access_token
     * @return array
     */
    public function checkToken()
    {
		//哪些方法不需要TOKEN验证
        $uncheck = array(
			'payment/index',
			'sysconfig/index',
			'sysconfig/detail',
			'shop/index',
			'shop/detail',
			'guestbook/add',
			'verifycode/get_mobile_verify_code',
			'verifycode/check',
			'emailverifycode/get_email_verify_code',
			'emailverifycode/check'
		);
		
        if (!in_array(strtolower(request()->controller().'/'.request()->action()), $uncheck))
        {
            //TOKEN验证
			$access_token = request()->header('AccessToken') ?: request()->param('access_token');
			if(!$access_token){Util::echo_json(ReturnData::create(ReturnData::TOKEN_ERROR));}
			
			$this->login_info = cache('access_token_'.$access_token);
			if (!$this->login_info)
			{
				$token_info = logic('Token')->checkToken($access_token);
				if ($token_info['code']!=ReturnData::SUCCESS) {Util::echo_json($token_info);}
				
				//Token对应的用户信息
				$this->login_info = logic('User')->getUserInfo(array('id'=>$token_info['data']['user_id']));
				cache('access_token_'.$access_token, $this->login_info, 3600); //文件缓存60分钟
			}
        }
    }
}