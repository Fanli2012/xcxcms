<?php
namespace app\fladmin\controller;

class AdminRole extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
		$posts = parent::pageList('admin_role', '', 'listorder desc');
		
        $this->assign('page',$posts->render());
        $this->assign('posts',$posts);
        
		return $this->fetch();
    }
	
	public function add()
    {
		return $this->fetch();
    }
    
    public function doadd()
    {
		if(db('admin_role')->insert($_POST))
        {
			$this->success('添加成功！', CMS_ADMIN.'adminrole' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'adminrole' , 3);
		}
    }
    
    public function edit()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$id="";}
        if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
		$this->assign('id',$id);
        $this->assign('post',db('admin_role')->where('id='.$id)->find());
        return $this->fetch();
    }
	
	public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else {$id="";exit;}
        
		if(db('admin_role')->where('id='.$id)->update($_POST))
        {
            $this->success('修改成功！', CMS_ADMIN.'adminrole' , 1);
        }
		else
		{
			$this->error('修改失败！', CMS_ADMIN.'adminrole' , 3);
		}
    }
	
	public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('删除失败！请重新提交');}
		
		if(db("admin_role")->where("id in ($id)")->delete())
        {
            $this->success('删除成功');
        }
		else
		{
			$this->error('删除失败！请重新提交');
		}
    }
	
	//角色权限设置视图
	public function permissions()
    {
		if(!empty($_GET["id"])){$role_id = $_GET["id"];}else{$this->error('您访问的页面不存在或已被删除！');}
		
		$menu = array();
		$access = db('access')->where('role_id='.$role_id)->select();
		if($access)
		{
			foreach($access as $k=>$v)
			{
				$menu[] = $v['menu_id'];
			}
		}
		
		$menus = $this->category_tree($this->get_category('menu',0));
		foreach($menus as $k=>$v)
		{
			$menus[$k]['is_access'] = 0;
			
			if(!empty($menu) && in_array($v['id'], $menu))
			{
				$menus[$k]['is_access'] = 1;
			}
		}
		
        $this->assign('menus',$menus);
        $this->assign('role_id',$role_id);
		
		return $this->fetch();
    }
	
	//角色权限设置
	public function dopermissions()
    {
		$menus = array();
		if($_POST['menuid'] && $_POST['role_id'])
		{
			foreach($_POST['menuid'] as $row)
			{
				$menus[] = array(
					'role_id' => $_POST['role_id'],
					'menu_id' => $row
				);
			}
		}
		else
		{
			$this->error('操作失败！');
		}
		
		$access = db('access');
		$access->where('role_id='.$_POST['role_id'])->delete();
		
		if($access->insertAll($menus))
        {
            $this->success('操作成功！');
        }
		else
		{
			$this->error('操作失败！');
		}
    }
	
	//将栏目列表生成数组
	public function get_category($modelname,$pid=0,$pad=0)
	{
		$arr=array();
		
		$cats = db($modelname)->where("pid=$pid")->order('id asc')->select();
		
		if($cats)
		{
			foreach($cats as $row)//循环数组
			{
				$row['deep'] = $pad;
				if($child = $this->get_category($modelname,$row["id"],$pad+1))//如果子级不为空
				{
					$row['child'] = $child;
				}
				$arr[] = $row;
			}
			return $arr;
		}
	}

	public function category_tree($list,$pid=0)
	{
		global $temp;
		if(!empty($list))
		{
			foreach($list as $v)
			{
				$temp[] = array("id"=>$v['id'],"deep"=>$v['deep'],"name"=>$v['name'],"pid"=>$v['pid']);
				//echo $v['id'];
				if(array_key_exists("child",$v))
				{
					$this->category_tree($v['child'],$v['pid']);
				}
			}
		}
		
		return $temp;
	}
}