<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class UserMessage extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //用户消息-列表
    public function index()
	{
		//参数
		$pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //获取用户消息
        $postdata = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_message/index';
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
                    if($v['title']==0)
                    {
                        $html .= '<p class="tit">'.$v['title'].'</p>';
                    }
                    
                    if($v['des']==0)
                    {
                        $html .= '<p class="des">'.$v['desc'].'</p>';
                    }
                    
                    $html .= '<p class="time">'.date('Y-m-d H:i:s',$v['add_time']).'</p>';
                    $html .= '</li>';
                }
            }
            
    		exit(json_encode($html));
    	}
		
		$this->assign($assign_data);
        return $this->fetch();
    }
}