<?php
namespace app\wap\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\GoodsLogic;

class Goods extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new GoodsLogic();
    }
    
    //列表
    public function index()
	{
        $where = [];
        $title = '';
        
        $key = input('key', null);
        if($key != null)
        {
            $arr_key = logic('Article')->getArrByString($key);
            if(!$arr_key){$this->error('您访问的页面不存在或已被删除', '/' , '', 3);}
            
            //分类id
            if(isset($arr_key['f']) && $arr_key['f']>0)
            {
                $type_id = $where['type_id'] = $arr_key['f'];
                
                $post = model('GoodsType')->getOne(['id'=>$arr_key['f']]);
                $this->assign('post',$post);
                
                //面包屑导航
                $this->assign('bread', logic('Goods')->get_goods_type_path($where['type_id']));
            }
        }
        
        /* $key = input('key', null);
        if($key != null)
        {
            $arr_key = $this->getArrByString($key);
            if(!$arr_key){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
            
            //省
            if(isset($arr_key['p']) && !empty($arr_key['p']))
            {
                $where['fl_shop.province_id'] = $arr_key['p'];
                $title = model('Region')->getRegionName($where['fl_shop.province_id']);
                $this->assign('province',$title);
                
                $province_id = $arr_key['p'];
            }
            
            //市
            if(isset($arr_key['c']) && !empty($arr_key['c']))
            {
                $where['fl_shop.city_id'] = $arr_key['c'];
                $region = model('Region')->getOne(['id'=>$where['fl_shop.city_id']]);
                if($region)
                {
                    $title = $region['name'];
                    $this->assign('city',$region['name']);
                    $this->assign('province',model('Region')->getRegionName($region['parent_id']));
                    
                    $province_id = $region['parent_id'];
                }
            }
            
            //区
            if(isset($arr_key['d']) && !empty($arr_key['d']))
            {
                $where['fl_shop.district_id'] = $arr_key['d'];
                $title = model('Region')->getRegionName($where['fl_shop.district_id']);
            }
            
            //判断是否有店铺
            if(isset($arr_key['s']) && !empty($arr_key['s']))
            {
                $where['fl_goods.shop_id'] = $arr_key['s'];
                $title = $title.model('Shop')->getDb()->where(['id'=>$where['fl_goods.shop_id']])->value('company_name');
            }
            
            //商品类目
            if(isset($arr_key['f']) && !empty($arr_key['f']))
            {
                $where['fl_goods.category_id'] = $arr_key['f'];
                $title = $title.model('Category')->getDb()->where(['id'=>$where['fl_goods.category_id']])->value('name');
            }
        } */
        
        $where['delete_time'] = 0;
        $where['status'] = 0;
        $list = $this->getLogic()->getPaginate($where, 'id desc', ['content']);
        if(!$list){$this->error('您访问的页面不存在或已被删除', '/' , '', 3);}
        
        $page = $list->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $this->assign('list', $list);
        
        //推荐商品
        $relate_tuijian_list = cache("index_goods_detail_relate_tuijian_list_$key");
        if(!$relate_tuijian_list)
        {
            $where_tuijian['delete_time'] = 0;
            $where_tuijian['status'] = 0;
            $where_tuijian['tuijian'] = 1;
            if(isset($type_id)){$where_tuijian['type_id'] = $type_id;}
            $relate_tuijian_list = logic('Goods')->getAll($where_tuijian, 'update_time desc', ['content'], 5);
            cache("index_goods_detail_relate_tuijian_list_$key",$relate_tuijian_list,2592000);
        }
        $this->assign('relate_tuijian_list',$relate_tuijian_list);
        
        //随机商品
        $relate_rand_list = cache("index_goods_detail_relate_rand_list_$key");
        if(!$relate_rand_list)
        {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            if(isset($type_id)){$where_rand['type_id'] = $type_id;}
            $relate_rand_list = logic('Goods')->getAll($where_rand, 'rand()', ['content'], 5);
            cache("index_goods_detail_relate_rand_list_$key",$relate_rand_list,2592000);
        }
        $this->assign('relate_rand_list',$relate_rand_list);
        
        //seo标题设置
        $title = $title.'最新动态';
        $this->assign('title',$title);
        return $this->fetch();
    }
	
    //详情
    public function detail()
	{
        if(!checkIsNumber(input('id',null))){$this->error('您访问的页面不存在或已被删除', '/' , '', 3);}
        $id = input('id');
        
        $post = cache("index_goods_detail_$id");
        if(!$post)
        {
            $where['id'] = $id;
            $post = $this->getLogic()->getOne($where);
            if(!$post){$this->error('您访问的页面不存在或已被删除', '/' , '', 3);}
            cache("index_goods_detail_$id",$post,2592000);
            
        }
        $this->assign('post',$post);
        
        //最新文章
        $relate_zuixin_list = cache("index_goods_detail_relate_zuixin_list_$id");
        if(!$relate_zuixin_list)
        {
            $where_zuixin['delete_time'] = 0;
            $where_zuixin['status'] = 0;
            $where_zuixin['type_id'] = $post['type_id'];
            $where_zuixin['id'] = ['<',$id];
            $relate_zuixin_list = logic('Goods')->getAll($where_zuixin, 'update_time desc', ['content'], 5);
            if(!$relate_zuixin_list){unset($where_zuixin['id']);$relate_zuixin_list = logic('Goods')->getAll($where_zuixin, 'update_time desc', ['content'], 5);}
            cache("index_goods_detail_relate_zuixin_list_$id",$relate_zuixin_list,2592000);
        }
        $this->assign('relate_zuixin_list',$relate_zuixin_list);
        
        //随机文章
        $relate_rand_list = cache("index_goods_detail_relate_rand_list_$id");
        if(!$relate_rand_list)
        {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            $where_rand['type_id'] = $post['type_id'];
            $relate_rand_list = logic('Goods')->getAll($where_rand, 'rand()', ['content'], 5);
            cache("index_goods_detail_relate_rand_list_$id",$relate_rand_list,2592000);
        }
        $this->assign('relate_rand_list',$relate_rand_list);
        
        //面包屑导航
        $this->assign('bread', logic('Goods')->get_goods_type_path($post['type_id']));
        
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