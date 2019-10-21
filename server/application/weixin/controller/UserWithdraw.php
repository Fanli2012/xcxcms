<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class UserWithdraw extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //提现列表
    public function index()
	{
		//参数
		$pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //获取提现列表
        $postdata = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_withdraw/index';
		$res = Util::curl_request($url,$postdata,'GET');
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
                    $html .= '<li>';
                    $html .= '<span class="green">- '.$v['money'].'</span>';
                    $html .= '<div class="info"><p class="tit">提现</p>';
                    $html .= '<p class="des">收款账号:'.$v['name'].' ,提现方式:'.$v['method'].' ,姓名:'.$v['name'].'<br>状态:<font color="red">'.$v['status_text'].'</font></p>';
                    $html .= '<p class="time">'.date('Y-m-d H:i:s', $v['add_time']).'</p></div>';
                    $html .= '</li>';
                }
            }
            
    		exit(json_encode($html));
    	}
		
		$this->assign($assign_data);
        return $this->fetch();
    }
	
    //提现
    public function add()
	{
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		$this->login_info = array_merge($this->login_info, $res['data']);
		session('weixin_user_info', $this->login_info);
		
		//是否达到可提现要求，0否
        $assign_data['is_withdraw'] = 0;
        $assign_data['min_withdraw_money'] = sysconfig('CMS_MIN_WITHDRAWAL_MONEY'); //最低可提现金额
        if ( $this->login_info['money'] >= $assign_data['min_withdraw_money'] ) { $assign_data['is_withdraw'] = 1; }
        
		$this->assign($assign_data);
        return $this->fetch();
    }
    
}