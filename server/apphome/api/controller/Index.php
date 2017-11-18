<?php
namespace app\api\controller;

class Index extends Base
{
    public function index()
    {
        echo ':)';
		
		return $this->fetch();
    }
    
    public function arclist()
    {
        echo ':():';
    }
    
    public function erweima()
    {
		header("Content-type: text/html; charset=utf-8");
        
		//imagepng($imgSource);
        /* $url = $_REQUEST['url'];
		
		$url = str_replace("%26","&",$url);
		$url = str_replace("%3F","?",$url);
		$url = str_replace("%3D","=",$url); */
		Vendor('phpqrcode.phpqrcode');
		
        //\QRcode::png("http://www.baidu.com", false, "L", 4 ,2);
		//\QRcode::png('http://www.baidu.com', './uploads/erweima.png', 'L',6, 2);
		return \QRcode::png("http://www.baidu.com",false,"H",6);
		//echo '<img src="/uploads/erweima.png">';
		//return \QRcode::png("http://www.baidu.com",false,QR_ECLEVEL_L,6,2,false,0xFFFFFF,0x000000);;
        //return get_erweima("http://www.baidu.com");
    }
	
	public function imageresize()
    {
		imageResize("http://www.xcxcms.com/uploads/erweima.png", 500, 500);
		//imageResize($_REQUEST['url'], $_REQUEST['w'], $_REQUEST['h']);
    }
	
	public function hprose()
    {
		Vendor('hprose.HproseHttpClient');
		$client = new \HproseHttpClient('http://www.xcxcms.com/api/Api');
        //$client = new \Hprose\Http\Client('http://www.xcxcms.com/flapi/Api/test1', false); // 创建一个同步的 HTTP 客户端
        // 或者采用
        //$client = new \HproseHttpClient();
        //$client->useService('http://serverName/index.php/Home/Server');
        $result = $client->test1();
		var_dump($result);
	}
	
}
