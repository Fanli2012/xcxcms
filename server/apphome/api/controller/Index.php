<?php
namespace app\api\controller;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Index extends Base
{
    public function index()
    {
		dd(Article::getList(array('typeid'=>1)));
		//return $this->fetch();
    }
    
    //安卓升级信息
	public function andriodUpgrade()
	{
		$res = array(
			'appname'       => 'nbnbk', //app名字
			'serverVersion' => 2, //服务器版本号
            'serverFlag'    => 1, //服务器标志
			'lastForce'     => 0, //是否强制更新，0不强制，1强制
            'updateurl'     => 'http://www.baidu.com/wap/app-release.apk', //apk下载地址
			'upgradeinfo'   => '描述：3.0.0' //版本更新的描述
		);
        
        exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
	}
    
	//关于
    public function about(Request $request)
    {
        return ReturnData::create(ReturnData::SUCCESS,array('url'=>'http://www.baidu.com'));
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
		imageResize("http://www.thinkphp5.com/uploads/erweima.png", 500, 500);
		//imageResize($_REQUEST['url'], $_REQUEST['w'], $_REQUEST['h']);
    }
	
	public function hprose()
    {
		Vendor('hprose.HproseHttpClient');
		$client = new \HproseHttpClient('http://www.thinkphp5.com/flapi/Api');
        //$client = new \Hprose\Http\Client('http://www.thinkphp5.com/flapi/Api/test1', false); // 创建一个同步的 HTTP 客户端
        // 或者采用
        //$client = new \HproseHttpClient();
        //$client->useService('http://serverName/index.php/Home/Server');
        $result = $client->test1();
		var_dump($result);
	}
	
}
