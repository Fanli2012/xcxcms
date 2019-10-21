<?php
namespace app\api\controller;
use think\Request;
use think\Db;
use think\Log;
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
		
		//跨域访问
        if (config('app_debug') == true)
		{
            header("Access-Control-Allow-Origin:*");
            // 响应类型
            header("Access-Control-Allow-Methods:GET,POST");
            // 响应头设置
            header("Access-Control-Allow-Headers:x-requested-with,content-type,x-access-token,x-access-appid");
        }
		
		//请求日志
		Log::info('【请求地址】：'.request()->ip().' ['.date('Y-m-d H:i:s').'] '.request()->method().' '.'/'.request()->module().'/'.request()->controller().'/'.request()->action());
		Log::info('【请求参数】：'.json_encode(request()->param(), JSON_UNESCAPED_SLASHES));
		Log::info('【请求头】：'.json_encode(request()->header(), JSON_UNESCAPED_SLASHES));
    }
	
    //设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除');
    }
}