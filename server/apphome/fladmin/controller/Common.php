<?php
namespace app\fladmin\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;

class Common extends Controller
{
    protected $admin_user_info;
    
    /**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
		$request = Request::instance();
		
		// 批量赋值
        $this->assign([
            'action_name'  => $request->action(),
            'controller_name' => $request->controller(),
            'module_name' => $request->module()
        ]);
		
        $route = $request->action().'/'.$request->controller().'/'.$request->module();
        
		if(!Session::has('admin_user_info'))
		{
			$this->error('您访问的页面不存在或已被删除！', '/',3);
		}
        else
        {
            $this->admin_user_info = Session::get('admin_user_info');
            $this->assign('admin_user_info',$this->admin_user_info);
        }
        
        //判断是否拥有权限
		if($this->admin_user_info['role_id'] <> 1)
		{
			$uncheck = array('fladmin/index/index','fladmin/index/upconfig','fladmin/index/upcache','fladmin/index/welcome');
            
			if(in_array($route, $uncheck))
			{
				
			}
			else
			{
				$menu_id = db('menu')->where(array('module'=>$request->module(), 'controller'=>$request->controller(), 'action'=>$request->action()))->value('id');
				if(!$menu_id){$this->error('你没有权限访问，请联系管理员！', CMS_ADMIN, 3);}
				
				$check = db('access')->where(array('role_id' => $this->admin_user_info['role_id'], 'menu_id' => $menu_id))->find();
				
				if(!$check)
				{
					$this->error('你没有权限访问，请联系管理员！', CMS_ADMIN, 3);
				}
			}
        }
        
        unset($request);
    }
	
    /**
     * 获取分页数据及分页导航
     * @param string $modelname 模块名与数据库表名对应
     * @param array  $map       查询条件
     * @param string $orderby   查询排序
     * @param string $field     要返回数据的字段
     * @param int    $listRows  每页数量，默认15条
     * 
     * @return 格式化后输出的数据。
     */
    public function pageList($modelname, $map = null, $orderby = '', $field = '*', $listRows = 15)
    {
        //获取当前数据对象的【主键名称】
        $id = Db::getTableInfo(config('database.prefix').$modelname, 'pk');
		$this->assign('pkid',$id);
		
        $orderby = !empty($orderby) ? $orderby : $id.' desc';
        
		$request = Request::instance();$param = $request->param(); //等价于$param = request()->param();
		
        $model = Db::name($modelname)->field($field);
        if($map != null){$model = $model->where($map);}
        
		// 查询满足的数据，并且每页显示15条数据
		$voList = $model->order($orderby)->paginate($listRows,false,array('query' => $param));
		
        return $voList;
    }
	
    //设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除！');
    }
}