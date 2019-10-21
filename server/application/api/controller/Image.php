<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\service\AliyunOSS;
use app\common\lib\wechat\WechatAuth;

class Image extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public $path;
    public $public_path;
    public $file_size = 2048000; //最大文件上传大小2M
    
    public function __construct()
    {
        parent::__construct();
        
		$this->file_size = sysconfig('IMAGE_UPLOAD_MAX_FILESIZE'); //最大文件上传大小2M
		
        $this->path = '/uploads/'.date('Y/m',time());
        $this->public_path = $_SERVER['DOCUMENT_ROOT'];
    }
    
    //文件/图片上传，成功返回路径，不含域名
    public function image_upload()
	{
        $res = [];
        $files = $_FILES;//得到传输的数据
        
        if($files)
        {
            // 对上传文件数组信息处理
            $files = $this->dealFiles($files);
            
            foreach($files as $key=>$file)
            {
                $type = strtolower(substr(strrchr($file['name'], '.'), 1)); //文件后缀
                $new_file_name = date('Ymdhis',time()).rand(1000,9999);
				
                $image_path = $this->path.'/'.$new_file_name.'.'.$type;
                $uploads_path = $this->path; //存储路径
                
                $allow_type = array('jpg','jpeg','gif','png','doc','docx','txt','pdf'); //定义允许上传的类型
                
                //判断文件类型是否被允许上传
                if(!in_array($type, $allow_type))
                {
                    //如果不被允许，则直接停止程序运行
                    Util::echo_json(ReturnData::create(ReturnData::FAIL,null,'文件格式不正确'));
                }
                
                //判断是否是通过HTTP POST上传的
                if(!is_uploaded_file($file['tmp_name']))
                {
                    //如果不是通过HTTP POST上传的
                    Util::echo_json(ReturnData::create(ReturnData::FAIL));
                }
                
                //文件小于1M
                if ($file['size'] < $this->file_size)
                {
                    if ($file['error'] > 0)
                    {
                        Util::echo_json(ReturnData::create(ReturnData::FAIL,null,$file['error']));
                    }
                    else
                    {
                        if(!file_exists($this->public_path.$uploads_path))
                        {
                            Helper::createDir($this->public_path.$uploads_path); //创建文件夹;
                        }
                        
                        move_uploaded_file($file['tmp_name'], $this->public_path.$image_path);
                    }
                }
                else
                {
                    Util::echo_json(ReturnData::create(ReturnData::FAIL,null,'文件不得超过2M'));
                }
                
                $res[] = array('url' => sysconfig('CMS_SITE_CDN_ADDRESS').$image_path, 'path' => $image_path, 'name' => $file['name'], 'file_name' => $new_file_name, 'type' => $type, 'file_size' => $file['size']);
            }
        }
        
        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res), JSON_UNESCAPED_SLASHES); //让json_encode不自动转义斜杠“/”的方法
    }
    
    //阿里云OSS图片上传，成功返回路径，不含域名
    public function ossImageUpload()
    {
        $res = $this->aliyunOSSFileUpload($_FILES);
        
        if($res['code'] == 1)
        {
            Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res['data']));
        }
        
        Util::echo_json(ReturnData::create(ReturnData::FAIL, null, $res['msg']));
    }
    
    public function aliyunOSSFileUpload($files)
    {
        $res = [];
        
        //$files = $_FILES;//得到传输的数据
        $path = 'data/uploads/'.date('Y/m',time());
        
        if($files)
        {
            // 对上传文件数组信息处理
            $files = $this->dealFiles($files);
            
            foreach($files as $key=>$file)
            {
                $type = strtolower(substr(strrchr($file['name'], '.'), 1)); //文件后缀
                
                $image_path = $path.'/'.date('Ymdhis',time()).rand(1000,9999).'.'.$type;
                $uploads_path = $path; //存储路径
                
                $allow_type = array('jpg','jpeg','gif','png','doc','docx','txt','pdf'); //定义允许上传的类型
                
                //判断文件类型是否被允许上传
                if(!in_array($type, $allow_type))
                {
                    //如果不被允许，则直接停止程序运行
                    return ['code'=>0,'msg'=>'文件格式不正确','data'=>''];
                }
                
                //判断是否是通过HTTP POST上传的
                if(!is_uploaded_file($file['tmp_name']))
                {
                    //如果不是通过HTTP POST上传的
                    return ['code'=>0,'msg'=>'上传失败','data'=>''];
                }
                
                //文件小于2M
                if ($file['size'] < $this->file_size)
                {
                    if ($file['error'] > 0)
                    {
                        return ['code'=>0,'msg'=>$file['error'],'data'=>''];
                    }
                    else
                    {
                        /* if(!file_exists(substr(ROOT_PATH, 0, -1).$uploads_path))
                        {
                            Helper::createDir(substr(ROOT_PATH, 0, -1).$uploads_path); //创建文件夹;
                        }
                        
                        move_uploaded_file($file['tmp_name'], substr(ROOT_PATH, 0, -1).$image_path); */
                        
                        $image = AliyunOSS::uploadFile($image_path, $file['tmp_name']);
                        if($image && $image['code']==1){}else{return ['code'=>0,'msg'=>'系统错误','data'=>''];}
                    }
                }
                else
                {
                    return ['code'=>0,'msg'=>'文件不得超过2M','data'=>''];
                }
                
                $res[$key] = $image['data']['oss-request-url'];
            }
            
            return ['code'=>1,'msg'=>'操作成功','data'=>$res];
        }
        
        return ['code'=>0,'msg'=>'参数错误','data'=>''];
    }
    
    /**
     * 转换上传文件数组变量为正确的方式
     * @access public
     * @param array $files 上传的文件变量
     * @return array
     */
    public function dealFiles($files)
    {
        $fileArray = [];
        $n         = 0;
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $keys  = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key) {
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            } else {
                $fileArray = $files;
                break;
            }
        }
        
        return $fileArray;
    }
    
    /**
     * base64图片上传，成功返回路径，不含域名，只能单图上传
     * @param string img base64字符串
     * @return string
     */
    public function base64ImageUpload()
	{
        $res = $this->base64ImageSave($_POST['img']);
        
        if($res['code'] == ReturnData::SUCCESS)
        {
            Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res['data']));
        }
        
        Util::echo_json(ReturnData::create(ReturnData::FAIL, null, $res['msg']));
    }
    
    public function base64ImageSave($files)
    {
        $res = [];
        $base64_img = $files;
        
        if($base64_img)
        {
            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result))
            {
                $type = $result[2];
                if(in_array($type, array('jpeg','jpg','gif','bmp','png')))
                {
                    $image_path = $this->path.'/'.date('Ymdhis',time()).rand(1000,9999).'.'.$type;
                    $uploads_path = $this->path; //存储路径
                    
                    if(!file_exists($this->public_path.$uploads_path))
                    {
                        Helper::createDir($this->public_path.$uploads_path); //创建文件夹;
                    }
                    
                    if(file_put_contents($this->public_path.$image_path, base64_decode(str_replace($result[1], '', $base64_img))))
                    {
                        return ReturnData::create(ReturnData::SUCCESS, $image_path);
                    }
                    
                    return ReturnData::create(ReturnData::FAIL, null, '图片上传失败');
                }
                
                //图片后缀格式不在范围内
                return ReturnData::create(ReturnData::FAIL, null, '图片上传类型错误');
            }
            
            //文件错误
            return ReturnData::create(ReturnData::FAIL, null, '文件错误');
        }
        
        return ReturnData::create(ReturnData::FAIL, null, '请上传文件');
    }
    
    /**
     * 获取小程序码
     * @param string scene 场景值 id=1
     * @param string page 'pages/home/pages/shop/index' 必须是已经发布的小程序存在的页面 id=1
     * @param int width 宽度，默认430
     * @param int type 0路径存储，1base64
     */
    public function get_wxacodeunlimit()
	{
        $data['scene'] = input('scene','');
        $data['page'] = input('page','');
        $data['width'] = input('width',430);
        $data['type'] = input('type', 0); //0路径存储，1base64
        
        $image_path = '/uploads/wxacode/'.md5($data['page'].$data['scene']).'.jpg';
        if($data['type']==0)
        {
            $data['image_path'] = $this->public_path.$image_path;
        }
        
        $xcx = new WechatAuth(sysconfig('CMS_WX_MINIPROGRAM_APPID'), sysconfig('CMS_WX_MINIPROGRAM_APPSECRET'));
        $res = $xcx->getwxacodeunlimit($data);
        
        if($data['type']==0)
        {
            $res = $image_path;
            /* $headurl = db('shop')->where(['id'=>$data['shop_id']])->value('head_img');
            if($headurl && file_exists($_SERVER['DOCUMENT_ROOT'].$headurl))
            {
                $head_image_path = '/uploads/wxacode/';
                //编辑已保存的原头像，保存成圆形（其实不是圆形，改变它的边角为透明）
                //header("content-type:image/png"); //传入保存后的头像文件名
                $imgg = $this->yuan_img($this->public_path.$headurl);
                $head_img_name = "head_img_".$data['shop_id'].".png";
                imagepng($imgg, $this->public_path.$head_image_path.$head_img_name);
                imagedestroy($imgg);
                
                //缩小头像（原图为200，430的小程序码logo为192）
                $target_im = imagecreatetruecolor(192,192); //创建一个新的画布（缩放后的），从左上角开始填充透明背景
                imagesavealpha($target_im, true);
                $trans_colour = imagecolorallocatealpha($target_im, 255, 255, 255, 127);
                imagefill($target_im, 0, 0, $trans_colour);
                imagefilledellipse($target_im, 96, 96, 192, 192, imagecolorallocatealpha($target_im, 255, 255, 255, 0));
                
                $o_image = imagecreatefrompng($this->public_path.$head_image_path.$head_img_name); //获取上文已保存的修改之后头像的内容
                imagecopyresampled($target_im,$o_image, 0, 0, 0, 0, 192, 192, 200, 200);
                $comp_path = $this->public_path.$head_image_path.$head_img_name;
                imagepng($target_im, $comp_path);
                imagedestroy($target_im);
                
                //传入保存后的二维码地址  
                $url = $this->create_pic_watermark($this->public_path.$image_path, $comp_path, "center");
                unlink($this->public_path.$head_image_path.$head_img_name);
            } */
        }
        else
        {

        }

        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    /**
     * [yuan_img 编辑图片为圆形]  剪切头像为圆形
     * @param  [string] $imgpath [头像保存之后的图片名]
     */
    public function yuan_img($imgpath)
    {
        $ext     = pathinfo($imgpath);
        $src_img = null;
        switch ($ext['extension']) {
        case 'jpg':
            $src_img = imagecreatefromjpeg($imgpath);
            break;
        case 'png':
            $src_img = imagecreatefrompng($imgpath);
            break;
        }
        $wh  = getimagesize($imgpath);
        $w   = $wh[0];
        $h   = $wh[1];
        $w   = min($w, $h);
        $h   = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r   = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        
        return $img;
    }
    
    /**
     * [create_pic_watermark 添加图片水印] 头像贴在二维码中间
     * @param  [string] $dest_image [需要添加图片水印的图片名]
     * @param  [string] $watermark  [水印图片名]
     * @param  [string] $locate     [水印位置，center,left_buttom,right_buttom三选一]
     * @return [type]             [description]
     */
    public function create_pic_watermark($dest_image,$watermark,$locate)
    {
        list($dwidth,$dheight,$dtype)=getimagesize($dest_image);
        list($wwidth,$wheight,$wtype)=getimagesize($watermark);
        $types=array(1 => "GIF",2 => "JPEG",3 => "PNG",
            4 => "SWF",5 => "PSD",6 => "BMP",
            7 => "TIFF",8 => "TIFF",9 => "JPC",
            10 => "JP2",11 => "JPX",12 => "JB2",
            13 => "SWC",14 => "IFF",15 => "WBMP",16 => "XBM");
        $dtype=strtolower($types[$dtype]);//原图类型
        $wtype=strtolower($types[$wtype]);//水印图片类型
        $created="imagecreatefrom".$dtype;
        $createw="imagecreatefrom".$wtype;
        $imgd=$created($dest_image);
        $imgw=$createw($watermark);
        switch($locate){
            case 'center':
                $x=($dwidth-$wwidth)/2;
                $y=($dheight-$wheight)/2;
                break;
            case 'left_buttom':
                $x=1;
                $y=($dheight-$wheight-2);
                break;
            case 'right_buttom':
                $x=($dwidth-$wwidth-1);
                $y=($dheight-$wheight-2);
                break;
            default:
                die("未指定水印位置!");
                break;
        }
        imagecopy($imgd,$imgw,$x,$y,0,0, $wwidth,$wheight);
        $save="image".$dtype;
        //保存到服务器
        $f_file_name = $dest_image;
        imagejpeg($imgd,$f_file_name); //保存
        imagedestroy($imgw);
        imagedestroy($imgd);
        //传回处理好的图片
        //$url = 'https://www.qubaobei.com/'.str_replace('/opt/ci123/www/html/markets/app2/baby/','',PATH.$f_file_name);
        return $dest_image;
    }
	
	/**
     * CURL上传图片
     * @param [string] $url 图片上传地址
     * @param [string] $file = $_FILES['file'] 图片文件流
     */
	public function curl_upload_image($url, $file)
    {
		// 创建一个 cURL 句柄
		$ch = curl_init($url);
		// 创建一个 CURLFile 对象
		$cfile = curl_file_create($file['tmp_name'], $file['type'], $file['name']);
		 // 设置 POST 数据
		$data = array('file' => $cfile);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		// 执行句柄
		$response = curl_exec($ch);
        if ($response === false)
		{
            $error = curl_error($ch);
            curl_close($ch);
            return false;
        }
		else
		{
            // 解决windows 服务器 BOM 问题
            $response = trim($response,chr(239).chr(187).chr(191));
            $response = json_decode($response, true);
        }
        
        curl_close($ch);
		return $response;
    }
}