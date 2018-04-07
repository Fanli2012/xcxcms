<?php
namespace app\fladmin\controller;
use app\common\lib\ReturnData;
use app\common\logic\GoodsTypeLogic;

class Goodstype extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new GoodsTypeLogic();
    }
    
    public function index()
    {
		$this->assign('catlist',tree(get_category('goods_type',0)));
		
        return $this->fetch();
    }
    
    public function add()
    {
        if(input('reid',null)!=null)
        {
            $id = input('reid');
            if(preg_match('/[0-9]*/',$id)){}else{exit;}
            if($id!=0)
            {
				$this->assign('postone', $this->getLogic()->getOne(array('id'=>$id),['content']));
            }
            
            $this->assign('id',$id);
        }
        else
        {
            $this->assign('id',0);
        }
		
        return $this->fetch();
    }
    
    public function doadd()
    {
        if(isset($_POST["prid"])){if($_POST["prid"]=="top"){$_POST['parent_id']=0;}else{$_POST['parent_id'] = $_POST["prid"];}unset($_POST["prid"]);}//父级栏目id
        $_POST['addtime'] = time();//添加时间
        
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
    
    public function edit()
    {
        $id = input('id',null);if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('id',$id);
        $post = $this->getLogic()->getOne(array('id'=>$id));
        $parent_id = $post['parent_id'];
        if($parent_id!=0){$this->assign('postone', $this->getLogic()->getOne(array('id'=>$parent_id)));}
        $this->assign('post',$post);
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        
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
    
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');}
		
		if($this->getLogic()->getOne(array('parent_id'=>$id)))
		{
			$this->error('删除失败！请先删除子分类');
		}
		else
		{
			if($this->getLogic()->del(array('id'=>$id)))
			{
				if(db("goods")->where("typeid=$id")->count()>0) //判断该分类下是否有商品，如果有把该分类下的商品也一起删除
				{
					if(db("goods")->where("typeid=$id")->delete())
					{
						$this->success('删除成功', url('index'), '', 1);
					}
					else
					{
						$this->error('分类下的商品删除失败！');
					}
				}
				else
				{
					$this->success('删除成功', url('index'), '', 1);
				}
			}
			else
			{
				$this->error('删除失败！请重新提交');
			}
		}
    }
}