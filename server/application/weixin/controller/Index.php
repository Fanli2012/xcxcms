<?php
namespace app\weixin\controller;
use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

/**
 * 公共-首页
 */
class Index extends Common
{
	/**
     * 首页
     * @param string $modelname 模块名与数据库表名对应
     * @param array  $map       查询条件
     * @param string $orderby   查询排序
     * @param string $field     要返回数据的字段
     * @param int    $listRows  每页数量，默认10条
     * 
     * @return 格式化后输出的数据。内容格式为：
     *     - "code"                 (string)：代码
     *     - "info"                 (string)：信息提示
     * 
     *     - "result" array
     * 
     *     - "img_list"             (array) ：图片队列，默认8张
     *     - "img_title"            (string)：车图名称
     *     - "img_url"              (string)：车图片url地址
     *     - "car_name"             (string)：车名称
     */
    public function index()
	{
		//分享到首页，把推荐码invite_code存下来
        if(isset($_REQUEST['invite_code']) && !empty($_REQUEST['invite_code']))
        {
			session('weixin_user_invite_code', $_REQUEST['invite_code']);
		}
        
		$pagesize = 8;
        $offset = 0;
		if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //最新商品列表
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset
		);
        $url = sysconfig('CMS_API_URL').'/goods/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['goods_list'] = $res['data']['list'];
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
		
        //banner轮播图
        $get_data = array(
            'group_id' => 1,
            'limit' => 5,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/slide/index';
		$res = Util::curl_request($url, $get_data, 'GET');
        $assign_data['slide_list'] = $res['data']['list'];
        
        //最新资讯
        $get_data = array(
            'limit'  => 5,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/article/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['article_list'] = $res['data']['list'];
        
        //畅销商品列表
        $get_data = array(
            'orderby'=> 1,
            'limit'  => 8,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/goods/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['goods_sale_list'] = $res['data']['list'];
        
        //商品推荐
        $get_data = array(
            'tuijian'=> 1,
            'limit'  => 6,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/goods/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['goods_recommend_list'] = $res['data']['list'];
        
        //促销、优惠商品列表
        $get_data = array(
            'orderby'=> 5,
            'limit'  => 4,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/goods/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['goods_promote_list'] = $res['data']['list'];
        //dd($assign_data);
		$this->assign($assign_data);
        return $this->fetch();
    }
	
    //XML地图
    public function sitemap()
    {
        //最新文章
        $where['delete_time'] = 0;
        $where['status'] = 0;
        $where['add_time'] = ['<',time()];
        $list = logic('Article')->getAll($where, 'update_time desc', ['content'], 100);
        $this->assign('list',$list);
        
		return $this->fetch();
    }
    
    //404页面
    public function notfound()
    {
		return $this->fetch();
    }
    
	public function test()
    {
        //echo '<pre>';print_r(request());exit;
		//echo (dirname('/images/uiui/1.jpg'));
		//echo '<pre>';
		//$str='<p><img border="0" src="./images/1.jpg" alt=""/></p>';
		
		//echo getfirstpic($str);
		//$imagepath='.'.getfirstpic($str);
		//$image = new \Think\Image(); 
		//$image->open($imagepath);
		// 按照原图的比例生成一个最大为240*180的缩略图并保存为thumb.jpg
		//$image->thumb(CMS_IMGWIDTH, CMS_IMGHEIGHT)->save('./images/1thumb.jpg');
        
        return $this->fetch();
    }
}