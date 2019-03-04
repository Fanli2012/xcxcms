<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
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
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.typeid', null) !== null){$where['typeid'] = input('param.typeid');}
        if(input('param.keyword', null) !== null){$where['title'] = ['like','%'.input('param.keyword').'%'];}
        if(input('tuijian', null) !== null){$where['tuijian'] = input('tuijian');}
        $where['status'] = 0;
        $orderby = input('orderby','id desc');
        
        $res = $this->getLogic()->getList($where, $orderby, ['content'], $offset, $limit);
		
        if($res['list'])
        {
            foreach($res['list'] as $k=>$v)
            {
                if($v['litpic']){$res['list'][$k]['litpic'] = http_host().$v['litpic'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $where['id'] = input('id');
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if($res['litpic']){$res['litpic'] = http_host().$res['litpic'];}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res->append(['goods_img_list','type_name_text','status_text'])->toArray())));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['add_time'] = $_POST['pubdate'] = time();
            $res = $this->getLogic()->add($_POST);
            
            exit(json_encode($res));
        }
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            unset($_POST['id']);
            $_POST['update_time'] = time();
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            
            $res = $this->getLogic()->del($where);
            
            exit(json_encode($res));
        }
    }
}