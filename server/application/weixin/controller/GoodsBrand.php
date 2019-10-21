<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class GoodsBrand extends Common
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //商品品牌列表
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
        $url = sysconfig('CMS_API_URL').'/goods_brand/index';
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
                    $html .= '<li><a href="'.$v['goods']['goods_detail_url'].'"><span class="goods_thumb"><img alt="'.$v['goods']['title'].'" src="'.env('APP_URL').$v['goods']['litpic'].'"></span></a>';
                    $html .= '<div class="goods_info"><p class="goods_tit">'.$v['goods']['title'].'</p>';
                    $html .= '<p class="goods_price">￥<b>'.$v['goods']['price'].'</b></p>';
                    $html .= '<p class="goods_des fr"><span id="del_history" onclick="delconfirm(\''.route('weixin_user_goods_history_delete',array('id'=>$v['id'])).'\')">删除</span></p>';
                    $html .= '</div></li>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
		$this->assign($assign_data);
        return $this->fetch();
    }
	
	//商品品牌详情
    public function detail()
	{
        $get_data['id'] = $id;
        $url = sysconfig('CMS_API_URL').'/goods_brand/detail';
		$res = Util::curl_request($url,$postdata,'GET');
        $assign_data['post'] = $res['data'];
        if(!$assign_data['post']){Helper::http404();}
		
        $this->assign($assign_data);
        return $this->fetch();
	}
}