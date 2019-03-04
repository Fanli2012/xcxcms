<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\ArticleLogic;

class Article extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new ArticleLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $limit = input('limit/d',10);
        $offset = input('offset/d', 0);
        if(input('type_id/d', 0) != 0){$where['type_id'] = input('type_id');}
        if(input('keyword', null) != null){$where['title'] = ['like','%'.input('keyword').'%'];}
        if(input('tuijian/d', 0) != 0){$where['tuijian'] = input('tuijian');}
        $where['delete_time'] = 0;
        $where['status'] = 0;
        $orderby = input('orderby','update_time desc');
        
        $res = $this->getLogic()->getList($where,$orderby,['content'],$offset,$limit);
		
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if(!empty($v['litpic'])){$res['list'][$k]['litpic'] = http_host().$v['litpic'];}
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
        $where['delete_time'] = 0;
        $where['status'] = 0;
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if($res['content']){$res['content'] = preg_replace('/src=\"\/uploads\//','src="'.http_host().'/uploads/', $res['content']);}
        if($res['litpic']){$res['litpic'] = http_host().$res['litpic'];}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['add_time'] = $_POST['update_time'] = time();
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