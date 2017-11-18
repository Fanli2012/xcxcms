<?php
namespace app\common\model;

class Menu extends Base
{
	// 设置当前模型对应的完整数据表名称
    protected $table = 'menu';
    
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
	
	//获取后台管理员所具有权限的菜单列表
	public function getPermissionsMenu($role_id, $pid=0, $pad=0)
	{
		$res = array();
		
		$where['fl_access.role_id'] = $role_id;
		$where['fl_menu.pid'] = $pid;
		$where["fl_menu.status"] = 1;
		
		$menu =db('menu')
			->join('fl_access', 'fl_access.menu_id = fl_menu.id')
            ->field('fl_menu.*, fl_access.role_id')
			->where($where)
			->order('fl_menu.listorder asc')
            ->select();
		
		if($menu)
		{
			foreach($menu as $row)
			{
				$row['deep'] = $pad;
				
				if($PermissionsMenu = $this->getPermissionsMenu($role_id, $row['id'], $pad+1))
				{
					$row['child'] = $PermissionsMenu;
				}
				
				$res[] = $row;
			}
		}
		
		return $res;
	}
}