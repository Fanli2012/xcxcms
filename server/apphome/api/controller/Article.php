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
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.typeid', null) !== null){$where['typeid'] = input('param.typeid');}
        if(input('param.keyword', null) !== null){$where['title'] = ['like','%'.input('param.keyword').'%'];}
        if(input('tuijian', null) !== null){$where['tuijian'] = input('tuijian');}
        $where['ischeck'] = 0;
        $orderby = input('orderby','id desc');
        
        $res = $this->getLogic()->getList($where,$orderby,['body'],$offset,$limit);
		
        if($res['list'])
        {
            foreach($res['list'] as $k=>$v)
            {
                $res['list'][$k]['url'] = http_host().get_front_url(array("id"=>$v['id'],"type"=>'content'));
                if(!empty($v['litpic'])){$res['list'][$k]['litpic'] = http_host().$v['litpic'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        $where['id'] = input('param.id',null);
        
        if($where['id'] == null){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(!empty($res['litpic'])){$res['litpic'] = http_host().$res['litpic'];}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['addtime'] = time();
            $res = $this->getLogic()->add($_POST);
            
            exit(json_encode($res));
        }
    }
    
    //修改
    public function edit()
    {
        if(input('id',null)!=null){$id = input('id');}else{$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(Helper::isPostRequest())
        {
            unset($_POST['id']);
            $where['id'] = $id;
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }
    
    //删除
    public function del()
    {
        if(input('id',null)!=null){$id = input('id');}else{$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(Helper::isPostRequest())
        {
            unset($_POST['id']);
            $where['id'] = $id;
            
            $res = $this->getLogic()->del($where);
            
            exit(json_encode($res));
        }
    }
}