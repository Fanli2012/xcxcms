<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserLogic;
use app\common\model\User as UserModel;

class User extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserLogic();
    }
    
    //个人中心
    public function index()
	{
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		if(!($res['code']==ReturnData::SUCCESS && $res['data']))
		{
			$this->error('参数错误');
		}
		
		$this->login_info = array_merge($this->login_info, $res['data']);
		session('weixin_user_info', $this->login_info);
        return $this->fetch();
	}
    
    //个人中心-设置
    public function setting()
	{
		$get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		$this->login_info = array_merge($this->login_info, $res['data']);
		session('weixin_user_info', $this->login_info);
		
        return $this->fetch();
	}
	
    //资金管理
    public function account()
	{
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		$this->login_info = array_merge($this->login_info, $res['data']);
		session('weixin_user_info', $this->login_info);
		
        return $this->fetch();
    }
    
	//我的团队-列表
    public function myteam()
	{
		//获取会员信息
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url,$get_data,'GET');
		$this->login_info = array_merge($this->login_info, $res['data']);
        
		//获取用户推介资金信息
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_referral_commission/detail';
		$res = Util::curl_request($url,$get_data,'GET');
		$user_referral_commission = $res['data'];
		$assign_data['user_referral_commission'] = $user_referral_commission;
		
        //获取直属下级会员列表
        $pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/myteam';
		$res = Util::curl_request($url, $get_data, 'GET');
        $assign_data['list'] = $res['data']['list'];
        //总页数
        $assign_data['totalpage'] = ceil($res['data']['count']/$pagesize);
        
        if(isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax']==1)
        {
    		$html = '';
            
            if($res['data']['list'])
            {
                foreach($res['data']['list'] as $k => $v)
                {
                    $html .= '<li><span class="goods_thumb" style="width:72px;height:72px;"><img style="width:72px;height:72px;" alt="'.$v['user_name'].'" src="'.$v['head_img'].'"></span>';
                    $html .= '<div class="goods_info"><p class="goods_tit">'.$v['user_name'].'</p>';
                    $html .= '<p style="line-height:24px;">佣金：'.$v['commission'].'</p>';
                    $html .= '<p style="line-height:24px;">注册时间：'.date('Y-m-d',$v['add_time']).'</p>';
                    $html .= '</div></li>';
                }
            }
            
    		exit(json_encode($html));
    	}
		
		$this->assign($assign_data);
        return $this->fetch();
	}
    
	//推介赚钱
    public function referral()
	{
		//$this->assign($assign_data);
        return $this->fetch();
	}
	
	//转换为帐户余额
    public function user_referral_commission_turn_user_money()
	{
		//$this->assign($assign_data);
        return $this->fetch();
	}
    
}