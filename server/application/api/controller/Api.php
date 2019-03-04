<?php
namespace app\api\controller;

class Api extends Hprose
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
		//Vendor('phpqrcode.phpqrcode');
        include(EXTEND_PATH.'phpqrcode/phpqrcode.php'); //引入phpqrcode类

        //\QRcode::png("http://www.baidu.com", false, "L", 4 ,2);
		//\QRcode::png('http://www.baidu.com', './uploads/erweima.png', 'L',6, 2);
		return \QRcode::png("http://www.baidu.com",false,"H",6);
		//echo '<img src="/uploads/erweima.png">';
		//return \QRcode::png("http://www.baidu.com",false,QR_ECLEVEL_L,6,2,false,0xFFFFFF,0x000000);;
        //return get_erweima("http://www.baidu.com");
    }
	
	function test1()
	{
        return 'qwewe';
    }
	
    public function test2()
	{
        return 'test2';
    }
}
