<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\GoodsLogic;
use app\common\model\Goods as GoodsModel;

class Goods extends Common
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
        //参数
		$timestamp = time();
        $limit = input('limit',10);
        $offset = input('offset', 0);
		$where = array();
        if(input('type_id', '') !== ''){$where['type_id'] = input('type_id');}
        if(input('tuijian', '') !== ''){$where['tuijian'] = input('tuijian');}
		if(input('brand_id', '') !== ''){$where['brand_id'] = input('brand_id');}
        if(input('status', '') === ''){$where['status'] = GoodsModel::GOODS_STATUS_NORMAL;}else{if(input('status') != -1){$where['status'] = input('status');}}
		//价格区间搜索
		if(input('min_price', '') !== '' && input('max_price', '') !== '')
		{
			$where['price'] = array(array('>=', input('min_price')),array('<=', input('max_price')));
		}
		//促销商品
		if(input('is_promote', 0) == 1)
		{
			$where['promote_start_date'] = array('<=', $timestamp);
			$where['promote_end_date'] = array('>=', $timestamp);
		}
        //关键词搜索
        if(input('keyword', '') !== '')
        {
			$where['title'] = array('like','%'.input('keyword').'%');
            //添加搜索关键词
            logic('GoodsSearchword')->add(array('name'=>input('keyword')));
        }
        
		//排序
		$orderby = input('orderby','id desc');
        if(input('orderby', '') !== '')
        {
            switch (input('orderby'))
            {
                case 1:
                    $orderby = 'sale desc'; //销量从高到低
                    break;
                case 2:
                    $orderby = 'comment_number desc'; //评论从高到低
                    break;
                case 3:
                    $orderby = 'price desc'; //价格从高到低
                    break;
                case 4:
                    $orderby = 'price asc'; //价格从低到高
                    break;
                case 5:
                    $orderby = array('orderRaw','rand()'); //随机
                    break;
                default:
                    $orderby = 'update_time desc'; //最新
            }
        }
		
        $res = $this->getLogic()->getList($where, $orderby, ['content'], $offset, $limit);
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if($v['litpic']){$res['list'][$k]['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['litpic'];}
				if($v['goods_img']){$res['list'][$k]['goods_img'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['goods_img'];}
            }
        }
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $where['id'] = input('id');
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        
		if($res['content']){$res['content'] = str_replace(' style=""', '', preg_replace('/src=\"\/uploads\//','src="'.sysconfig('CMS_SITE_CDN_ADDRESS').'/uploads/', $res['content']));}
        if($res['litpic']){$res['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$res['litpic'];}
        if($res['goods_img']){$res['goods_img'] = sysconfig('CMS_SITE_CDN_ADDRESS').$res['goods_img'];}
		
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['add_time'] = $_POST['update_time'] = time();
            $res = $this->getLogic()->add($_POST);
            
            Util::echo_json($res);
        }
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            unset($_POST['id']);
            $_POST['update_time'] = time();
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            Util::echo_json($res);
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            
            $res = $this->getLogic()->del($where);
            
            Util::echo_json($res);
        }
    }
}