<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class UserGoodsCollect extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //列表
    public function index()
	{
		//参数
		$pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //获取收藏列表
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_goods_collect/index';
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
                    $html .= '<li><a href="'.url('goods/detail').'?id='.$v['goods']['id'].'"><span class="goods_thumb"><img alt="'.$v['goods']['title'].'" src="'.$v['goods']['litpic'].'"></span></a>';
                    $html .= '<div class="goods_info"><p class="goods_tit">'.$v['goods']['title'].'</p>';
                    $html .= '<p class="goods_price">￥<b>'.$v['goods']['price'].'</b></p>';
					$html .= '<p class="goods_des fr"><span class="btn" onclick="del_collect(\''.$v['goods']['id'].'\')">删除</span></p>';
					$html .= '</div></li>';
                }
            }
            
    		exit(json_encode($html));
    	}
		
		$this->assign($assign_data);
        return $this->fetch();
    }
}