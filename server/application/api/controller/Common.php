<?php
namespace app\api\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;

class Common extends Controller
{
    /**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
        parent::_initialize();
    }
	
    /**
     * 获取分页数据及分页导航
     * @param string $modelname 模块名与数据库表名对应
     * @param array  $map       查询条件
     * @param string $orderby   查询排序
     * @param string $field     要返回数据的字段
     * @param int    $listRows  每页数量，默认10条
     * 
     * @return 格式化后输出的数据。内容格式为：
     *     - "code"                 (string)：代码
     *     - "info"                 (string)：信息提示
     * 
     *     - "result" array
     * 
     *     - "img_list"             (array) ：图片队列，默认8张
     *     - "img_title"            (string)：车图名称
     *     - "img_url"              (string)：车图片url地址
     *     - "car_name"             (string)：车名称
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