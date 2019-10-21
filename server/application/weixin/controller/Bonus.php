<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Bonus extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //优惠券列表
    public function index()
	{
		//参数
		$pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //获取优惠券列表
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset
		);
        $url = sysconfig('CMS_API_URL').'/bonus/index';
		$res = Util::curl_request($url,$get_data,'GET');
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
                    $html .= '<a href="javascript:;" onclick="getbonus('.$v['id'].')">';
                    $html .= '<div class="flow-have-adr">';
                    $html .= '<p class="f-h-adr-title"><label>'.$v['name'].'</label><span class="ect-colory fr"><small>￥</small>'.$v['money'].'</span><div class="cl"></div></p>';
                    $html .= '<p class="f-h-adr-con">有效期至'.date('Y-m-d H:i:s', $v['end_time']).' <span class="fr">满'.$v['min_amount'].'可用</span></p>';
                    $html .= '</div></a>';
                }
            }
			
    		exit(json_encode($html));
    	}
        
		$this->assign($assign_data);
        return $this->fetch();
    }
}