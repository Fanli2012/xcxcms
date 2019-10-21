<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Goods extends Common
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
		
        if(input('type_id', '') != ''){$param['type_id'] = input('type_id');}
        if(input('tuijian', '') != ''){$param['tuijian'] = input('tuijian');}
        if(input('status', '') != ''){$param['status'] = input('status');}
        if(input('is_promote', '') != ''){$param['is_promote'] = input('is_promote');}
        if(input('orderby', '') != ''){$param['orderby'] = input('orderby');}
        $param['max_price'] = 99999;if(input('max_price', '') != ''){$param['max_price'] = input('max_price');}
        $param['min_price'] = 0;if(input('min_price', '') != ''){$param['min_price'] = input('min_price');}
        if(input('brand_id', '') != ''){$param['brand_id'] = input('brand_id');}
        if(input('keyword', '') != ''){$param['keyword'] = input('keyword');}
        //获取商品列表
        $get_data = $param;
        $get_data['limit'] = $pagesize;
        $get_data['offset'] = $offset;
        $url = sysconfig('CMS_API_URL').'/goods/index';
		$res = Util::curl_request($url, $get_data, 'GET');
        $assign_data['goods_list'] = $res['data']['list'];
        $assign_data['request_param'] = $param;
		//总页数
        $assign_data['totalpage'] = ceil($res['data']['count']/$pagesize);
        if(isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax']==1)
        {
    		$html = '';
            
            if($res['data']['list'])
            {
                foreach($res['data']['list'] as $k => $v)
                {
                    $html .= '<li><a href="'.url('goods/detail').'?id='.$v['id'].'">';
					if($v['is_promote']>0)
					{
						$html .= '<span class="label">限时抢购</span>';
					}
					$html .= '<img alt="'.$v['title'].'" src="'.$v['litpic'].'">';
					$html .= '<div class="ll-list-info">';
					$html .= '<p class="ll-list-tit2">'.$v['title'].'</p>';
					$html .= '<p class="ll-list-click">'.$v['click'].'人查看</p>';
					$html .= '<div class="ll-list-price"><span class="price">￥'.$v['price'].'</span> <span class="market-price">￥'.$v['market_price'].'</span></div>';
					$html .= '</div></a></li>';
                }
            }
            
    		exit(json_encode($html));
    	}
		
        $this->assign($assign_data);
        return $this->fetch();
    }
	
    //详情
    public function detail()
	{
		if(!checkIsNumber(input('id',null))){Helper::http404();}
        $id = input('id');
		//获取商品详情
        $get_data = array(
            'id'  => $id
		);
        $url = sysconfig('CMS_API_URL').'/goods/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		if(empty($res['data'])){Helper::http404();}
        $post = $res['data'];
        //判断用户是否收藏该商品，0未收藏，1已收藏
		$post['is_collect'] = 0;
        if($this->login_info)
		{
			$get_data = array(
				'goods_id' => $id,
				'access_token' => $this->login_info['token']['token']
			);
			$url = sysconfig('CMS_API_URL').'/user_goods_collect/detail';
			$res = Util::curl_request($url, $get_data, 'GET');
			if($res['code'] == ReturnData::SUCCESS || !empty($res['data'])){$post['is_collect'] = 1;}
		}
        //添加浏览记录
        if($this->login_info)
        {
            $post_data = array(
                'goods_id'  => $id,
                'access_token' => $this->login_info['token']['token']
            );
            $url = sysconfig('CMS_API_URL').'/user_goods_history/add';
            Util::curl_request($url, $post_data, 'POST');
        }
        
		$assign_data['post'] = $post;
        $this->assign($assign_data);
        return $this->fetch();
    }
    
	//商品分类页
    public function category_list()
	{
		$assign_data['type_id'] = input('type_id', ''); //分类ID
		
        $pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        
        //获取商品列表
        $get_data = array(
            'type_id' => $assign_data['type_id'],
            'limit'  => $pagesize,
            'offset' => $offset
		);
        $url = sysconfig('CMS_API_URL').'/goods/index';
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
                    $html .= '<li><a href="'.url('goods/detail').'?id='.$v['id'].'">';
					if($v['is_promote']>0){ $html .= '<span class="label">限时抢购</span>'; }
					$html .= '<img alt="'.$v['title'].'" src="'.$v['litpic'].'">';
					$html .= '<div class="ll-list-info">';
					$html .= '<p class="ll-list-tit2">'.$v['title'].'</p>';
					$html .= '<p class="ll-list-click">'.$v['click'].'人查看</p>';
					$html .= '<div class="ll-list-price"><span class="price">￥'.$v['price'].'</span> <span class="market-price">￥'.$v['market_price'].'</span></div>';
					$html .= '</div></a></li>';
                }
				
				//另一种风格
                /* foreach($res['data']['list'] as $k => $v)
                {
                    $html .= '<li>';
                    $html .= '<a href="'.url('goods/detail').'?id='.$v['id'].'"><img class="imgzsy" alt="'.$v['title'].'" src="'.$v['litpic'].'"><div class="goods_info"><p class="goods_tit">';
                    
                    if($v['is_promote']>0)
                    {
                        $html .= '<span class="badge_comm" style="background-color:#f23030;">Hot</span>';
                    }
                    
                    $html .= $v['title'].'</p><div class="goods_price">￥<b>'.$v['price'].'</b></div></div></a>';
                    $html .= '</li>';
                } */ 
            }
            
    		exit(json_encode($html));
    	}
        
        //商品分类列表
        $get_data = array(
            'parent_id' => 0,
            'limit'     => 15,
            'offset'    => 0
		);
        $url = sysconfig('CMS_API_URL').'/goods_type/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['goods_type_list'] = $res['data']['list'];
		
		$this->assign($assign_data);
        return $this->fetch();
	}
}