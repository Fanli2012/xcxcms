<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Search extends Common
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //搜索页
	public function index()
	{
        //商品热门搜索词列表
        $get_data = array(
            'limit'  => 50,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/goods_searchword/index';
		$res = Util::curl_request($url, $get_data, 'GET');
        $assign_data['goods_searchword_list'] = $res['data']['list'];
		
        $this->assign($assign_data);
        return $this->fetch();
    }
}