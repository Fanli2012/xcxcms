<?php
namespace app\fladmin\controller;
use app\common\lib\ReturnData;
use app\common\logic\GoodsBrandLogic;
use app\common\lib\Helper;

class Goodsbrand extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new GoodsBrandLogic();
    }
    
    public function index()
    {
		$this->assign('posts', $this->getLogic()->getAll('','listorder asc'));
		
        return $this->fetch();
    }
    
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['add_time'] = time();//更新时间
            $_POST['click'] = rand(200,500);//点击
            
            $res = $this->getLogic()->add($_POST);
            if($res['code']==ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'), '', 1);
            }
            else
            {
                $this->error($res['msg']);
            }
        }
        
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('id',null);if(preg_match('/[0-9]*/',$id)){unset($_POST["id"]);}else{exit;}
        
        if(Helper::isPostRequest())
        {
            $res = $this->getLogic()->edit($_POST,array('id'=>$id));
            if ($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'), '', 1);
            }
            else
            {
                $this->error($res['msg']);
            }
        }
        
        $post = $this->getLogic()->getOne(array('id'=>$id));
        $this->assign('post',$post);
        $this->assign('id',$id);
        
        return $this->fetch();
    }
    
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');}
		
		if(db('goods_brand')->where("id in ($id)")->delete())
        {
            $this->success('删除成功');
        }
		else
		{
			$this->error('删除失败！请重新提交');
		}
    }
}