<?php
namespace app\api\controller;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Index extends Common
{
    public function index()
    {$current_url = request()->url();
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
        
        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
	}
    
	// 关于
    public function about(Request $request)
    {
        return ReturnData::create(ReturnData::SUCCESS,array('url'=>'http://www.baidu.com'));
    }
	
    //配置信息
	public function config()
	{
		$res = array(
			'company_name' => '繁橙工作室', //公司名称
            'cover_img' => http_host().'/images/xcx-banner.jpg', //封面
			'point_lat' => '24.489506', //纬度
            'point_lng' => '118.193202', //经度
			'website' => 'http://fc.xyabb.com/', //官网
			'contact' => '范例', //联系人
			'contact_information' => '15280719357', //联系方式
            'introduction' => '繁橙工作室为广大客户提供企业建站、微商城、分销系统、小程序等一站式服务。', //企业简介
			'content' => '<p>繁橙工作室为广大客户提供企业建站、微信建站、手机建站、微商城、分销系统、微官网、小程序等一站式服务。5年专注于网站建设，已服务过1000+用户和客户。</p><p><br/></p><p>核心价值观：以客户为中心，结果导向，删繁就简，追求极致。</p><p><br/></p><p>专注于中小企业一站式产品服务，助力中小企业轻松拥抱互联网。</p><p><br/></p><p>经营理念：平台化、可定制、低成本、一站式。</p><p><br/></p><p>我们是一群善于学习的年轻人，在瞬息万变的移动互联网，不断挑战自我，不断创新，拥抱变化，追求卓越，从未停歇。我们是怀揣着理想的团队，由兴趣开始，因理想而坚持，梦想与工作并肩而行。我们的团队有来自美图、美亚柏科、网宿科技、4399、网龙等厦门比较酷的互联网公司精英，因为相信互联网的广阔市场和前景而走到一起。过硬的技术、丰富的经验是我们赖以生存的基础，我们不但说得到还做得到。</p>', //关于我们
            'email' => '277023115@qq.com', //微信号
			'wechat' => 'jkui2012', //微信号
            'qq' => '277023115', //qq
            'main_product' => '中小企业解决方案：PC+手机+微信网站+小程序+APP', //主营产品或服务
            'zipcode' => '361000', //邮编
            'fax' => '', //传真
            'zhiwu' => '经理', //职务
			'head_img' => http_host().'/images/avatar.jpg', //头像
			'province_text' => '福建', //省
			'city_text' => '厦门', //市
			'district_text' => '湖里', //区
			'address' => '软件园二期观日路48号', //详情地址
		);
        
        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
	}
    
    /**
     * 文件转Base64二进制流
     * @param $url 网络文件路径，绝对地址
     * @return string
     */
    public function getFileBinary()
    {
        $str = file_get_contents($_REQUEST['url']);
        Util::echo_json(ReturnData::create(ReturnData::SUCCESS,chunk_split(base64_encode($str))));
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
	
	public function imageresize()
    {
		imageResize("http://www.thinkphp5.com/uploads/erweima.png", 500, 500);
		//imageResize($_REQUEST['url'], $_REQUEST['w'], $_REQUEST['h']);
    }
	
	public function hprose()
    {
		//导入类库
		include(EXTEND_PATH.'hprose/HproseHttpClient.php'); //引入Hprose类
		$client = new \HproseHttpClient('http://www.thinkphp5.com/flapi/Api');
        //$client = new \Hprose\Http\Client('http://www.thinkphp5.com/flapi/Api/test1', false); // 创建一个同步的 HTTP 客户端
        // 或者采用
        //$client = new \HproseHttpClient();
        //$client->useService('http://serverName/index.php/Home/Server');
        $result = $client->test1();
		var_dump($result);
	}
	
}
