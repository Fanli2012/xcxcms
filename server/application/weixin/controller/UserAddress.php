<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class UserAddress extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //收货地址-列表
    public function index()
	{
		//参数
        $pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //获取收货地址列表
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_address/index';
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
                    $html .= '<div class="flow-have-adr">';
                    
                    if($v['is_default']==1)
                    {
                        $html .= '<p class="f-h-adr-title"><label>'.$v['name'].'</label><span class="ect-colory">'.$v['mobile'].'</span><span class="fr">默认</span></p>';
                    }
                    else
                    {
                        $html .= '<p class="f-h-adr-title"><label>'.$v['name'].'</label><span class="ect-colory">'.$v['mobile'].'</span></p>';
                    }
                    
                    $html .= '<p class="f-h-adr-con">'.$v['province_name'].$v['city_name'].$v['district_name'].' '.$v['address'].'</p>';
                    $html .= '<div class="adr-edit-del"><a href="'.route('weixin_user_address_update',array('id'=>$v['id'])).'"><i class="iconfont icon-bianji"></i>编辑</a><a href="javascript:del('.$v['id'].');"><i class="iconfont icon-xiao10"></i>删除</a></div>';
                    $html .= '</div>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
		$this->assign($assign_data);
        return $this->fetch();
	}
    
    //收货地址-添加
    public function add()
	{
        return $this->fetch();
	}
    
    //收货地址-修改
    public function edit()
	{
        $id = input('id', '');
        if($id == ''){$this->error('参数错误');}
        
        $get_data = array(
            'id'  => $_REQUEST['id'],
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_address/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
        $assign_data['post'] = $res['data'];
        
		$this->assign($assign_data);
        return $this->fetch();
	}
}