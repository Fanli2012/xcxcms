<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\CartLogic;
use app\common\model\Cart as CartModel;

class Cart extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new CartLogic();
    }
    
    //购物车-列表
    public function index()
	{
        //参数
        $where = array();
        $where['user_id'] = $this->login_info['id'];
        $res = $this->getLogic()->getAll($where);
		
		if($res)
		{
			foreach($res as $k=>$v)
            {
                if($v['litpic']){$res[$k]['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['litpic'];}
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
        $where['user_id'] = $this->login_info['id'];
		
		$res = $this->getLogic()->getOne($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
			$_POST['user_id'] = $this->login_info['id'];
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
			$where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->edit($_POST,$where);
            
            Util::echo_json($res);
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(input('id/d', '') !== ''){$where['id'] = input('id');}
            $where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->del($where);
            
            Util::echo_json($res);
        }
    }
    
    //购物车结算商品列表
    public function cart_checkout_goods_list()
	{
        //参数
        $where['cartids'] = input('cartids','');
        $where['user_id'] = $this->login_info['id'];
        
        if($where['cartids']=='')
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
		
		$res = $this->getLogic()->cartCheckoutGoodsList($where);
		if($res['list'])
		{
			foreach($res['list'] as $k=>$v)
            {
                if($v['litpic']){$res['list'][$k]['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['litpic'];}
            }
		}
		
        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
}