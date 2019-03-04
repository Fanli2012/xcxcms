<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;

//二维码,如果输出乱码就转成base64输出
class Qrcode extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function createSimpleQrcode(Request $request)
	{
        //参数
        $url = input('url',null);
        $size = input('size', 6);
        $is_binary = input('is_binary',0); //0表示不是二进制，1表示二进制流base64
        
        if($url==null)
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        
        if($is_binary==1){return get_erweima($url,$size);}
        
		return '<img src="'.get_erweima($url,$size).'">';
    }
    
    //二维码
	public function qrcode()
	{
		$url = $_REQUEST['url'];
		
		$url = str_replace("%26","&",$url);
		$url = str_replace("%3F","?",$url);
		$url = str_replace("%3D","=",$url);

		require_once(EXTEND_PATH.'phpqrcode/phpqrcode.php'); //引入phpqrcode类

		return \QRcode::png($url,false,"H",6);
	}
}